<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = ['name', 'slug', 'input_type', 'sort_order'];

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'reservation_equipment')
            ->withPivot('quantity', 'details');
    }
}
