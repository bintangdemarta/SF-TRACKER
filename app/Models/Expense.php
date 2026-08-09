<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'shift_session_id',
        'category_id',
        'amount',
        'payment_source',
        'odometer',
        'fuel_liters',
        'notes',
        'is_reimbursable',
        'reimbursed_at',
        'reimbursement_wallet_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'is_reimbursable' => 'boolean',
            'reimbursed_at' => 'datetime',
            'fuel_liters' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shiftSession(): BelongsTo
    {
        return $this->belongsTo(ShiftSession::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function reimbursementTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'reimbursement_wallet_transaction_id');
    }

    public function isPendingReimbursement(): bool
    {
        return $this->is_reimbursable && $this->reimbursed_at === null;
    }
}
