<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Manually typed calendar rows (e.g. UB events held outside).
        Schema::create('calendar_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('activity', 500);
            $table->string('department', 100)->nullable();
            $table->string('venue')->nullable();
            $table->string('remarks', 500)->nullable();
            $table->timestamps();
        });

        // Editable remarks and approval date for reservations.
        Schema::table('reservations', function (Blueprint $table) {
            $table->text('calendar_remarks')->nullable();
            $table->timestamp('approved_at')->nullable();
        });

        // Already-approved reservations: use their last update as the approval date.
        DB::table('reservations')
            ->where('status', 'approved')
            ->update(['approved_at' => DB::raw('updated_at')]);

        // Fix the old misspelling in saved reservations.
        DB::table('reservations')
            ->where('venue', 'Centenial Hall')
            ->update(['venue' => 'Centennial Hall']);
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['calendar_remarks', 'approved_at']);
        });

        Schema::dropIfExists('calendar_entries');
    }
};
