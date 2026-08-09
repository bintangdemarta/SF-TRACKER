<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ShiftSession;
use App\Models\User;
use App\Services\FinancialMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FinancialMetricsService $metrics;

    protected ExpenseCategory $bbm;

    protected ExpenseCategory $pemeliharaan;

    protected ExpenseCategory $parkir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->metrics = app(FinancialMetricsService::class);
        $this->bbm = ExpenseCategory::create(['name' => 'BBM', 'type' => 'bbm']);
        $this->pemeliharaan = ExpenseCategory::create(['name' => 'Servis', 'type' => 'pemeliharaan']);
        $this->parkir = ExpenseCategory::create(['name' => 'Parkir', 'type' => 'mikro']);
    }

    protected function makeShift(User $user, array $overrides = []): ShiftSession
    {
        return ShiftSession::create(array_merge([
            'user_id' => $user->id,
            'start_odometer' => 1000,
            'target_income' => 150000,
            'started_at' => now()->subHours(4),
            'status' => 'active',
        ], $overrides));
    }

    public function test_gross_revenue_sums_fare_and_tips_across_trips(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);
        $shift->tripLogs()->create(['fare_amount' => 15000, 'tip_cash' => 2000, 'tip_app' => 1000, 'points_earned' => 1]);
        $shift->tripLogs()->create(['fare_amount' => 20000, 'tip_cash' => 0, 'tip_app' => 3000, 'points_earned' => 1]);

        $this->assertSame(41000, $this->metrics->grossRevenue($shift));
    }

    public function test_operational_cost_sums_all_expense_categories(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);
        Expense::create(['user_id' => $user->id, 'shift_session_id' => $shift->id, 'category_id' => $this->bbm->id, 'amount' => 20000, 'payment_source' => 'cash']);
        Expense::create(['user_id' => $user->id, 'shift_session_id' => $shift->id, 'category_id' => $this->parkir->id, 'amount' => 5000, 'payment_source' => 'cash']);

        $this->assertSame(25000, $this->metrics->operationalCost($shift));
    }

    public function test_net_profit_is_gross_revenue_minus_operational_cost(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);
        $shift->tripLogs()->create(['fare_amount' => 50000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);
        Expense::create(['user_id' => $user->id, 'shift_session_id' => $shift->id, 'category_id' => $this->bbm->id, 'amount' => 15000, 'payment_source' => 'cash']);

        $this->assertSame(35000, $this->metrics->netProfit($shift));
    }

    public function test_net_profit_can_be_negative_when_expenses_exceed_revenue(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);
        $shift->tripLogs()->create(['fare_amount' => 10000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);
        Expense::create(['user_id' => $user->id, 'shift_session_id' => $shift->id, 'category_id' => $this->pemeliharaan->id, 'amount' => 250000, 'payment_source' => 'digital_balance']);

        $this->assertSame(-240000, $this->metrics->netProfit($shift));
    }

    public function test_hourly_rate_is_null_when_zero_hours_elapsed(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['started_at' => now()]);

        $this->assertSame(0.0, $this->metrics->hoursWorked($shift));
        $this->assertNull($this->metrics->hourlyRate($shift));
    }

    public function test_hourly_rate_is_null_for_sub_minute_duration_even_with_nonzero_hours(): void
    {
        // diffInMinutes() is precise/fractional, so a 30s-old shift yields
        // a tiny nonzero hours_worked. hourlyRate() must still refuse to
        // extrapolate a Rp/hour figure from a near-instantaneous duration.
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['started_at' => now()->subSeconds(30)]);
        $shift->tripLogs()->create(['fare_amount' => 50000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);

        $this->assertLessThan(1 / 60, $this->metrics->hoursWorked($shift));
        $this->assertNull($this->metrics->hourlyRate($shift));
    }

    public function test_hourly_rate_uses_ended_at_when_shift_is_closed(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, [
            'started_at' => now()->subHours(5),
            'ended_at' => now()->subHours(1),
            'end_odometer' => 1100,
            'status' => 'closed',
        ]);
        $shift->tripLogs()->create(['fare_amount' => 80000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);

        $this->assertSame(4.0, $this->metrics->hoursWorked($shift));
        $this->assertSame(20000.0, $this->metrics->hourlyRate($shift));
    }

    public function test_summarize_returns_all_expected_keys(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['end_odometer' => 1040]);
        $shift->tripLogs()->create(['fare_amount' => 30000, 'tip_cash' => 5000, 'tip_app' => 0, 'points_earned' => 0]);
        Expense::create(['user_id' => $user->id, 'shift_session_id' => $shift->id, 'category_id' => $this->bbm->id, 'amount' => 10000, 'payment_source' => 'cash']);

        $summary = $this->metrics->summarize($shift);

        $this->assertSame(35000, $summary['gross_revenue']);
        $this->assertSame(10000, $summary['operational_cost']);
        $this->assertSame(25000, $summary['net_profit']);
        $this->assertSame(40.0, $summary['distance_km']);
    }

    public function test_summarize_for_period_aggregates_multiple_shifts(): void
    {
        $user = User::factory()->create();
        $shift1 = $this->makeShift($user, [
            'started_at' => now()->subHours(6), 'ended_at' => now()->subHours(4),
            'start_odometer' => 1000, 'end_odometer' => 1030, 'status' => 'closed',
        ]);
        $shift1->tripLogs()->create(['fare_amount' => 20000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);

        $shift2 = $this->makeShift($user, [
            'started_at' => now()->subHours(2), 'ended_at' => now()->subHour(),
            'start_odometer' => 1030, 'end_odometer' => 1050, 'status' => 'closed',
        ]);
        $shift2->tripLogs()->create(['fare_amount' => 15000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);
        Expense::create(['user_id' => $user->id, 'shift_session_id' => $shift2->id, 'category_id' => $this->bbm->id, 'amount' => 8000, 'payment_source' => 'cash']);

        $summary = $this->metrics->summarizeForPeriod($user, now()->startOfDay(), now()->endOfDay());

        $this->assertSame(2, $summary['shift_count']);
        $this->assertSame(35000, $summary['gross_revenue']);
        $this->assertSame(8000, $summary['operational_cost']);
        $this->assertSame(27000, $summary['net_profit']);
        $this->assertSame(50.0, $summary['distance_km']);
    }

    public function test_summarize_for_period_includes_standalone_expenses_without_a_shift(): void
    {
        $user = User::factory()->create();
        Expense::create([
            'user_id' => $user->id, 'shift_session_id' => null,
            'category_id' => $this->pemeliharaan->id, 'amount' => 120000, 'payment_source' => 'digital_balance',
        ]);

        $summary = $this->metrics->summarizeForPeriod($user, now()->startOfDay(), now()->endOfDay());

        $this->assertSame(0, $summary['shift_count']);
        $this->assertSame(120000, $summary['operational_cost']);
        $this->assertSame(-120000, $summary['net_profit']);
    }

    public function test_summarize_for_period_excludes_shifts_outside_range(): void
    {
        $user = User::factory()->create();
        $this->travelTo(now()->subDays(3), function () use ($user) {
            $this->makeShift($user, [
                'started_at' => now(), 'ended_at' => now()->addHours(4),
                'end_odometer' => 1050, 'status' => 'closed',
            ])->tripLogs()->create(['fare_amount' => 99000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);
        });

        $summary = $this->metrics->summarizeForPeriod($user, now()->startOfDay(), now()->endOfDay());

        $this->assertSame(0, $summary['shift_count']);
        $this->assertSame(0, $summary['gross_revenue']);
    }

    public function test_summarize_for_period_returns_zeroed_shape_with_no_data(): void
    {
        $user = User::factory()->create();

        $summary = $this->metrics->summarizeForPeriod($user, now()->startOfDay(), now()->endOfDay());

        $this->assertSame(0, $summary['shift_count']);
        $this->assertSame(0, $summary['gross_revenue']);
        $this->assertSame(0, $summary['operational_cost']);
        $this->assertSame(0, $summary['net_profit']);
        $this->assertNull($summary['hourly_rate']);
    }
}
