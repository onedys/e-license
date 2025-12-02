<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_license_id')->constrained()->onDelete('cascade');
            $table->text('installation_id'); // Encrypted
            $table->json('api_response');
            $table->enum('status', ['success', 'blocked', 'error']);
            $table->timestamps();
            
            $table->index(['user_license_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activation_logs');
    }
};