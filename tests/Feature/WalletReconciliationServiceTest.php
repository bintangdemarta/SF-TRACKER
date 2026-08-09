<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ShiftSession;
use App\Models\User;
use App\Services\WalletReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletReconciliationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WalletReconciliationService $wallet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wallet = app(WalletReconciliationService::class);
    }

    protected function makeShift(User $user): ShiftSession
    {
        return ShiftSession::create([
            'user_id' => $user->id,
            'start_odometer' => 1000,
            'target_income' => 150000,
            'started_at' => now(),
            'status' => 'active',
        ]);
    }

    public function test_new_user_has_zero_balances(): void
    {
        $user = User::factory()->create();

        $this->assertSame(0, $this->wallet->cashBalance($user));
        $this->assertSame(0, $this->wallet->digitalBalance($user));
    }

    public function test_trip_income_splits_between_cash_and_digital_wallet(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);
        $trip = $shift->tripLogs()->create([
            'fare_amount' => 15000,
            'tip_cash' => 5000,
            'tip_app' => 2000,
            'points_earned' => 1,
        ]);

        $this->wallet->recordTripIncome($trip);

        $this->assertSame(5000, $this->wallet->cashBalance($user));
        $this->assertSame(17000, $this->wallet->digitalBalance($user));
    }

    public function test_multiple_trips_accumulate_balances(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);

        foreach ([
            ['fare_amount' => 10000, 'tip_cash' => 2000, 'tip_app' => 0, 'points_earned' => 1],
            ['fare_amount' => 12000, 'tip_cash' => 0, 'tip_app' => 1000, 'points_earned' => 1],
        ] as $data) {
            $trip = $shift->tripLogs()->create($data);
            $this->wallet->recordTripIncome($trip);
        }

        $this->assertSame(2000, $this->wallet->cashBalance($user));
        $this->assertSame(23000, $this->wallet->digitalBalance($user));
    }

    public function test_cash_expense_reduces_cash_balance_without_warning_when_sufficient(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);
        $trip = $shift->tripLogs()->create(['fare_amount' => 0, 'tip_cash' => 20000, 'tip_app' => 0, 'points_earned' => 0]);
        $this->wallet->recordTripIncome($trip);

        $category = ExpenseCategory::create(['name' => 'BBM', 'type' => 'bbm']);
        $expense = Expense::create([
            'user_id' => $user->id,
            'shift_session_id' => $shift->id,
            'category_id' => $category->id,
            'amount' => 15000,
            'payment_source' => 'cash',
        ]);

        $warning = $this->wallet->recordExpense($expense);

        $this->assertNull($warning);
        $this->assertSame(5000, $this->wallet->cashBalance($user));
    }

    public function test_cash_expense_exceeding_balance_returns_safety_warning(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);
        // No cash income posted — cash balance is 0.

        $category = ExpenseCategory::create(['name' => 'BBM', 'type' => 'bbm']);
        $expense = Expense::create([
            'user_id' => $user->id,
            'shift_session_id' => $shift->id,
            'category_id' => $category->id,
            'amount' => 20000,
            'payment_source' => 'cash',
        ]);

        $warning = $this->wallet->recordExpense($expense);

        $this->assertNotNull($warning);
        $this->assertStringContainsString('melebihi kas fisik', $warning);
        // Still posts, so the ledger reflects the real deficit.
        $this->assertSame(-20000, $this->wallet->cashBalance($user));
    }

    public function test_digital_expense_reduces_digital_balance_only(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);
        $trip = $shift->tripLogs()->create(['fare_amount' => 30000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);
        $this->wallet->recordTripIncome($trip);

        $category = ExpenseCategory::create(['name' => 'Sinking Fund', 'type' => 'sinking_fund']);
        $expense = Expense::create([
            'user_id' => $user->id,
            'shift_session_id' => $shift->id,
            'category_id' => $category->id,
            'amount' => 10000,
            'payment_source' => 'digital_balance',
        ]);

        $warning = $this->wallet->recordExpense($expense);

        $this->assertNull($warning);
        $this->assertSame(20000, $this->wallet->digitalBalance($user));
        $this->assertSame(0, $this->wallet->cashBalance($user));
    }

    public function test_reimbursement_credits_digital_wallet_and_marks_expense(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);
        $category = ExpenseCategory::create(['name' => 'Talangan Resto', 'type' => 'mikro']);
        $expense = Expense::create([
            'user_id' => $user->id,
            'shift_session_id' => $shift->id,
            'category_id' => $category->id,
            'amount' => 45000,
            'payment_source' => 'cash',
            'is_reimbursable' => true,
        ]);
        $this->wallet->recordExpense($expense);

        $transaction = $this->wallet->reimburse($expense);

        $expense->refresh();
        $this->assertNotNull($expense->reimbursed_at);
        $this->assertSame($transaction->id, $expense->reimbursement_wallet_transaction_id);
        $this->assertSame(45000, $this->wallet->digitalBalance($user));
        $this->assertSame('reimbursement', $transaction->type);
    }

    public function test_transfer_digital_to_cash_moves_funds_between_wallets(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);
        $trip = $shift->tripLogs()->create(['fare_amount' => 50000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);
        $this->wallet->recordTripIncome($trip);

        $this->wallet->transferDigitalToCash($user, 20000);

        $this->assertSame(30000, $this->wallet->digitalBalance($user));
        $this->assertSame(20000, $this->wallet->cashBalance($user));
    }
}
