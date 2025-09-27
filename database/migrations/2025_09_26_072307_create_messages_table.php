<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
      Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('room_id')->constrained()->cascadeOnDelete();
        $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
        $table->enum('type',['text','image','voice','video'])->default('text');
        $table->json('content');             // { text: "..."} 或 { url: "...", ... }
        $table->boolean('is_read')->default(false);
        $table->timestamp('read_at')->nullable();
        $table->timestamps();

        $table->index(['room_id','is_read']);
      });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

