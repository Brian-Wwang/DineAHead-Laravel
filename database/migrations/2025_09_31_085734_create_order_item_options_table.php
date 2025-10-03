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
      Schema::create('order_item_options', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();

        // 快照字段（避免跟随修改）
        $table->string('option_name');   // 例如 Spicy / Addition
        $table->string('value_name');    // 例如 微辣 / 鸡蛋
        $table->tinyInteger('select_type')->nullable(); // 10=single 20=multiple
        $table->decimal('extra_price', 10, 2)->default(0); // 当时的加价快照

        $table->timestamps();
        $table->softDeletes();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_options');
    }
};
