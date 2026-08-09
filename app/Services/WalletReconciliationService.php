<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\TripLog;
use App\Models\User;
use App\Models\WalletTransaction;

class WalletReconciliationService
{
    public function cashBalance(User $user): int
    {
        return (int) (WalletTransaction::where('user_id', $user->id)
            ->where('source_wallet', 'cash')
            ->latest('id')
            ->value('balance_after') ?? 0);
    }

    public function digitalBalance(User $user): int
    {
        return (int) (WalletTransaction::where('user_id', $user->id)
            ->where('source_wallet', 'digital')
            ->latest('id')
            ->value('balance_after') ?? 0);
    }

    /**
     * Post trip income to the correct wallets: cash tips go to the physical
     * cash wallet, fare + app tips go to the digital wallet.
     */
    public function recordTripIncome(TripLog $trip): void
    {
        $user = $trip->shiftSession->user;

        if ($trip->tip_cash > 0) {
            $this->post($user, 'deposit', 'cash', $trip->tip_cash);
        }

        $digitalAmount = $trip->fare_amount + $trip->tip_app;
        if ($digitalAmount > 0) {
            $this->post($user, 'deposit', 'digital', $digitalAmount);
        }
    }

    /**
     * Post an expense as a withdrawal from its payment source wallet.
     * Returns a warning string if a cash expense exceeds available cash
     * (FR-3.2 Cash-in-Hand Safety Check) — the transaction still posts so
     * the ledger reflects reality even when the driver goes into deficit.
     */
    public function recordExpense(Expense $expense): ?string
    {
        $wallet = $expense->payment_source === 'cash' ? 'cash' : 'digital';

        $warning = null;
        if ($wallet === 'cash') {
            $available = $this->cashBalance($expense->user);
            if ($expense->amount > $available) {
                $warning = "Pengeluaran tunai Rp".number_format($expense->amount, 0, ',', '.')
                    ." melebihi kas fisik yang tersedia (Rp".number_format($available, 0, ',', '.').").";
            }
        }

        $this->post($expense->user, 'withdraw', $wallet, -$expense->amount, $expense);

        return $warning;
    }

    /**
     * Reimburse a cash outlay (e.g. "uang talangan resto") back into the
     * digital wallet and close the loop on the originating expense.
     */
    public function reimburse(Expense $expense, ?int $amount = null): WalletTransaction
    {
        $amount = $amount ?? $expense->amount;

        $transaction = $this->post($expense->user, 'reimbursement', 'digital', $amount, $expense);

        $expense->update([
            'reimbursed_at' => now(),
            'reimbursement_wallet_transaction_id' => $transaction->id,
        ]);

        return $transaction;
    }

    /**
     * Move funds from the digital wallet to physical cash-in-hand
     * ("Penarikan Kas" in the PRD's dual-wallet diagram).
     */
    public function transferDigitalToCash(User $user, int $amount): void
    {
        $this->post($user, 'withdraw', 'digital', -$amount);
        $this->post($user, 'deposit', 'cash', $amount);
    }

    protected function post(User $user, string $type, string $wallet, int $signedAmount, ?Expense $expense = null): WalletTransaction
    {
        $current = $wallet === 'cash' ? $this->cashBalance($user) : $this->digitalBalance($user);

        return WalletTransaction::create([
            'user_id' => $user->id,
            'type' => $type,
            'source_wallet' => $wallet,
            'amount' => $signedAmount,
            'balance_after' => $current + $signedAmount,
            'expense_id' => $expense?->id,
        ]);
    }
}
