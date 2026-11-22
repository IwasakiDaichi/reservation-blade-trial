<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_listed'];

    protected function casts(): array
    {
        return ['is_listed' => 'boolean'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /** Names shown in the department dropdown. */
    public static function listedNames(): array
    {
        return static::query()->where('is_listed', true)->orderBy('id')->pluck('name')->all();
    }

    /**
     * Turns the form answer into a department row.
     * "Others" creates (or reuses) a department from the typed text without adding it to the dropdown.
     */
    public static function resolve(string $selected, ?string $other = null): self
    {
        $name = $selected === 'Others' ? trim((string) $other) : trim($selected);

        $existing = static::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

        return $existing ?? static::create(['name' => $name, 'is_listed' => false]);
    }
}
