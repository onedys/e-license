<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('license_pool_id')->nullable()->constrained()->onDelete('set null');
            $table->text('license_key'); // Encrypted
            $table->enum('status', ['pending', 'active', 'blocked', 'replaced'])->default('pending');
            $table->integer('activation_attempts')->default(0);
            $table->text('installation_id')->nullable(); // Encrypted
            $table->text('confirmation_id')->nullable(); // Encrypted
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('warranty_until')->nullable();
            $table->foreignId('replaced_by')->nullable()->constrained('user_licenses')->onDelete('set null');
            $table->timestamp('replaced_at')->nullable();
            $table->boolean('is_replacement')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'status']);
            $table->index(['order_id', 'status']);
            $table->index('warranty_until');
            $table->index('activated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_licenses');
    }
};