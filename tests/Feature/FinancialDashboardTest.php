<?php

namespace Tests\Feature;

use App\Livewire\FinancialDashboard;
use App\Livewire\ShiftTracker;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinancialDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ExpenseCategory::create(['name' => 'BBM', 'type' => 'bbm']);
    }

    public function test_dashboard_shows_no_active_shift_section_when_none_running(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(FinancialDashboard::class)
            ->assertDontSee('Shift Berjalan')
            ->assertSee('Belum ada shift hari ini');
    }

    public function test_dashboard_shows_active_shift_metrics_after_logging_a_trip(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->set('start_odometer', 1000)
            ->set('target_income', 100000)
            ->call('startShift')
            ->set('fare_amount', 40000)
            ->call('logTrip');

        Livewire::actingAs($user)
            ->test(FinancialDashboard::class)
            ->assertSee('Shift Berjalan')
            ->assertSee('Rp40.000');
    }

    public function test_dashboard_shows_todays_aggregate_after_closing_a_shift(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->set('start_odometer', 1000)
            ->set('target_income', 100000)
            ->call('startShift')
            ->set('fare_amount', 60000)
            ->call('logTrip')
            ->set('end_odometer', 1030)
            ->call('endShift');

        Livewire::actingAs($user)
            ->test(FinancialDashboard::class)
            ->assertDontSee('Shift Berjalan')
            ->assertSee('1 shift')
            ->assertSee('Rp60.000');
    }
}
