<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'original_name',
        'mime_type',
        'size',
        'extension',
        'path',
        'telegram_file_id',
        'message_id',
        'channel_id',
        'folder_id',
        'user_id',
        'is_starred'
    ];

    protected $casts = [
        'size' => 'integer',
        'is_starred' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}