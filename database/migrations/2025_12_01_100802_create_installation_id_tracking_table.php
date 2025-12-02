<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installation_id_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('installation_id_hash')->unique();
            $table->foreignId('user_license_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('first_used_at');
            $table->timestamps();
            
            $table->index(['user_id', 'installation_id_hash']);
            $table->index('first_used_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installation_id_tracking');
    }
};