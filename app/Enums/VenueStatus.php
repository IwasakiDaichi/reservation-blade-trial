<?php

namespace App\Enums;

enum VenueStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Deactivated = 'deactivated';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Maintenance => 'Under maintenance',
            self::Deactivated => 'Deactivated',
        };
    }

    /** Tailwind classes for the status badge. */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Active => 'bg-green-100 text-green-800',
            self::Maintenance => 'bg-amber-100 text-amber-800',
            self::Deactivated => 'bg-gray-200 text-gray-700',
        };
    }

    /** Only active venues can be reserved. */
    public function isReservable(): bool
    {
        return $this === self::Active;
    }

    /** Statuses that students are allowed to see. */
    public static function visibleToStudents(): array
    {
        return [self::Active, self::Maintenance];
    }
}
