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
      Schema::create('menu_options', function (Blueprint $table) {
        $table->id();
        $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();

        // 支持绑定平台选项或商家自定义选项（二选一，允许混合存在）
        $table->foreignId('platform_option_id')->nullable()->constrained('platform_options')->cascadeOnDelete();
        $table->foreignId('store_option_id')->nullable()->constrained('store_options')->cascadeOnDelete();

        // 行为配置
        $table->boolean('is_required')->default(false); // 必选 / 可选
        $table->integer('max_select')->default(1);      // 多选时限制数量

        $table->timestamps();
        $table->softDeletes();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_options');
    }
};
