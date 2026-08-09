<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftSession extends Model
{
    protected $fillable = [
        'user_id',
        'start_odometer',
        'end_odometer',
        'started_at',
        'ended_at',
        'target_income',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tripLogs(): HasMany
    {
        return $this->hasMany(TripLog::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function totalDistanceKm(): float
    {
        if ($this->end_odometer === null) {
            return 0;
        }

        return max(0, $this->end_odometer - $this->start_odometer);
    }
}
