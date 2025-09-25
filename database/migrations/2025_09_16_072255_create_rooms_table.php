<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id_1')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id_2')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('last_msg_id')->nullable(); // 后面更新最后消息
            $table->timestamps();

            $table->unique(['user_id_1', 'user_id_2']); // 保证唯一
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
