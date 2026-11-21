<?php

namespace App\Models;

use App\Enums\VenueStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'capacity_min',
        'capacity_max',
        'amenities',
        'rules',
        'image_path',
        'status',
        'status_note',
        'available_from',
    ];

    protected function casts(): array
    {
        return [
            'status' => VenueStatus::class,
            'capacity_min' => 'integer',
            'capacity_max' => 'integer',
            'available_from' => 'date',
        ];
    }

    /** Venues students may browse: active and under maintenance (never deactivated). */
    public function scopeVisibleToStudents(Builder $query): Builder
    {
        return $query->whereIn(
            'status',
            array_map(fn (VenueStatus $status) => $status->value, VenueStatus::visibleToStudents())
        );
    }

    public function isReservable(): bool
    {
        return $this->status->isReservable();
    }

    /** "150–200" for a range, or "100" when min and max are the same or no minimum is set. */
    public function capacityLabel(): string
    {
        if ($this->capacity_min && $this->capacity_min !== $this->capacity_max) {
            return number_format($this->capacity_min).'–'.number_format($this->capacity_max);
        }

        return number_format($this->capacity_max);
    }

    /** One amenity per line in the database, returned as a clean array. */
    public function amenityList(): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\R/', (string) $this->amenities) ?: [])
        ));
    }

    /** Public URL of the venue photo, or null. Needs `php artisan storage:link`. */
    public function imageUrl(): ?string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : null;
    }
}
