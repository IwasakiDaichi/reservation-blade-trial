<?php

namespace App\Enums;

enum SignatureRole: string
{
    case Applicant = 'applicant';
    case Security = 'security';
    case Endorsement = 'endorsement';
    case ReservationOfficer = 'reservation_officer';
    case Approval = 'approval';

    public function label(): string
    {
        return match ($this) {
            self::Applicant => 'Applicant',
            self::Security => 'Security personnel',
            self::Endorsement => 'Dean / Director / Head of Office',
            self::ReservationOfficer => 'Reservation officer',
            self::Approval => 'CPDO Director (approval)',
        };
    }

    /** Name of the file input on the staff signature page. */
    public function fileField(): string
    {
        return $this->value.'_signature';
    }

    /** Signatures uploaded by CPDO staff (the applicant signs on the reservation form). */
    public static function staff(): array
    {
        return [self::Security, self::Endorsement, self::ReservationOfficer, self::Approval];
    }
}
