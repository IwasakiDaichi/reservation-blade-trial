<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department',
        'mobile_number',
        'purpose',
        'venue',
        'other_venue',
        'reserved_date',
        'time',
        'sound_system',
        'microphone',
        'video_wall',
        'flags',
        'podium',
        'platform',
        'tables',
        'chairs',
        'removal_of_rings',
        'other_requirements',
        'agreed',
        'status',
        'rejection_reason',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'sound_system_details',
        'microphone_details',
        'video_wall_details',
        'applicant_signature_path',
        'security_signature_path',
        'endorsement_signature_path',
        'reservation_officer_signature_path',
        'approval_signature_path',
    ];

    protected function casts(): array
    {
        return [
            'reserved_date' => 'date',
            'sound_system' => 'boolean',
            'microphone' => 'boolean',
            'video_wall' => 'boolean',
            'flags' => 'boolean',
            'podium' => 'boolean',
            'platform' => 'boolean',
            'tables' => 'integer',
            'chairs' => 'integer',
            'removal_of_rings' => 'boolean',
            'agreed' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
