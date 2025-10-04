<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');             // 简化为纯文本；可扩展 attachments JSON
            $table->json('meta')->nullable(); // 自定义扩展
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('messages'); }
};
