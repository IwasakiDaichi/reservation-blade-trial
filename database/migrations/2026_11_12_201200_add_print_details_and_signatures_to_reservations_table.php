<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('sound_system_details')->nullable();
            $table->string('microphone_details')->nullable();
            $table->string('video_wall_details')->nullable();
            $table->string('applicant_signature_path')->nullable();
            $table->string('security_signature_path')->nullable();
            $table->string('endorsement_signature_path')->nullable();
            $table->string('reservation_officer_signature_path')->nullable();
            $table->string('approval_signature_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'sound_system_details',
                'microphone_details',
                'video_wall_details',
                'applicant_signature_path',
                'security_signature_path',
                'endorsement_signature_path',
                'reservation_officer_signature_path',
                'approval_signature_path',
            ]);
        });
    }
};
