<?php

namespace App\Livewire;

use App\Models\ShiftSession;
use App\Services\FinancialMetricsService;
use App\Services\WalletReconciliationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShiftTracker extends Component
{
    public ?ShiftSession $activeShift = null;

    // Start shift form
    public $start_odometer = '';

    public $target_income = '';

    // Trip quick-log form
    public $order_id = '';

    public $fare_amount = '';

    public $tip_cash = '';

    public $tip_app = '';

    public $points_earned = '';

    // End shift form
    public $end_odometer = '';

    public function mount(): void
    {
        $this->loadActiveShift();
    }

    protected function loadActiveShift(): void
    {
        $this->activeShift = ShiftSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->latest('started_at')
            ->first();
    }

    public function startShift(): void
    {
        $validated = $this->validate([
            'start_odometer' => ['required', 'integer', 'min:0'],
            'target_income' => ['required', 'integer', 'min:0'],
        ]);

        ShiftSession::create([
            'user_id' => Auth::id(),
            'start_odometer' => $validated['start_odometer'],
            'target_income' => $validated['target_income'],
            'started_at' => now(),
            'status' => 'active',
        ]);

        $this->reset(['start_odometer', 'target_income']);
        $this->loadActiveShift();
        $this->dispatch('shift-updated');
    }

    public function logTrip(WalletReconciliationService $wallet): void
    {
        if (! $this->activeShift) {
            return;
        }

        $validated = $this->validate([
            'order_id' => ['nullable', 'string', 'max:255'],
            'fare_amount' => ['required', 'integer', 'min:0'],
            'tip_cash' => ['nullable', 'integer', 'min:0'],
            'tip_app' => ['nullable', 'integer', 'min:0'],
            'points_earned' => ['nullable', 'integer', 'min:0'],
        ]);

        $trip = $this->activeShift->tripLogs()->create([
            'order_id' => $validated['order_id'] ?: null,
            'fare_amount' => $validated['fare_amount'],
            'tip_cash' => $validated['tip_cash'] ?: 0,
            'tip_app' => $validated['tip_app'] ?: 0,
            'points_earned' => $validated['points_earned'] ?: 0,
        ]);

        $wallet->recordTripIncome($trip);

        $this->reset(['order_id', 'fare_amount', 'tip_cash', 'tip_app', 'points_earned']);
        $this->loadActiveShift();
        $this->dispatch('wallet-updated');
    }

    public function endShift(): void
    {
        if (! $this->activeShift) {
            return;
        }

        $validated = $this->validate([
            'end_odometer' => ['required', 'integer', 'min:'.$this->activeShift->start_odometer],
        ], [
            'end_odometer.min' => 'Odometer akhir tidak boleh lebih kecil dari odometer awal ('.$this->activeShift->start_odometer.' km).',
        ]);

        $this->activeShift->update([
            'end_odometer' => $validated['end_odometer'],
            'ended_at' => now(),
            'status' => 'closed',
        ]);

        $this->reset(['end_odometer']);
        $this->loadActiveShift();
        $this->dispatch('shift-updated');
    }

    public function getTripsProperty()
    {
        return $this->activeShift
            ? $this->activeShift->tripLogs()->latest()->get()
            : collect();
    }

    public function getGrossRevenueProperty(): int
    {
        return $this->activeShift
            ? app(FinancialMetricsService::class)->grossRevenue($this->activeShift)
            : 0;
    }

    public function getTripCountProperty(): int
    {
        return $this->trips->count();
    }

    public function render()
    {
        return view('livewire.shift-tracker');
    }
}
