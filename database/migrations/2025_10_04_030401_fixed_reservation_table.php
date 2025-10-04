<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // 删除 slot_start / slot_end
            if (Schema::hasColumn('reservations', 'slot_start')) {
                $table->dropColumn('slot_start');
            }
            if (Schema::hasColumn('reservations', 'slot_end')) {
                $table->dropColumn('slot_end');
            }

            // 添加审计字段
            $table->unsignedBigInteger('created_by')->nullable()->after('remark');
            $table->string('created_by_name')->nullable()->after('created_by');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by_name');
            $table->string('updated_by_name')->nullable()->after('updated_by');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dateTime('slot_start')->nullable();
            $table->dateTime('slot_end')->nullable();

            $table->dropColumn(['created_by', 'created_by_name', 'updated_by', 'updated_by_name']);
        });
    }
};
