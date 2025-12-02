<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->text('license_key'); // Encrypted
            $table->string('keyname_with_dash');
            $table->string('errorcode')->nullable();
            $table->string('product_name')->nullable(); // prd dari API
            $table->boolean('is_retail')->default(true);
            $table->integer('remaining')->nullable();
            $table->integer('blocked')->default(0);
            $table->enum('status', ['active', 'blocked', 'invalid', 'exhausted'])->default('active');
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->integer('validation_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['product_id', 'status']);
            $table->index('keyname_with_dash');
            $table->index('validated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_pools');
    }
};