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
      Schema::create('platform_option_values', function (Blueprint $table) {
          $table->id();
          $table->foreignId('platform_option_id')->constrained()->cascadeOnDelete();
          $table->string('name'); // 全糖、半糖
          $table->decimal('extra_price', 10, 2)->default(0);
          $table->integer('sort_order')->default(0);
          $table->boolean('is_active')->default(true);
          $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_option_values');
    }
};
