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
            'tables' => 'boolean',
            'chairs' => 'boolean',
            'removal_of_rings' => 'boolean',
            'agreed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}