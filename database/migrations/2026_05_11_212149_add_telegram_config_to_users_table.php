<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_bot_token', 255)->nullable()->after('password');
            $table->string('telegram_channel_id', 50)->nullable()->after('telegram_bot_token');
            $table->string('telegram_channel_name', 255)->nullable()->after('telegram_channel_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telegram_bot_token', 'telegram_channel_id', 'telegram_channel_name']);
        });
    }
};