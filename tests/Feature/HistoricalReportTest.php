<?php

namespace Tests\Feature;

use App\Livewire\HistoricalReport;
use App\Models\ShiftSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HistoricalReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_to_weekly_period_and_shows_empty_state(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(HistoricalReport::class)
            ->assertSet('period', 'weekly')
            ->assertSee('Belum ada shift pada periode ini');
    }

    public function test_shows_summary_for_a_shift_within_the_default_week(): void
    {
        $user = User::factory()->create();
        $shift = ShiftSession::create([
            'user_id' => $user->id, 'start_odometer' => 1000, 'end_odometer' => 1030,
            'target_income' => 60000, 'started_at' => now()->subHours(3), 'ended_at' => now()->subHours(2),
            'status' => 'closed',
        ]);
        $shift->tripLogs()->create(['fare_amount' => 45000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);

        Livewire::actingAs($user)
            ->test(HistoricalReport::class)
            ->assertSee('Rp45.000')
            ->assertDontSee('Belum ada shift pada periode ini');
    }

    public function test_switching_to_custom_period_without_dates_shows_range_error(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(HistoricalReport::class)
            ->set('period', 'custom')
            ->assertSet('rangeError', fn (?string $error) => $error !== null);
    }

    public function test_custom_period_with_valid_dates_clears_the_range_error(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(HistoricalReport::class)
            ->set('period', 'custom')
            ->set('customFrom', '2026-01-01')
            ->set('customTo', '2026-01-31')
            ->assertSet('rangeError', null);
    }

    public function test_export_urls_carry_the_current_filter_state(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(HistoricalReport::class)
            ->set('period', 'custom')
            ->set('customFrom', '2026-01-01')
            ->set('customTo', '2026-01-31');

        $url = $component->instance()->exportUrl('xlsx');

        $this->assertStringContainsString('period=custom', $url);
        $this->assertStringContainsString('from=2026-01-01', $url);
        $this->assertStringContainsString('format=xlsx', $url);
    }
}
