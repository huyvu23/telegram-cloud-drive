<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->string('extension')->nullable();
            $table->string('telegram_file_id')->nullable();
            $table->string('message_id')->nullable();
            $table->string('channel_id')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_starred')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};