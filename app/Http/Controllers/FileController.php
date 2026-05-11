<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    private $storageDir;
    private $telegramTimeout = 120;

    public function __construct()
    {
        $this->storageDir = storage_path('app/files');
        
        if (!file_exists($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

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

    public function index(Request $request)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

        $folderId = $request->query('folder_id');
        $query = File::withoutTrashed()->where('user_id', $userId);
        
        if ($folderId) {
            $query->where('folder_id', $folderId);
        } else {
            $query->whereNull('folder_id');
        }
        
        $files = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['files' => $files, 'pagination' => ['total' => $files->count()]]);
    }

    public function upload(Request $request)
    {
        // Log to file
        $logFile = storage_path('upload_log.txt');
        $log = function($msg) use ($logFile) {
            file_put_contents($logFile, "[" . date('H:i:s') . "] " . $msg . "\n", FILE_APPEND);
        };
        
        $log("=== Upload Request ===");
        
        // Check file exists
        if (!$request->hasFile('file')) {
            $log("ERROR: No file provided");
            return response()->json(['error' => 'No file provided'], 400);
        }
        
        $file = $request->file('file');
        $fileSize = $file->getSize();
        $fileName = $file->getClientOriginalName();
        
        $log("INFO: File: {$fileName} Size: {$fileSize}");
        
        // Check file size (50MB limit)
        if ($fileSize > 50 * 1024 * 1024) {
            $log("ERROR: File too large: {$fileSize}");
            return response()->json(['error' => 'File too large (max 50MB)'], 400);
        }
        
        // Authenticate
        $userId = $this->getUserId($request);
        if (!$userId) {
            $log("ERROR: Invalid token");
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $user = User::find($userId);
        if (!$user) {
            $log("ERROR: User not found");
            return response()->json(['error' => 'User not found'], 404);
        }
        
        $log("INFO: User: {$user->email} (ID:{$userId})");
        
        // Check Telegram config
        $botToken = $user->telegram_bot_token;
        $channelId = $user->telegram_channel_id;
        
        if (empty($botToken) || empty($channelId)) {
            $log("ERROR: No Telegram config for user");
            return response()->json(['error' => 'Telegram not configured. Go to Settings.'], 400);
        }
        
        $log("INFO: Using Telegram bot");
        
        // Upload to Telegram
        $telegramApi = "https://api.telegram.org/bot{$botToken}/sendDocument";
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $telegramApi,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'chat_id' => $channelId,
                'document' => new \CURLFile($file->getRealPath(), $file->getMimeType() ?: 'application/octet-stream', $fileName)
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->telegramTimeout
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            $log("ERROR: Curl error: {$error}");
            return response()->json(['error' => 'Telegram error: ' . $error], 500);
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['ok']) || !$result['ok']) {
            $errMsg = $result['description'] ?? 'Unknown Telegram error';
            $log("ERROR: Telegram failed: {$errMsg}");
            return response()->json(['error' => 'Telegram upload failed: ' . $errMsg], 500);
        }
        
        // Success - save to database
        $folderId = $request->input('folder_id');
        $fileRecord = File::create([
            'user_id' => $userId,
            'name' => $fileName,
            'original_name' => $fileName,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $fileSize,
            'extension' => $file->getClientOriginalExtension() ?: 'bin',
            'path' => "{$userId}/{$fileName}",
            'telegram_file_id' => $result['result']['document']['file_id'],
            'message_id' => $result['result']['message_id'],
            'channel_id' => $channelId,
            'folder_id' => $folderId
        ]);
        
        // Update storage
        $user->storage_used = ($user->storage_used ?? 0) + $fileSize;
        $user->save();
        
        $log("SUCCESS: File ID:{$fileRecord->id} Telegram:{$result['result']['message_id']}");
        
        return response()->json([
            'success' => true,
            'message' => 'Upload thành công!',
            'file' => $fileRecord
        ], 201);
    }

    public function download(Request $request, $id)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

        $file = File::where('id', $id)->where('user_id', $userId)->first();
        if (!$file) return response()->json(['error' => 'File not found'], 404);

        return response()->json(['file' => $file]);
    }

    public function destroy(Request $request, $id)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

        $file = File::where('id', $id)->where('user_id', $userId)->first();
        if (!$file) return response()->json(['error' => 'File not found'], 404);

        $file->delete();

        return response()->json(['success' => true, 'message' => 'File deleted']);
    }

    public function trash(Request $request)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

        $files = File::onlyTrashed()->where('user_id', $userId)->get();

        return response()->json(['files' => $files]);
    }

    public function restore(Request $request, $id)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

        $file = File::onlyTrashed()->where('id', $id)->where('user_id', $userId)->first();
        if (!$file) return response()->json(['error' => 'File not found'], 404);

        $file->restore();

        return response()->json(['success' => true, 'message' => 'File restored']);
    }

    public function emptyTrash(Request $request)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

        File::onlyTrashed()->where('user_id', $userId)->delete();

        return response()->json(['success' => true, 'message' => 'Trash emptied']);
    }
}
