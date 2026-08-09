<?php

namespace Tests\Feature;

use App\Livewire\ExpenseLogger;
use App\Models\ExpenseCategory;
use App\Models\ShiftSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExpenseLoggerTest extends TestCase
{
    use RefreshDatabase;

    protected ExpenseCategory $bbm;
    protected ExpenseCategory $parkir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bbm = ExpenseCategory::create(['name' => 'BBM', 'type' => 'bbm']);
        $this->parkir = ExpenseCategory::create(['name' => 'Parkir', 'type' => 'mikro']);
    }

    public function test_dashboard_renders_expense_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Catat Pengeluaran');
    }

    public function test_user_can_log_a_cash_expense_within_balance(): void
    {
        $user = User::factory()->create();
        $shift = ShiftSession::create([
            'user_id' => $user->id, 'start_odometer' => 1000, 'target_income' => 100000,
            'started_at' => now(), 'status' => 'active',
        ]);
        $shift->tripLogs()->create(['fare_amount' => 0, 'tip_cash' => 30000, 'tip_app' => 0, 'points_earned' => 0]);
        app(\App\Services\WalletReconciliationService::class)
            ->recordTripIncome($shift->tripLogs()->first());

        Livewire::actingAs($user)
            ->test(ExpenseLogger::class)
            ->set('category_id', $this->parkir->id)
            ->set('amount', 5000)
            ->set('payment_source', 'cash')
            ->call('logExpense')
            ->assertSet('warningMessage', null)
            ->assertSee('Parkir');

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'shift_session_id' => $shift->id,
            'category_id' => $this->parkir->id,
            'amount' => 5000,
            'payment_source' => 'cash',
        ]);
    }

    public function test_cash_expense_exceeding_balance_shows_warning(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ExpenseLogger::class)
            ->set('category_id', $this->bbm->id)
            ->set('amount', 20000)
            ->set('payment_source', 'cash')
            ->call('logExpense')
            ->assertSet('warningMessage', fn ($message) => str_contains($message, 'melebihi kas fisik'));
    }

    public function test_bbm_category_reveals_odometer_field(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ExpenseLogger::class)
            ->set('category_id', $this->bbm->id)
            ->assertSet('showOdometerField', true)
            ->set('category_id', $this->parkir->id)
            ->assertSet('showOdometerField', false);
    }

    public function test_odometer_below_shift_start_is_rejected_for_bbm(): void
    {
        $user = User::factory()->create();
        ShiftSession::create([
            'user_id' => $user->id, 'start_odometer' => 1000, 'target_income' => 100000,
            'started_at' => now(), 'status' => 'active',
        ]);

        Livewire::actingAs($user)
            ->test(ExpenseLogger::class)
            ->set('category_id', $this->bbm->id)
            ->set('amount', 20000)
            ->set('odometer', 900)
            ->set('payment_source', 'digital_balance')
            ->call('logExpense')
            ->assertHasErrors(['odometer']);

        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_non_bbm_category_ignores_submitted_odometer(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ExpenseLogger::class)
            ->set('category_id', $this->parkir->id)
            ->set('amount', 3000)
            ->set('payment_source', 'digital_balance')
            ->call('logExpense');

        $this->assertDatabaseHas('expenses', [
            'category_id' => $this->parkir->id,
            'odometer' => null,
        ]);
    }

    public function test_expense_logs_without_an_active_shift(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ExpenseLogger::class)
            ->set('category_id', $this->parkir->id)
            ->set('amount', 3000)
            ->set('payment_source', 'digital_balance')
            ->call('logExpense');

        $this->assertDatabaseHas('expenses', [
            'shift_session_id' => null,
            'category_id' => $this->parkir->id,
        ]);
    }

    public function test_reimbursable_flag_is_persisted(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ExpenseLogger::class)
            ->set('category_id', $this->parkir->id)
            ->set('amount', 45000)
            ->set('payment_source', 'cash')
            ->set('is_reimbursable', true)
            ->call('logExpense');

        $this->assertDatabaseHas('expenses', [
            'category_id' => $this->parkir->id,
            'is_reimbursable' => true,
        ]);
    }

    public function test_amount_is_required_and_must_be_positive(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ExpenseLogger::class)
            ->set('category_id', $this->parkir->id)
            ->set('amount', 0)
            ->call('logExpense')
            ->assertHasErrors(['amount']);

        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_category_is_required(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ExpenseLogger::class)
            ->set('amount', 5000)
            ->call('logExpense')
            ->assertHasErrors(['category_id']);
    }
}
