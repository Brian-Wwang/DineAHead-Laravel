<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();      // FCM token
            $table->string('platform')->nullable(); // ios / android / web
            $table->string('device_id')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('device_tokens'); }
};
