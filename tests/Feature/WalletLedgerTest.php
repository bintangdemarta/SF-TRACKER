<?php

namespace Tests\Feature;

use App\Livewire\ShiftTracker;
use App\Livewire\WalletLedger;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WalletLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_zero_balances_for_new_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeText('Kas Tunai Fisik');
    }

    public function test_logging_a_trip_updates_the_ledger_balance(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->set('start_odometer', 1000)
            ->set('target_income', 100000)
            ->call('startShift')
            ->set('fare_amount', 20000)
            ->set('tip_cash', 5000)
            ->call('logTrip');

        Livewire::actingAs($user)
            ->test(WalletLedger::class)
            ->assertSet('cashBalance', 5000)
            ->assertSet('digitalBalance', 20000);
    }

    public function test_user_can_transfer_digital_balance_to_cash(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->set('start_odometer', 1000)
            ->set('target_income', 100000)
            ->call('startShift')
            ->set('fare_amount', 50000)
            ->call('logTrip');

        Livewire::actingAs($user)
            ->test(WalletLedger::class)
            ->set('transfer_amount', 15000)
            ->call('transferToCash')
            ->assertSet('cashBalance', 15000)
            ->assertSet('digitalBalance', 35000);
    }

    public function test_transfer_requires_positive_amount(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(WalletLedger::class)
            ->set('transfer_amount', 0)
            ->call('transferToCash')
            ->assertHasErrors(['transfer_amount']);
    }
}
