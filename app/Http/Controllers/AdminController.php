<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AdminController extends Controller
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

    private function verifyAdmin($userId)
    {
        $user = User::find($userId);
        return $user && $user->role === 'admin';
    }

    public function index()
    {
        return view('admin.index');
    }

    public function stats(Request $request)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);
        if (!$this->verifyAdmin($userId)) return response()->json(['error' => 'Forbidden'], 403);

        $totalUsers = User::count();
        $totalFiles = File::count();
        $totalFolders = Folder::count();
        $totalStorage = User::sum('storage_used') ?? 0;
        
        $usersWithConfig = User::whereNotNull('telegram_bot_token')
            ->whereNotNull('telegram_channel_id')
            ->count();
        $usersWithoutConfig = $totalUsers - $usersWithConfig;

        return response()->json([
            'totalUsers' => $totalUsers,
            'totalFiles' => $totalFiles,
            'totalFolders' => $totalFolders,
            'totalStorage' => $totalStorage,
            'usersWithConfig' => $usersWithConfig,
            'usersWithoutConfig' => $usersWithoutConfig
        ]);
    }

    public function users(Request $request)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);
        if (!$this->verifyAdmin($userId)) return response()->json(['error' => 'Forbidden'], 403);

        $users = User::orderBy('created_at', 'desc')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'storage_limit' => $user->storage_limit,
                'storage_used' => $user->storage_used,
                'file_count' => $user->files()->count(),
                'telegram_configured' => $user->has_telegram_config,
                'telegram_bot_token' => $user->telegram_bot_token,
                'telegram_channel_id' => $user->telegram_channel_id,
                'telegram_channel_name' => $user->telegram_channel_name,
                'created_at' => $user->created_at,
                'is_verified' => $user->is_verified
            ];
        });

        return response()->json(['users' => $users]);
    }

    public function updateUser(Request $request, $id)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);
        if (!$this->verifyAdmin($userId)) return response()->json(['error' => 'Forbidden'], 403);

        $user = User::findOrFail($id);
        
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('role')) {
            $user->role = $request->role;
        }
        if ($request->has('storage_limit')) {
            $user->storage_limit = (int)$request->storage_limit;
        }
        if ($request->has('is_verified')) {
            $user->is_verified = (bool)$request->is_verified;
        }
        if ($request->has('telegram_bot_token')) {
            $user->telegram_bot_token = $request->telegram_bot_token ?: null;
        }
        if ($request->has('telegram_channel_id')) {
            $user->telegram_channel_id = $request->telegram_channel_id ?: null;
        }
        if ($request->has('telegram_channel_name')) {
            $user->telegram_channel_name = $request->telegram_channel_name ?: null;
        }
        
        $user->save();

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function deleteUser(Request $request, $id)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);
        if (!$this->verifyAdmin($userId)) return response()->json(['error' => 'Forbidden'], 403);

        if ($id == $userId) {
            return response()->json(['error' => 'Cannot delete yourself'], 400);
        }

        $user = User::findOrFail($id);
        
        // Delete all user files from Telegram
        $files = File::where('user_id', $id)->get();
        foreach ($files as $file) {
            if ($file->telegram_file_id && $user->telegram_bot_token) {
                try {
                    \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$user->telegram_bot_token}/deleteMessage", [
                        'chat_id' => $file->channel_id,
                        'message_id' => $file->message_id
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to delete Telegram message: ' . $e->getMessage());
                }
            }
        }
        
        // Delete user (cascades to files, folders, shares)
        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted']);
    }

    public function allFiles(Request $request)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);
        if (!$this->verifyAdmin($userId)) return response()->json(['error' => 'Forbidden'], 403);

        $files = File::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'size' => $file->size,
                    'user_name' => $file->user->name ?? 'Unknown',
                    'user_email' => $file->user->email ?? 'Unknown',
                    'created_at' => $file->created_at
                ];
            });

        return response()->json(['files' => $files]);
    }

    // ======================
    // ACCOUNTS BOT (Master Bot for all users without config)
    // ======================
    
    public function setupAccountsBot(Request $request)
    {
        $userId = $this->getUserId($request);
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);
        if (!$this->verifyAdmin($userId)) return response()->json(['error' => 'Forbidden'], 403);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'bot_token' => 'required|string',
            'channel_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $botToken = $request->bot_token;
        $channelId = $request->channel_id;

        // Verify bot token
        try {
            $response = Http::get("https://api.telegram.org/bot{$botToken}/getMe");
            $result = json_decode($response->body(), true);
            
            if (!($result['ok'] ?? false)) {
                return response()->json(['error' => 'Bot token không hợp lệ'], 400);
            }

            $botUsername = $result['result']['username'] ?? 'Unknown';
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể kết nối Telegram. Kiểm tra bot token.'], 400);
        }

        // Verify bot is admin of channel
        try {
            $chatResponse = Http::get("https://api.telegram.org/bot{$botToken}/getChat", [
                'chat_id' => $channelId
            ]);
            $chatResult = json_decode($chatResponse->body(), true);
            
            if (!($chatResult['ok'] ?? false)) {
                return response()->json(['error' => 'Channel ID không hợp lệ hoặc bot không có quyền truy cập'], 400);
            }

            $channelName = $chatResult['result']['title'] ?? 'Unknown Channel';

            // Check if bot is admin
            $memberResponse = Http::get("https://api.telegram.org/bot{$botToken}/getChatMember", [
                'chat_id' => $channelId,
                'user_id' => $result['result']['id']
            ]);
            $memberResult = json_decode($memberResponse->body(), true);
            
            if (!($memberResult['ok'] ?? false)) {
                return response()->json(['error' => 'Không thể kiểm tra quyền bot. Đảm bảo bot là admin channel.'], 400);
            }

            $status = $memberResult['result']['status'] ?? '';
            if ($status !== 'administrator' && $status !== 'creator') {
                return response()->json(['error' => 'Bot phải là Admin của channel'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi kiểm tra channel: ' . $e->getMessage()], 400);
        }

        // Save to .env
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        
        if (preg_match('/ACCOUNTS_BOT_TOKEN=/', $envContent)) {
            $envContent = preg_replace('/ACCOUNTS_BOT_TOKEN=.*/', 'ACCOUNTS_BOT_TOKEN=' . $botToken, $envContent);
        } else {
            $envContent .= "\nACCOUNTS_BOT_TOKEN=" . $botToken;
        }
        
        if (preg_match('/ACCOUNTS_CHANNEL_ID=/', $envContent)) {
            $envContent = preg_replace('/ACCOUNTS_CHANNEL_ID=.*/', 'ACCOUNTS_CHANNEL_ID=' . $channelId, $envContent);
        } else {
            $envContent .= "\nACCOUNTS_CHANNEL_ID=" . $channelId;
        }
        
        if (preg_match('/ACCOUNTS_CHANNEL_NAME=/', $envContent)) {
            $envContent = preg_replace('/ACCOUNTS_CHANNEL_NAME=.*/', 'ACCOUNTS_CHANNEL_NAME=' . $channelName, $envContent);
        } else {
            $envContent .= "\nACCOUNTS_CHANNEL_NAME=" . $channelName;
        }
        
        file_put_contents($envPath, $envContent);
        \Illuminate\Support\Facades\Artisan::call('config:clear');

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu Accounts Bot! Users chưa có Telegram sẽ dùng bot này.',
            'channel' => $channelName
        ]);
    }

    public function getAccountsBot()
    {
        return response()->json([
            'configured' => !empty(env('ACCOUNTS_BOT_TOKEN')),
            'bot_token' => env('ACCOUNTS_BOT_TOKEN') ? substr(env('ACCOUNTS_BOT_TOKEN'), 0, 10) . '...' : null,
            'channel_id' => env('ACCOUNTS_CHANNEL_ID'),
            'channel_name' => env('ACCOUNTS_CHANNEL_NAME')
        ]);
    }
}