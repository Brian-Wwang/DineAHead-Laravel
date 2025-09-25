<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('tables', function (Blueprint $table) {
        $table->id();
        $table->foreignId('store_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->text('description')->nullable();
        $table->string('seat-level')->nullable();
        $table->jsonb('images')->nullable();   // 存储图片URL数组
        $table->tinyInteger('status')->default(0)->comment('0=available,1=pending,2=accept,3=confirm');
        $table->boolean('is_active')->default(true);
        $table->unsignedBigInteger('current_booking_id')->nullable();
        $table->timestamps();
        $table->softDeletes(); // is_delete
      });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
