<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty_exchanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_license_id')->constrained()->onDelete('cascade');
            $table->foreignId('new_license_pool_id')->constrained('license_pools')->onDelete('cascade');
            $table->foreignId('replacement_user_license_id')->nullable()->constrained('user_licenses')->onDelete('set null');
            $table->string('reason');
            $table->timestamp('approved_at')->nullable();
            $table->boolean('auto_approved')->default(false);
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['user_license_id', 'approved_at']);
            $table->index('auto_approved');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_exchanges');
    }
};