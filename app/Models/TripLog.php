<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripLog extends Model
{
    protected $fillable = [
        'shift_session_id',
        'order_id',
        'fare_amount',
        'tip_cash',
        'tip_app',
        'points_earned',
        'distance_km',
    ];

    public function shiftSession(): BelongsTo
    {
        return $this->belongsTo(ShiftSession::class);
    }

    public function totalIncome(): int
    {
        return $this->fare_amount + $this->tip_cash + $this->tip_app;
    }
}
