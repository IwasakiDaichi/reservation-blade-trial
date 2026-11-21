<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** The departments shown in the dropdown. */
    private const LISTED = [
        'Laboratory Elementary School',
        'High School',
        'Science High School',
        'School of Business Administration & Accountancy',
        'School of Criminal Justice and Public Safety',
        'School of Dentistry',
        'School of Engineering & Architecture',
        'School of Information Technology',
        'School of International Hospitality & Tourism management',
        'School of Law',
        'School of Natural Sciences',
        'School of Nursing',
        'School of Teacher Education & Liberal Arts',
    ];

    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_listed')->default(true);
            $table->timestamps();
        });

        // key = lower-case name, value = department id
        $ids = [];
        foreach (self::LISTED as $name) {
            $ids[mb_strtolower($name)] = DB::table('departments')->insertGetId([
                'name' => $name, 'is_listed' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Anything students typed under "Others" becomes its own (unlisted) department.
        $typed = DB::table('users')->whereNotNull('department')->pluck('department')
            ->merge(DB::table('reservations')->whereNotNull('department')->pluck('department'))
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '' && strcasecmp($v, 'Others') !== 0)
            ->unique(fn ($v) => mb_strtolower($v));

        foreach ($typed as $name) {
            if (! isset($ids[mb_strtolower($name)])) {
                $ids[mb_strtolower($name)] = DB::table('departments')->insertGetId([
                    'name' => $name, 'is_listed' => false, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->restrictOnDelete();
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->restrictOnDelete();
        });

        foreach ($ids as $lower => $id) {
            DB::table('users')->whereRaw('LOWER(TRIM(department)) = ?', [$lower])->update(['department_id' => $id]);
            DB::table('reservations')->whereRaw('LOWER(TRIM(department)) = ?', [$lower])->update(['department_id' => $id]);
        }

        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('department'));
        Schema::table('reservations', fn (Blueprint $table) => $table->dropColumn('department'));
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->string('department')->nullable());
        Schema::table('reservations', fn (Blueprint $table) => $table->string('department')->default(''));

        foreach (DB::table('departments')->get() as $department) {
            DB::table('users')->where('department_id', $department->id)->update(['department' => $department->name]);
            DB::table('reservations')->where('department_id', $department->id)->update(['department' => $department->name]);
        }

        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('department_id'));
        Schema::table('reservations', fn (Blueprint $table) => $table->dropConstrainedForeignId('department_id'));

        Schema::dropIfExists('departments');
    }
};
