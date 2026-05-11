<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function index(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) return response()->json(['error' => 'Unauthorized'], 401);
        
        $parts = explode('.', $token);
        if (count($parts) !== 2) return response()->json(['error' => 'Invalid token'], 401);
        
        $payload = json_decode(base64_decode($parts[0]), true);
        if (!$payload || $payload['exp'] < time()) return response()->json(['error' => 'Token expired'], 401);
        
        $userId = $payload['user_id'];
        
        $folders = Folder::where('user_id', $userId)->whereNull('deleted_at')->orderBy('name')->get();
        
        return response()->json(['folders' => $folders]);
    }

    public function store(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) return response()->json(['error' => 'Unauthorized'], 401);
        
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[0]), true);
        $userId = $payload['user_id'];
        
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $folder = Folder::create([
            'name' => $request->name,
            'user_id' => $userId,
            'parent_id' => $request->parent_id,
            'path' => '/users/' . $userId . '/' . $request->name . '_' . time()
        ]);

        return response()->json(['folder' => $folder], 201);
    }

    public function breadcrumbs(Request $request, $id)
    {
        $breadcrumbs = [];
        $current = Folder::find($id);
        
        while ($current) {
            array_unshift($breadcrumbs, ['id' => $current->id, 'name' => $current->name]);
            $current = $current->parent_id ? Folder::find($current->parent_id) : null;
        }

        return response()->json(['breadcrumbs' => $breadcrumbs]);
    }
}