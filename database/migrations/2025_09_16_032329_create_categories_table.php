<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \App\Enums\CategoryType;
use \App\Enums\DiscountType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('store_id')->nullable()->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->tinyInteger('type')
          ->default(CategoryType::Normal->value)
          ->comment('10=normal, 20=discount');

        $table->tinyInteger('discount_type')
          ->default(DiscountType::None->value)
          ->comment('0=none,10=percentage,20=actual,30=fix');
        $table->decimal('discount_value', 10, 2)->nullable();
        $table->boolean('is_active')->default(true);
        $table->unsignedBigInteger('created_by')->nullable();
        $table->string('created_by_name')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->string('updated_by_name')->nullable();
        $table->timestamps();
        $table->softDeletes();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
