<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Share;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ShareController extends Controller
{
    private function getUserId(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) return null;
        
        $parts = explode('.', $token);
        if (count($parts) !== 2) return null;
        
        $payload = json_decode(base64_decode($parts[0]), true);
        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) return null;
        
        return $payload['user_id'] ?? null;
    }

    public function page($token)
    {
        return view('drive.shared', ['token' => $token]);
    }

    public function create(Request $request)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

        $fileId = $request->input('file_id');
        $file = File::where('id', $fileId)->where('user_id', $userId)->first();
        
        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $existing = Share::where('file_id', $fileId)->where('is_active', true)->first();
        if ($existing) {
            return response()->json([
                'share' => $existing,
                'link' => url('/shared/' . $existing->token)
            ]);
        }

        $token = Str::random(32);
        $share = Share::create([
            'file_id' => $fileId,
            'token' => $token,
            'created_by' => $userId,
            'max_downloads' => $request->input('max_downloads', 100),
            'expires_at' => $request->input('expires_at')
        ]);

        return response()->json([
            'share' => $share,
            'link' => url('/shared/' . $token)
        ], 201);
    }

    public function show(Request $request, $token)
    {
        $share = Share::where('token', $token)->where('is_active', true)->first();
        
        if (!$share) {
            return response()->json(['error' => 'Share not found or expired'], 404);
        }

        if ($share->expires_at && now()->gt($share->expires_at)) {
            return response()->json(['error' => 'Share expired'], 410);
        }

        if ($share->max_downloads && $share->download_count >= $share->max_downloads) {
            return response()->json(['error' => 'Download limit reached'], 410);
        }

        $file = File::find($share->file_id);
        
        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->json([
            'file' => $file,
            'expires_at' => $share->expires_at,
            'downloads_remaining' => $share->max_downloads ? $share->max_downloads - $share->download_count : null
        ]);
    }

    public function download(Request $request, $token)
    {
        $share = Share::where('token', $token)->where('is_active', true)->first();
        
        if (!$share) {
            return response()->json(['error' => 'Share not found or expired'], 404);
        }

        if ($share->expires_at && now()->gt($share->expires_at)) {
            return response()->json(['error' => 'Share expired'], 410);
        }

        if ($share->max_downloads && $share->download_count >= $share->max_downloads) {
            return response()->json(['error' => 'Download limit reached'], 410);
        }

        $file = File::find($share->file_id);
        
        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $share->increment('download_count');

        // Get owner's Telegram config
        $owner = User::find($share->created_by);

        // Check local first
        $localPath = $file->path;
        if ($localPath && \Storage::disk('files')->exists($localPath)) {
            return \Storage::disk('files')->download($localPath, $file->name);
        }

        // Download from owner's Telegram
        if ($file->telegram_file_id && $owner && $owner->telegram_bot_token) {
            try {
                $response = Http::get("https://api.telegram.org/bot{$owner->telegram_bot_token}/getFile", [
                    'file_id' => $file->telegram_file_id
                ]);

                $result = json_decode($response->body(), true);
                
                if ($result['ok'] ?? false) {
                    $filePath = $result['result']['file_path'];
                    $downloadUrl = "https://api.telegram.org/file/bot{$owner->telegram_bot_token}/{$filePath}";
                    
                    $content = file_get_contents($downloadUrl);
                    
                    if ($content !== false) {
                        return response($content, 200, [
                            'Content-Type' => $file->mime_type ?? 'application/octet-stream',
                            'Content-Disposition' => 'attachment; filename="' . $file->name . '"',
                            'Content-Length' => strlen($content)
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Share Telegram download failed: ' . $e->getMessage());
            }
        }

        return response()->json(['error' => 'File not available'], 404);
    }

    public function listUserShares(Request $request)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

        $shares = Share::where('created_by', $userId)
            ->with('file')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['shares' => $shares]);
    }

    public function revoke(Request $request, $token)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

        $share = Share::where('token', $token)->where('created_by', $userId)->first();
        
        if (!$share) {
            return response()->json(['error' => 'Share not found'], 404);
        }

        $share->update(['is_active' => false]);

        return response()->json(['success' => true, 'message' => 'Share revoked']);
    }
}