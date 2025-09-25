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
      Schema::create('menus', function (Blueprint $table) {
        $table->id();
        $table->foreignId('store_id')->constrained()->cascadeOnDelete();
        $table->foreignId('category_id')->nullable();

        $table->string('name');
        $table->text('description')->nullable();

        // 价格支持两位小数
        $table->decimal('price', 10, 2);

        // ✅ 单张图片
        $table->string('image')->nullable();

        // ✅ 数字枚举，数字为准
        $table->tinyInteger('discount_type')
              ->default(0)
              ->comment('0=none,10=percentage,20=actual');

        $table->decimal('discount_amount', 10, 2)->default(0);

        // $table->tinyInteger('status')
        //       ->default(0)
        //       ->comment('0=available,1=pending,2=accept,3=confirm');

        $table->boolean('is_active')->default(true);
        $table->unsignedBigInteger('current_booking_id')->nullable();

        $table->timestamps();
        $table->softDeletes();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
