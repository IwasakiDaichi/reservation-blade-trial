<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->string('mobile_number', 30)->nullable();
            $table->timestamp('mobile_verified_at')->nullable();

            $table->string('mobile_verification_code_hash')->nullable();
            $table->timestamp('mobile_verification_expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'department',
                'mobile_number',
                'mobile_verified_at',
                'mobile_verification_code_hash',
                'mobile_verification_expires_at',
            ]);
        });
    }
};