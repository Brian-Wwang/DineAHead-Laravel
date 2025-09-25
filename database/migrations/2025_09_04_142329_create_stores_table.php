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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('contact');
            $table->string('email')->nullable();
            $table->text('description')->nullable();

            // ⚠️ 这里存放的是 locations.code（字符串），而不是 id
            $table->string('province_id');   // code
            $table->string('city_id');       // code

            $table->string('address');
            $table->string('cover');
            $table->time('time_start');
            $table->time('time_close');
            $table->string('price_level');
            $table->string('latitute');
            $table->string('longitute');

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // 常用查询索引
            $table->index(['province_id', 'city_id']);

            // 外键到 locations.code（需要 locations.code 全局唯一）
            $table->foreign('province_id')
                  ->references('code')->on('locations')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->foreign('city_id')
                  ->references('code')->on('locations')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['city_id']);
            $table->dropIndex(['province_id', 'city_id']);
        });

        Schema::dropIfExists('stores');
    }
};
