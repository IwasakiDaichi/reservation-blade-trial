<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\SignatureRole;
use App\Enums\VenueStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'mobile_number',
        'purpose',
        'venue_id',
        'other_venue',
        'reserved_date',
        'time',
        'other_requirements',
        'agreed',
        'status',
        'rejection_reason',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'calendar_remarks',
        'cancelled_by',
        'cancellation_reason',
        'approved_at',
    ];

    protected static function booted(): void
    {
        // Record the approval date whenever the status changes.
        static::saving(function (Reservation $reservation) {
            if ($reservation->isDirty('status')) {
                $reservation->approved_at = $reservation->status === 'approved' ? now() : null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'reserved_date' => 'date',
            'agreed' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** The venue's name, or the typed-in name when "Other venue" was chosen. */
    public function venueName(): string
    {
        return $this->venue?->name ?? ($this->other_venue ?: 'Other venue');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'reservation_equipment')
            ->withPivot('quantity', 'details');
    }

    public function hasEquipment(string $slug): bool
    {
        return $this->equipment->contains('slug', $slug);
    }

    public function equipmentDetails(string $slug): ?string
    {
        return $this->equipment->firstWhere('slug', $slug)?->pivot->details;
    }

    public function equipmentQuantity(string $slug): int
    {
        return (int) ($this->equipment->firstWhere('slug', $slug)?->pivot->quantity ?? 0);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(ReservationSignature::class);
    }

    public function signaturePath(SignatureRole $role): ?string
    {
        return $this->signatures->firstWhere('role', $role->value)?->path;
    }

    /** "Dec 05, 2026" or "Dec 05, 2026 - Dec 07, 2026". */
    public function dateText(): string
    {
        $start = $this->start_date ?? $this->reserved_date;
        $end = $this->end_date ?? $start;

        return $start->isSameDay($end)
            ? $start->format('M d, Y')
            : $start->format('M d, Y').' - '.$end->format('M d, Y');
    }

    /** True while the venue is under maintenance for this reservation's date. */
    public function affectedByMaintenance(): bool
    {
        $venue = $this->venue;

        if (! $venue || $venue->status !== VenueStatus::Maintenance) {
            return false;
        }

        if (! in_array($this->status, ['pending', 'approved'], true)) {
            return false;
        }

        $start = $this->start_date ?? $this->reserved_date;
        $end = $this->end_date ?? $start;

        if ($end->lt(today())) {
            return false;
        }

        return $venue->available_from === null || $start->lt($venue->available_from);
    }

    /** Last day the student may still cancel (CPDO rule: three days before the reserved date). */
    public function cancellationDeadline(): Carbon
    {
        $start = $this->start_date ?? $this->reserved_date;

        return $start->copy()->subDays((int) config('reservation.cancellation_notice_days'));
    }

    public function canBeCancelledByOwner(): bool
    {
        if (! in_array($this->status, ['pending', 'approved'], true)) {
            return false;
        }

        return $this->affectedByMaintenance() || today()->lte($this->cancellationDeadline());
    }

    /**
     * Other pending or approved reservations at the same venue that overlap in date AND time.
     */
    public static function conflictsFor(int $venueId, string $startDate, string $endDate, string $startTime, string $endTime, ?int $exceptId = null): Collection
    {
        $newStart = Carbon::createFromFormat('H:i', substr($startTime, 0, 5));
        $newEnd = Carbon::createFromFormat('H:i', substr($endTime, 0, 5));

        return static::query()
            ->with('user', 'department', 'venue')
            ->where('venue_id', $venueId)
            ->whereIn('status', ['pending', 'approved'])
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->whereRaw('DATE(COALESCE(start_date, reserved_date)) <= ?', [$endDate])
            ->whereRaw('DATE(COALESCE(end_date, reserved_date)) >= ?', [$startDate])
            ->get()
            ->filter(function (Reservation $other) use ($newStart, $newEnd) {
                if (! $other->start_time || ! $other->end_time) {
                    return true; // no times recorded, treat the whole day as taken
                }

                $otherStart = Carbon::createFromFormat('H:i', substr((string) $other->start_time, 0, 5));
                $otherEnd = Carbon::createFromFormat('H:i', substr((string) $other->end_time, 0, 5));

                return $newStart->lt($otherEnd) && $newEnd->gt($otherStart);
            })
            ->values();
    }
}
