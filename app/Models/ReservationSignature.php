<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationSignature extends Model
{
    protected $fillable = ['reservation_id', 'role', 'path'];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
