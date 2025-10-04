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
      Schema::create('store_option_values', function (Blueprint $table) {
        $table->id();
        $table->foreignId('store_option_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->decimal('extra_price', 10, 2)->default(0);
        $table->integer('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->unsignedBigInteger('created_by')->nullable()->comment('创建人ID')->after('is_active');
        $table->string('created_by_name')->nullable()->comment('创建人姓名')->after('create_by');
        $table->unsignedBigInteger('updated_by')->nullable()->comment('最后更新人ID')->after('create_by_name');
        $table->string('updated_by_name')->nullable()->comment('最后更新人姓名')->after('update_by');
        $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_option_values');
    }
};
