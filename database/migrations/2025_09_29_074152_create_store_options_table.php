<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\OptionType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('store_options', function (Blueprint $table) {
        $table->id();
        $table->foreignId('store_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->string('type')
          ->default(OptionType::Single->value)
          ->nullable(false)
          ->check('location_type IN (10, 20)');
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
      Schema::dropIfExists('store_options');
    }
};
