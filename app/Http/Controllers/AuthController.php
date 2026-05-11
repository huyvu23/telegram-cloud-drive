<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private $jwtSecret;

    public function __construct()
    {
        $this->jwtSecret = env('JWT_SECRET', 'secret');
    }

    private function generateToken($user)
    {
        $payload = [
            'user_id' => $user->id,
            'iat' => time(),
            'exp' => time() + (int)env('JWT_EXPIRES_IN', 3600)
        ];
        return base64_encode(json_encode($payload)) . '.' . base64_encode(hash_hmac('sha256', json_encode($payload), $this->jwtSecret, true));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_verified' => true,
            'storage_limit' => 10737418240 // 10GB default
        ]);

        $token = $this->generateToken($user);

        return response()->json([
            'message' => 'Registration successful',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role ?? 'user',
                'storageLimit' => $user->storage_limit,
                'storageUsed' => $user->storage_used,
                'hasTelegramConfig' => $user->has_telegram_config
            ],
            'accessToken' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Email hoặc mật khẩu không đúng'], 401);
        }

        $token = $this->generateToken($user);

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role ?? 'user',
                'storageLimit' => $user->storage_limit,
                'storageUsed' => $user->storage_used,
                'hasTelegramConfig' => $user->has_telegram_config
            ],
            'accessToken' => $token
        ]);
    }

    public function me(Request $request)
    {
        $token = $request->bearerToken();
        $userId = $this->verifyToken($token);
        
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);
        
        $user = User::find($userId);
        if (!$user) return response()->json(['error' => 'User not found'], 404);
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role ?? 'user',
                'storageLimit' => $user->storage_limit,
                'storageUsed' => $user->storage_used,
                'hasTelegramConfig' => $user->has_telegram_config
            ]
        ]);
    }

    public function setupTelegram(Request $request)
    {
        $token = $request->bearerToken();
        $userId = $this->verifyToken($token);
        
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

        $validator = Validator::make($request->all(), [
            'bot_token' => 'required|string',
            'channel_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $botToken = $request->bot_token;
        $channelId = $request->channel_id;

        // Verify bot token and get bot info
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

        // Update user
        $user = User::find($userId);
        $user->update([
            'telegram_bot_token' => $botToken,
            'telegram_channel_id' => $channelId,
            'telegram_channel_name' => $channelName
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Telegram đã được thiết lập thành công!',
            'channel' => $channelName
        ]);
    }

    public function getTelegramConfig(Request $request)
    {
        $token = $request->bearerToken();
        $userId = $this->verifyToken($token);
        
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

        $user = User::find($userId);
        
        return response()->json([
            'configured' => $user->has_telegram_config,
            'channel_id' => $user->telegram_channel_id,
            'channel_name' => $user->telegram_channel_name
        ]);
    }

    private function verifyToken($token)
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 2) return null;
            
            $payload = json_decode(base64_decode($parts[0]), true);
            $signature = base64_decode($parts[1]);
            
            $expected = hash_hmac('sha256', json_encode($payload), $this->jwtSecret, true);
            
            if (!hash_equals($expected, $signature)) return null;
            if ($payload['exp'] < time()) return null;
            
            return $payload['user_id'];
        } catch (\Exception $e) {
            return null;
        }
    }
}