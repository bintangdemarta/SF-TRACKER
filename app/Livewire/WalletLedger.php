<?php

namespace App\Livewire;

use App\Services\WalletReconciliationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class WalletLedger extends Component
{
    public int $cashBalance = 0;
    public int $digitalBalance = 0;

    public $transfer_amount = '';

    public function mount(WalletReconciliationService $wallet): void
    {
        $this->refreshBalances($wallet);
    }

    #[On('wallet-updated')]
    public function refreshBalances(WalletReconciliationService $wallet): void
    {
        $this->cashBalance = $wallet->cashBalance(Auth::user());
        $this->digitalBalance = $wallet->digitalBalance(Auth::user());
    }

    public function transferToCash(WalletReconciliationService $wallet): void
    {
        $validated = $this->validate([
            'transfer_amount' => ['required', 'integer', 'min:1'],
        ]);

        $wallet->transferDigitalToCash(Auth::user(), $validated['transfer_amount']);

        $this->reset('transfer_amount');
        $this->refreshBalances($wallet);
    }

    public function render()
    {
        return view('livewire.wallet-ledger');
    }
}
