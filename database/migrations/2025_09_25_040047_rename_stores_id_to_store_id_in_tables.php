<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            // 🔁 先移除旧外键约束
            $table->dropForeign(['stores_id']);

            // ✏️ 修改字段名称
            $table->renameColumn('stores_id', 'store_id');
        });

        Schema::table('tables', function (Blueprint $table) {
            // ✅ 重新添加外键约束
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->renameColumn('store_id', 'stores_id');
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->foreign('stores_id')->references('id')->on('stores')->cascadeOnDelete();
        });
    }
};
