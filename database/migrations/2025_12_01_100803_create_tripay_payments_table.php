<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tripay_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('reference')->unique();
            $table->string('merchant_ref');
            $table->string('payment_method');
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->json('callback_response')->nullable();
            $table->timestamps();
            
            $table->index(['reference', 'status']);
            $table->index('merchant_ref');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tripay_payments');
    }
};