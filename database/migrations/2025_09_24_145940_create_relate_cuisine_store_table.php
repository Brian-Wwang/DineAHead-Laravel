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
      Schema::create('relate_cuisine_store', function (Blueprint $table) {
        $table->id();
        $table->foreignId('store_id')->constrained()->onDelete('cascade');
        $table->foreignId('cuisine_id')->constrained()->onDelete('cascade');
        $table->timestamps();

        $table->unique(['store_id', 'cuisine_id']); // ✅ 放在这里
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relate_cuisine_store');
    }
};
