<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    protected $fillable = [
        'file_id',
        'token',
        'expires_at',
        'max_downloads',
        'download_count',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'download_count' => 'integer',
        'max_downloads' => 'integer',
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}