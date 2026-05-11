<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'storage_limit',
        'storage_used',
        'is_verified',
        'telegram_bot_token',
        'telegram_channel_id',
        'telegram_channel_name',
    ];

    protected $hidden = [
        'password',
        'telegram_bot_token',
    ];

    protected $casts = [
        'storage_limit' => 'integer',
        'storage_used' => 'integer',
        'is_verified' => 'boolean',
    ];

    protected $appends = ['has_telegram_config'];

    public function getHasTelegramConfigAttribute()
    {
        return !empty($this->telegram_bot_token) && !empty($this->telegram_channel_id);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function folders()
    {
        return $this->hasMany(Folder::class);
    }

    public function shares()
    {
        return $this->hasMany(Share::class, 'created_by');
    }
}