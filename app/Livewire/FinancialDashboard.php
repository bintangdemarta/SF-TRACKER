<?php

namespace App\Livewire;

use App\Models\ShiftSession;
use App\Services\FinancialMetricsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class FinancialDashboard extends Component
{
    public function getActiveShiftProperty(): ?ShiftSession
    {
        return ShiftSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->latest('started_at')
            ->first();
    }

    public function getActiveShiftMetricsProperty(): ?array
    {
        $shift = $this->activeShift;

        return $shift ? app(FinancialMetricsService::class)->summarize($shift) : null;
    }

    public function getTodayMetricsProperty(): array
    {
        return app(FinancialMetricsService::class)->summarizeForPeriod(
            Auth::user(),
            now()->startOfDay(),
            now()->endOfDay(),
        );
    }

    #[On('wallet-updated')]
    #[On('shift-updated')]
    public function refresh(): void
    {
        // Computed properties re-query on every render; this just forces the tick.
    }

    public function render()
    {
        return view('livewire.financial-dashboard');
    }
}
