<?php

namespace Tests\Feature;

use App\Livewire\ExpenseLogger;
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

    protected ExpenseCategory $bbm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bbm = ExpenseCategory::create(['name' => 'BBM', 'type' => 'bbm']);
    }

    public function test_dashboard_shows_no_active_shift_section_when_none_running(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(FinancialDashboard::class)
            ->assertDontSee('Shift Berjalan')
            ->assertSee('Belum ada shift pada periode ini');
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

    public function test_dashboard_shows_week_summary_section(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(FinancialDashboard::class)
            ->assertSee('Ringkasan Minggu Ini');
    }

    public function test_dashboard_shows_efficient_fuel_badge_for_active_shift(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->set('start_odometer', 1000)
            ->set('target_income', 100000)
            ->call('startShift')
            ->set('fare_amount', 100000)
            ->call('logTrip')
            ->set('end_odometer', 1080)
            ->call('endShift');

        Livewire::actingAs($user)
            ->test(ExpenseLogger::class)
            ->set('category_id', $this->bbm->id)
            ->set('amount', 15000)
            ->set('fuel_liters', '2')
            ->set('payment_source', 'digital_balance')
            ->call('logExpense');

        // 80 km / 2 L = 40 km/L -> 'good' tier -> "Irit" badge.
        Livewire::actingAs($user)
            ->test(FinancialDashboard::class)
            ->assertSee('40.0 km/L')
            ->assertSee('Irit');
    }

    public function test_dashboard_shows_dash_for_metrics_with_no_data_yet(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->set('start_odometer', 1000)
            ->set('target_income', 100000)
            ->call('startShift');

        Livewire::actingAs($user)
            ->test(FinancialDashboard::class)
            ->assertSee('Shift Berjalan')
            ->assertSeeText('—')
            ->assertSee('Belum ada data');
    }
}
