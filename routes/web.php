<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LanguageController;

// ======================
// FRONTEND PAGES
// ======================
Route::get('/', fn() => redirect('/login'));
Route::get('/login', fn() => view('auth.login'));
Route::get('/register', fn() => view('auth.register'));
Route::get('/drive', fn() => view('drive.index'));
Route::get('/settings', fn() => view('drive.settings'));
Route::get('/shared/{token}', [ShareController::class, 'page']);
Route::get('/admin', [AdminController::class, 'index']);

// ======================
// LANGUAGE SWITCHING
// ======================
Route::get('/lang/{locale}', [LanguageController::class, 'switchLocale']);

// ======================
// API ROUTES (prefix: /api)
// ======================
Route::prefix('api')->group(function () {
    Route::get('/health', fn() => response()->json(['status' => 'ok', 'timestamp' => now()->toISOString()]));

    // Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/telegram/setup', [AuthController::class, 'setupTelegram']);
    Route::get('/auth/telegram/config', [AuthController::class, 'getTelegramConfig']);

    // Files
    Route::get('/files', [FileController::class, 'index']);
    Route::post('/files/upload', [FileController::class, 'upload']);
    Route::get('/files/{id}/download', [FileController::class, 'download']);
    Route::delete('/files/{id}', [FileController::class, 'delete']);
    Route::get('/files/trash', [FileController::class, 'trash']);
    Route::post('/files/{id}/restore', [FileController::class, 'restore']);
    Route::delete('/files/{id}/permanent', [FileController::class, 'permanentDelete']);
    Route::delete('/files/empty-trash', [FileController::class, 'emptyTrash']);

    // Folders
    Route::get('/folders', [FolderController::class, 'index']);
    Route::post('/folders', [FolderController::class, 'store']);
    Route::get('/folders/{id}/breadcrumbs', [FolderController::class, 'breadcrumbs']);
    Route::delete('/folders/{id}', [FolderController::class, 'destroy']);

    // Share
    Route::post('/share', [ShareController::class, 'create']);
    Route::get('/share/{token}', [ShareController::class, 'show']);
    Route::get('/share/{token}/download', [ShareController::class, 'download']);
    Route::get('/shares', [ShareController::class, 'listUserShares']);
    Route::delete('/share/{token}', [ShareController::class, 'revoke']);

    // Admin
    Route::prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::get('/files', [AdminController::class, 'allFiles']);
        Route::post('/accounts/setup', [AdminController::class, 'setupAccountsBot']);
        Route::get('/accounts/config', [AdminController::class, 'getAccountsBot']);
    });
});