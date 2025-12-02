<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->string('username')->unique()->after('id');
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->timestamp('email_verified_at')->nullable()->change();
            $table->string('phone')->nullable()->after('email');
            $table->boolean('is_admin')->default(false)->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'phone', 'is_admin']);
            $table->string('email')->unique()->change();
            $table->string('name')->nullable(false)->change();
        });
    }
};