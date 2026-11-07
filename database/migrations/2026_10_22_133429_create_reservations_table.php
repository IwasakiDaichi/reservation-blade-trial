<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            // Applicant
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('department');
            $table->string('mobile_number');
            $table->text('purpose');

            // Venue and schedule
            $table->string('venue');
            $table->string('other_venue')->nullable();
            $table->date('reserved_date');
            $table->string('time');
            
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            

            // Equipment requests
            $table->boolean('sound_system')->default(false);
            $table->boolean('microphone')->default(false);
            $table->boolean('video_wall')->default(false);
            $table->boolean('flags')->default(false);
            $table->boolean('podium')->default(false);
            $table->boolean('platform')->default(false);

            // Physical setup
            $table->unsignedInteger('tables')->default(0);
            $table->unsignedInteger('chairs')->default(0);
            $table->boolean('removal_of_rings')->default(false);
            $table->text('other_requirements')->nullable();

            // Agreement and approval
            $table->boolean('agreed')->default(false);
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};