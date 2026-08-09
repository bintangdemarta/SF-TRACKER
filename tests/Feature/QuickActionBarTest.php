<?php

namespace Tests\Feature;

use App\Livewire\QuickActionBar;
use App\Livewire\ShiftTracker;
use App\Models\ShiftSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuickActionBarTest extends TestCase
{
    use RefreshDatabase;

    public function test_bar_shows_start_shift_button_when_no_active_shift(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(QuickActionBar::class)
            ->assertSet('hasActiveShift', false)
            ->assertSee('Mulai Shift')
            ->assertDontSee('Akhiri');
    }

    public function test_bar_shows_action_buttons_when_shift_active(): void
    {
        $user = User::factory()->create();
        ShiftSession::create([
            'user_id' => $user->id,
            'start_odometer' => 1000,
            'target_income' => 100000,
            'started_at' => now(),
            'status' => 'active',
        ]);

        Livewire::actingAs($user)
            ->test(QuickActionBar::class)
            ->assertSet('hasActiveShift', true)
            ->assertSee('Trip')
            ->assertSee('Keluar')
            ->assertSee('Tunai')
            ->assertSee('Akhiri')
            ->assertDontSee('Mulai Shift');
    }

    public function test_bar_switches_immediately_after_starting_a_shift(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ShiftTracker::class)
            ->set('start_odometer', 1000)
            ->set('target_income', 100000)
            ->call('startShift');

        Livewire::actingAs($user)
            ->test(QuickActionBar::class)
            ->assertSee('Akhiri')
            ->assertDontSee('Mulai Shift');
    }
}
