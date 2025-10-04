<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // 先删除外键约束
            $table->dropForeign(['user_id']);

            // 修改 user_id 可空
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // 重新加回外键约束
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // 回滚时，先删除外键
            $table->dropForeign(['user_id']);

            // user_id 改回必填
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            // 再加回外键
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
