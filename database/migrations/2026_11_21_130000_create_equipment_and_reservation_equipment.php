<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * slug => [name, input type, sort order, old toggle column or null, old details column or null]
     * input type: details = free text, toggle = yes/no, quantity = a number
     */
    private const ITEMS = [
        'sound-system'     => ['Sound System', 'details', 1, 'sound_system', 'sound_system_details'],
        'microphone'       => ['Microphone', 'details', 2, 'microphone', 'microphone_details'],
        'led-video-wall'   => ['LED Video Wall', 'details', 3, 'video_wall', 'video_wall_details'],
        'flags'            => ['Flags (UB and Phils.)', 'toggle', 4, 'flags', null],
        'podium'           => ['Podium', 'toggle', 5, 'podium', null],
        'platform'         => ['Platform', 'toggle', 6, 'platform', null],
        'tables'           => ['Tables', 'quantity', 7, 'tables', null],
        'chairs'           => ['Chairs', 'quantity', 8, 'chairs', null],
        'removal-of-rings' => ['Removal of Rings-Goal (GYM)', 'toggle', 9, 'removal_of_rings', null],
    ];

    private const OLD_COLUMNS = [
        'sound_system', 'sound_system_details', 'microphone', 'microphone_details',
        'video_wall', 'video_wall_details', 'flags', 'podium', 'platform',
        'tables', 'chairs', 'removal_of_rings',
    ];

    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('input_type', 20);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('reservation_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $table->unsignedInteger('quantity')->nullable();
            $table->string('details')->nullable();
            $table->unique(['reservation_id', 'equipment_id']);
        });

        $ids = [];
        foreach (self::ITEMS as $slug => [$name, $type, $order]) {
            $ids[$slug] = DB::table('equipment')->insertGetId([
                'name' => $name, 'slug' => $slug, 'input_type' => $type, 'sort_order' => $order,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        DB::table('reservations')->orderBy('id')->chunkById(200, function ($reservations) use ($ids) {
            $rows = [];

            foreach ($reservations as $r) {
                foreach (self::ITEMS as $slug => [, $type, , $column, $detailsColumn]) {
                    $details = $detailsColumn ? trim((string) ($r->{$detailsColumn} ?? '')) : '';

                    if ($type === 'quantity') {
                        $quantity = (int) ($r->{$column} ?? 0);
                        if ($quantity > 0) {
                            $rows[] = ['reservation_id' => $r->id, 'equipment_id' => $ids[$slug], 'quantity' => $quantity, 'details' => null];
                        }
                    } elseif ($details !== '' || $r->{$column}) {
                        $rows[] = ['reservation_id' => $r->id, 'equipment_id' => $ids[$slug], 'quantity' => null, 'details' => $details !== '' ? $details : null];
                    }
                }
            }

            if ($rows !== []) {
                DB::table('reservation_equipment')->insert($rows);
            }
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(self::OLD_COLUMNS);
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->boolean('sound_system')->default(false);
            $table->string('sound_system_details')->nullable();
            $table->boolean('microphone')->default(false);
            $table->string('microphone_details')->nullable();
            $table->boolean('video_wall')->default(false);
            $table->string('video_wall_details')->nullable();
            $table->boolean('flags')->default(false);
            $table->boolean('podium')->default(false);
            $table->boolean('platform')->default(false);
            $table->unsignedInteger('tables')->default(0);
            $table->unsignedInteger('chairs')->default(0);
            $table->boolean('removal_of_rings')->default(false);
        });

        $slugs = DB::table('equipment')->pluck('slug', 'id');

        foreach (DB::table('reservation_equipment')->get() as $row) {
            $item = self::ITEMS[$slugs[$row->equipment_id] ?? ''] ?? null;
            if (! $item) {
                continue;
            }

            [, $type, , $column, $detailsColumn] = $item;
            $update = $type === 'quantity' ? [$column => $row->quantity ?? 0] : [$column => true];

            if ($detailsColumn) {
                $update[$detailsColumn] = $row->details;
            }

            DB::table('reservations')->where('id', $row->reservation_id)->update($update);
        }

        Schema::dropIfExists('reservation_equipment');
        Schema::dropIfExists('equipment');
    }
};
