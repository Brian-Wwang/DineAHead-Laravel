<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PaymentStatus;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            // 先建字段，不加外键
            $table->unsignedBigInteger('reservation_id')->nullable();

            $table->tinyInteger('payment_status')
                ->default(PaymentStatus::Unpaid->value)
                ->comment('10=unpaid,20=paid,30=refunded');

            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
