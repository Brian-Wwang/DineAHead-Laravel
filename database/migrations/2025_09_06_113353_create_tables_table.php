<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\LocationType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('tables', function (Blueprint $table) {
        $table->id();
        $table->foreignId('store_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->text('description')->nullable();
        $table->foreignId('seat_level_id')
              ->constrained('seat_levels')
              ->nullOnDelete();
        $table->jsonb('images')->nullable();   // 存储图片URL数组
        $table->unsignedTinyInteger('location_type')
                ->default(LocationType::Indoor->value)
                ->nullable(false)
                ->check('location_type IN (10, 20, 30)');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes(); // is_delete
      });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
