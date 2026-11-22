<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Venue name => [capacity min, capacity max, description, calendar short name,
     *                calendar color, calendar text color, old values saved in reservations.venue]
     */
    private function venues(): array
    {
        return [
            'UB Cardinals Gym'         => [2000, 2500, null,                      'UB GYM',          '#FF00FF', '#111827', ['Campus Gym']],
            'RCB Dome'                 => [null, 200,  'Seating: 200 armchairs.', 'RCB DOME',        '#00FFFF', '#111827', ['RCB Dome']],
            'FGB Hall'                 => [150,  200,  null,                      'FGB HALL',        '#93C47D', '#111827', ['FGB Hall']],
            'Centennial Hall'          => [100,  150,  null,                      'CENTENNIAL HALL', '#FF9900', '#111827', ['Centennial Hall', 'Centenial Hall']],
            'AMS Hall'                 => [null, 120,  'Seating: 120 armchairs.', 'AMS HALL',        '#4A86E8', '#FFFFFF', ['AMS Hall']],
            'Legacy Multipurpose Hall' => [150,  300,  null,                      'LEGACY HALL',     '#C27BA0', '#111827', ['Legacy']],
            'UB Student Center'        => [null, 100,  null,                      'STUDENT CENTER',  '#FFFF00', '#111827', ['UB Student Center']],
            'Alumni AVR'               => [null, 55,   null,                      'ALUMNI AVR',      '#00FF00', '#111827', ['Alumni AVR']],
            'Centennial AVR'           => [null, 35,   null,                      'CENTENNIAL AVR',  '#FF9900', '#111827', ['Centennial AVR']],
        ];
    }

    public function up(): void
    {
        // 1. Calendar details now live with the venue.
        Schema::table('venues', function (Blueprint $table) {
            $table->string('short_name', 50)->nullable()->after('name');
            $table->string('calendar_color', 7)->nullable();
            $table->string('calendar_text_color', 7)->nullable();
        });

        // 2. Make sure every venue the reservation form used exists, with its calendar details.
        $ids = [];
        foreach ($this->venues() as $name => [$min, $max, $description, $short, $bg, $text]) {
            $venue = DB::table('venues')->where('name', $name)->first();

            if ($venue) {
                DB::table('venues')->where('id', $venue->id)->update([
                    'short_name' => $venue->short_name ?? $short,
                    'calendar_color' => $venue->calendar_color ?? $bg,
                    'calendar_text_color' => $venue->calendar_text_color ?? $text,
                ]);
                $ids[$name] = $venue->id;
            } else {
                $ids[$name] = DB::table('venues')->insertGetId([
                    'name' => $name,
                    'short_name' => $short,
                    'description' => $description,
                    'capacity_min' => $min,
                    'capacity_max' => $max,
                    'status' => 'active',
                    'calendar_color' => $bg,
                    'calendar_text_color' => $text,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Reservations point to a venue by id.
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('venue_id')->nullable()->after('purpose')
                ->constrained('venues')->restrictOnDelete();
        });

        foreach ($this->venues() as $name => $details) {
            DB::table('reservations')
                ->whereIn('venue', $details[6])
                ->update(['venue_id' => $ids[$name]]);
        }

        // 4. Anything that is not a known venue ("Other", or a value we do not recognise)
        //    keeps its text in other_venue and has no venue_id.
        DB::table('reservations')
            ->whereNull('venue_id')
            ->where('venue', '!=', 'Other')
            ->whereNull('other_venue')
            ->update(['other_venue' => DB::raw('venue')]);

        // 5. The text column is no longer needed.
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('venue');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('venue')->default('Other');
        });

        foreach ($this->venues() as $name => $details) {
            $id = DB::table('venues')->where('name', $name)->value('id');

            if ($id) {
                DB::table('reservations')->where('venue_id', $id)->update(['venue' => $details[6][0]]);
            }
        }

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('venue_id');
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn(['short_name', 'calendar_color', 'calendar_text_color']);
        });
    }
};
