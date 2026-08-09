<?php

namespace Tests\Feature;

use App\Livewire\ShiftTracker;
use App\Models\ShiftSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShiftTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_start_shift_form_when_no_active_shift(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Mulai Shift');
    }

    public function test_user_can_start_a_shift(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->set('start_odometer', 1000)
            ->set('target_income', 150000)
            ->call('startShift')
            ->assertSet('start_odometer', '')
            ->assertSee('Catat Trip');

        $this->assertDatabaseHas('shift_sessions', [
            'user_id' => $user->id,
            'start_odometer' => 1000,
            'target_income' => 150000,
            'status' => 'active',
        ]);
    }

    public function test_user_cannot_start_shift_without_odometer(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->set('start_odometer', '')
            ->set('target_income', 150000)
            ->call('startShift')
            ->assertHasErrors(['start_odometer' => 'required']);

        $this->assertDatabaseCount('shift_sessions', 0);
    }

    public function test_user_can_log_a_trip_during_active_shift(): void
    {
        $user = User::factory()->create();
        $shift = ShiftSession::create([
            'user_id' => $user->id,
            'start_odometer' => 1000,
            'target_income' => 150000,
            'started_at' => now(),
            'status' => 'active',
        ]);

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->set('fare_amount', 15000)
            ->set('tip_cash', 5000)
            ->set('tip_app', 2000)
            ->set('points_earned', 3)
            ->call('logTrip')
            ->assertSee('Rp22.000');

        $this->assertDatabaseHas('trip_logs', [
            'shift_session_id' => $shift->id,
            'fare_amount' => 15000,
            'tip_cash' => 5000,
            'tip_app' => 2000,
            'points_earned' => 3,
        ]);
    }

    public function test_gross_revenue_sums_all_trips_in_active_shift(): void
    {
        $user = User::factory()->create();
        $shift = ShiftSession::create([
            'user_id' => $user->id,
            'start_odometer' => 1000,
            'target_income' => 150000,
            'started_at' => now(),
            'status' => 'active',
        ]);
        $shift->tripLogs()->create(['fare_amount' => 15000, 'tip_cash' => 1000, 'tip_app' => 0, 'points_earned' => 1]);
        $shift->tripLogs()->create(['fare_amount' => 20000, 'tip_cash' => 0, 'tip_app' => 2000, 'points_earned' => 2]);

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->assertSee('Rp38.000');
    }

    public function test_user_can_end_shift_with_valid_odometer(): void
    {
        $user = User::factory()->create();
        $shift = ShiftSession::create([
            'user_id' => $user->id,
            'start_odometer' => 1000,
            'target_income' => 150000,
            'started_at' => now(),
            'status' => 'active',
        ]);

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->set('end_odometer', 1080)
            ->call('endShift')
            ->assertSee('Mulai Shift');

        $this->assertDatabaseHas('shift_sessions', [
            'id' => $shift->id,
            'end_odometer' => 1080,
            'status' => 'closed',
        ]);
    }

    public function test_end_shift_rejects_odometer_lower_than_start(): void
    {
        $user = User::factory()->create();
        ShiftSession::create([
            'user_id' => $user->id,
            'start_odometer' => 1000,
            'target_income' => 150000,
            'started_at' => now(),
            'status' => 'active',
        ]);

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->set('end_odometer', 900)
            ->call('endShift')
            ->assertHasErrors(['end_odometer']);

        $this->assertDatabaseHas('shift_sessions', ['status' => 'active']);
    }

    public function test_starting_new_shift_does_not_reopen_a_closed_one(): void
    {
        $user = User::factory()->create();
        ShiftSession::create([
            'user_id' => $user->id,
            'start_odometer' => 500,
            'end_odometer' => 600,
            'target_income' => 100000,
            'started_at' => now()->subDay(),
            'ended_at' => now()->subDay()->addHours(8),
            'status' => 'closed',
        ]);

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->assertSee('Mulai Shift');
    }
}
