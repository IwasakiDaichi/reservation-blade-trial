<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Walk-in / external applicants have no account, so there is no user.
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('applicant_name')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('applicant_name');
            // user_id stays nullable: walk-in rows may exist and have no user to point to.
        });
    }
};
