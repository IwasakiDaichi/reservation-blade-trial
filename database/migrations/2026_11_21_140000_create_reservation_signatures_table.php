<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** role => old column on reservations */
    private const ROLES = [
        'applicant'           => 'applicant_signature_path',
        'security'            => 'security_signature_path',
        'endorsement'         => 'endorsement_signature_path',
        'reservation_officer' => 'reservation_officer_signature_path',
        'approval'            => 'approval_signature_path',
    ];

    public function up(): void
    {
        Schema::create('reservation_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->string('role', 30);
            $table->string('path');
            $table->timestamps();
            $table->unique(['reservation_id', 'role']);
        });

        DB::table('reservations')->orderBy('id')->chunkById(200, function ($reservations) {
            $rows = [];

            foreach ($reservations as $r) {
                foreach (self::ROLES as $role => $column) {
                    if (filled($r->{$column} ?? null)) {
                        $rows[] = [
                            'reservation_id' => $r->id, 'role' => $role, 'path' => $r->{$column},
                            'created_at' => now(), 'updated_at' => now(),
                        ];
                    }
                }
            }

            if ($rows !== []) {
                DB::table('reservation_signatures')->insert($rows);
            }
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(array_values(self::ROLES));
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            foreach (self::ROLES as $column) {
                $table->string($column)->nullable();
            }
        });

        foreach (DB::table('reservation_signatures')->get() as $row) {
            $column = self::ROLES[$row->role] ?? null;
            if ($column) {
                DB::table('reservations')->where('id', $row->reservation_id)->update([$column => $row->path]);
            }
        }

        Schema::dropIfExists('reservation_signatures');
    }
};
