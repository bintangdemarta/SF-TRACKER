<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ShiftSession;
use App\Models\User;
use App\Services\PerformanceMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PerformanceMetricsService $performance;

    protected ExpenseCategory $bbm;

    protected ExpenseCategory $pemeliharaan;

    protected ExpenseCategory $parkir;

    protected ExpenseCategory $sinkingFund;

    protected function setUp(): void
    {
        parent::setUp();
        $this->performance = app(PerformanceMetricsService::class);
        $this->bbm = ExpenseCategory::create(['name' => 'BBM', 'type' => 'bbm']);
        $this->pemeliharaan = ExpenseCategory::create(['name' => 'Servis', 'type' => 'pemeliharaan']);
        $this->parkir = ExpenseCategory::create(['name' => 'Parkir', 'type' => 'mikro']);
        $this->sinkingFund = ExpenseCategory::create(['name' => 'Sinking Fund', 'type' => 'sinking_fund']);
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

    protected function expense(User $user, ShiftSession $shift, ExpenseCategory $category, int $amount, ?float $liters = null): Expense
    {
        return Expense::create([
            'user_id' => $user->id,
            'shift_session_id' => $shift->id,
            'category_id' => $category->id,
            'amount' => $amount,
            'payment_source' => 'cash',
            'fuel_liters' => $liters,
        ]);
    }

    // --- directOperationalCost ---

    public function test_direct_operational_cost_includes_bbm_pemeliharaan_and_mikro(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);
        $this->expense($user, $shift, $this->bbm, 20000);
        $this->expense($user, $shift, $this->pemeliharaan, 15000);
        $this->expense($user, $shift, $this->parkir, 5000);

        $this->assertSame(40000, $this->performance->directOperationalCost($shift));
    }

    public function test_direct_operational_cost_excludes_sinking_fund(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user);
        $this->expense($user, $shift, $this->bbm, 20000);
        $this->expense($user, $shift, $this->sinkingFund, 10000);

        $this->assertSame(20000, $this->performance->directOperationalCost($shift));
    }

    // --- costPerKm ---

    public function test_cost_per_km_divides_direct_operational_cost_by_distance(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['start_odometer' => 1000, 'end_odometer' => 1050]);
        $this->expense($user, $shift, $this->bbm, 15000);
        $this->expense($user, $shift, $this->parkir, 10000);

        // (15000 + 10000) / 50 km = 500 Rp/km
        $this->assertSame(500.0, $this->performance->costPerKm($shift));
    }

    public function test_cost_per_km_is_null_when_distance_is_zero(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['end_odometer' => null]);
        $this->expense($user, $shift, $this->bbm, 20000);

        $this->assertNull($this->performance->costPerKm($shift));
    }

    public function test_cost_per_km_is_null_when_odometer_did_not_move(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['start_odometer' => 1000, 'end_odometer' => 1000]);
        $this->expense($user, $shift, $this->bbm, 20000);

        $this->assertNull($this->performance->costPerKm($shift));
    }

    // --- fuelEfficiency ---

    public function test_fuel_efficiency_divides_distance_by_liters(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['start_odometer' => 1000, 'end_odometer' => 1080]);
        $this->expense($user, $shift, $this->bbm, 20000, 2.0);

        // 80 km / 2 L = 40 km/L
        $this->assertSame(40.0, $this->performance->fuelEfficiency($shift));
    }

    public function test_fuel_efficiency_sums_liters_across_multiple_bbm_purchases(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['start_odometer' => 1000, 'end_odometer' => 1090]);
        $this->expense($user, $shift, $this->bbm, 10000, 1.0);
        $this->expense($user, $shift, $this->bbm, 10000, 1.5);

        $this->assertSame(2.5, $this->performance->fuelLiters($shift));
        // 90 km / 2.5 L = 36 km/L
        $this->assertSame(36.0, $this->performance->fuelEfficiency($shift));
    }

    public function test_fuel_efficiency_is_null_when_no_liters_recorded(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['start_odometer' => 1000, 'end_odometer' => 1080]);
        // BBM expense logged without a liter value (PRD: liter is optional).
        $this->expense($user, $shift, $this->bbm, 20000, null);

        $this->assertSame(0.0, $this->performance->fuelLiters($shift));
        $this->assertNull($this->performance->fuelEfficiency($shift));
    }

    public function test_fuel_efficiency_is_null_when_distance_is_zero_even_with_liters(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['end_odometer' => null]);
        $this->expense($user, $shift, $this->bbm, 20000, 2.0);

        $this->assertNull($this->performance->fuelEfficiency($shift));
    }

    public function test_fuel_efficiency_ignores_non_bbm_categories(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['start_odometer' => 1000, 'end_odometer' => 1050]);
        $this->expense($user, $shift, $this->pemeliharaan, 50000, null);

        $this->assertSame(0.0, $this->performance->fuelLiters($shift));
        $this->assertNull($this->performance->fuelEfficiency($shift));
    }

    // --- targetAchievementRate ---

    public function test_target_achievement_rate_computes_percentage_of_gross_revenue_to_target(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['target_income' => 100000]);
        $shift->tripLogs()->create(['fare_amount' => 75000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);

        $this->assertSame(75.0, $this->performance->targetAchievementRate($shift));
    }

    public function test_target_achievement_rate_can_exceed_100_percent(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['target_income' => 100000]);
        $shift->tripLogs()->create(['fare_amount' => 130000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);

        $this->assertSame(130.0, $this->performance->targetAchievementRate($shift));
    }

    public function test_target_achievement_rate_is_null_when_no_target_set(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['target_income' => 0]);
        $shift->tripLogs()->create(['fare_amount' => 50000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);

        $this->assertNull($this->performance->targetAchievementRate($shift));
    }

    // --- threshold levels ---

    public function test_cost_per_km_level_boundaries(): void
    {
        $this->assertSame('unknown', $this->performance->costPerKmLevel(null));
        $this->assertSame('good', $this->performance->costPerKmLevel(0.0));
        $this->assertSame('good', $this->performance->costPerKmLevel(500.0));
        $this->assertSame('ok', $this->performance->costPerKmLevel(500.01));
        $this->assertSame('ok', $this->performance->costPerKmLevel(1000.0));
        $this->assertSame('bad', $this->performance->costPerKmLevel(1000.01));
    }

    public function test_fuel_efficiency_level_boundaries(): void
    {
        $this->assertSame('unknown', $this->performance->fuelEfficiencyLevel(null));
        $this->assertSame('good', $this->performance->fuelEfficiencyLevel(40.0));
        $this->assertSame('good', $this->performance->fuelEfficiencyLevel(55.0));
        $this->assertSame('ok', $this->performance->fuelEfficiencyLevel(39.99));
        $this->assertSame('ok', $this->performance->fuelEfficiencyLevel(25.0));
        $this->assertSame('bad', $this->performance->fuelEfficiencyLevel(24.99));
    }

    public function test_target_achievement_level_boundaries(): void
    {
        $this->assertSame('unknown', $this->performance->targetAchievementLevel(null));
        $this->assertSame('good', $this->performance->targetAchievementLevel(100.0));
        $this->assertSame('good', $this->performance->targetAchievementLevel(150.0));
        $this->assertSame('ok', $this->performance->targetAchievementLevel(99.99));
        $this->assertSame('ok', $this->performance->targetAchievementLevel(70.0));
        $this->assertSame('bad', $this->performance->targetAchievementLevel(69.99));
    }

    // --- shift duration edge case ---

    public function test_summarize_handles_zero_minute_shift_duration(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['started_at' => now()]);

        $summary = $this->performance->summarize($shift);

        $this->assertSame(0.0, $summary['hours_worked']);
        $this->assertNull($summary['hourly_rate']);
    }

    public function test_summarize_guards_hourly_rate_for_sub_minute_shift_duration(): void
    {
        // Carbon's diffInMinutes() is precise (fractional), not truncated to
        // whole minutes — a 30s-old shift yields ~0.008h, not exactly 0.
        // hourlyRate() must still refuse to extrapolate from that: dividing
        // a real net profit by a near-zero duration produces a wildly
        // inflated, meaningless Rp/hour figure.
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['started_at' => now()->subSeconds(30)]);
        $shift->tripLogs()->create(['fare_amount' => 50000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);

        $summary = $this->performance->summarize($shift);

        $this->assertLessThan(1 / 60, $summary['hours_worked']);
        $this->assertNull($summary['hourly_rate']);
    }

    // --- summarize() ---

    public function test_summarize_returns_full_performance_shape(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['target_income' => 100000, 'start_odometer' => 1000, 'end_odometer' => 1050]);
        $shift->tripLogs()->create(['fare_amount' => 80000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);
        $this->expense($user, $shift, $this->bbm, 15000, 1.25);
        $this->expense($user, $shift, $this->parkir, 10000);

        $summary = $this->performance->summarize($shift);

        $this->assertSame(80000, $summary['gross_revenue']);
        $this->assertSame(25000, $summary['operational_cost']);
        $this->assertSame(55000, $summary['net_profit']);
        $this->assertSame(50.0, $summary['distance_km']);
        $this->assertSame(25000, $summary['direct_operational_cost']);
        $this->assertSame(500.0, $summary['cost_per_km']);
        $this->assertSame('good', $summary['cost_per_km_level']);
        $this->assertSame(1.25, $summary['fuel_liters']);
        $this->assertSame(40.0, $summary['fuel_efficiency_km_l']);
        $this->assertSame('good', $summary['fuel_efficiency_level']);
        $this->assertSame(80.0, $summary['target_achievement_pct']);
        $this->assertSame('ok', $summary['target_achievement_level']);
    }

    // --- summarizeForPeriod() ---

    public function test_summarize_for_period_aggregates_performance_metrics_across_shifts(): void
    {
        $user = User::factory()->create();

        $shift1 = $this->makeShift($user, [
            'started_at' => now()->subHours(6), 'ended_at' => now()->subHours(4),
            'start_odometer' => 1000, 'end_odometer' => 1040, 'target_income' => 50000, 'status' => 'closed',
        ]);
        $shift1->tripLogs()->create(['fare_amount' => 40000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);
        $this->expense($user, $shift1, $this->bbm, 10000, 1.0);

        $shift2 = $this->makeShift($user, [
            'started_at' => now()->subHours(2), 'ended_at' => now()->subHour(),
            'start_odometer' => 1040, 'end_odometer' => 1060, 'target_income' => 50000, 'status' => 'closed',
        ]);
        $shift2->tripLogs()->create(['fare_amount' => 30000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);
        $this->expense($user, $shift2, $this->parkir, 5000);

        $summary = $this->performance->summarizeForPeriod($user, now()->startOfDay(), now()->endOfDay());

        $this->assertSame(2, $summary['shift_count']);
        $this->assertSame(70000, $summary['gross_revenue']);
        $this->assertSame(15000, $summary['direct_operational_cost']);
        $this->assertSame(60.0, $summary['distance_km']);
        // (10000 + 5000) / 60 km = 250 Rp/km
        $this->assertSame(250.0, $summary['cost_per_km']);
        $this->assertSame('good', $summary['cost_per_km_level']);
        $this->assertSame(1.0, $summary['fuel_liters']);
        // total distance 60 km / total liters 1.0 L = 60 km/L (period-level)
        $this->assertSame(60.0, $summary['fuel_efficiency_km_l']);
        // (40000 + 30000) / (50000 + 50000) * 100 = 70%
        $this->assertSame(70.0, $summary['target_achievement_pct']);
        $this->assertSame('ok', $summary['target_achievement_level']);
    }

    public function test_summarize_for_period_returns_null_metrics_with_no_data(): void
    {
        $user = User::factory()->create();

        $summary = $this->performance->summarizeForPeriod($user, now()->startOfDay(), now()->endOfDay());

        $this->assertSame(0, $summary['shift_count']);
        $this->assertNull($summary['cost_per_km']);
        $this->assertSame('unknown', $summary['cost_per_km_level']);
        $this->assertNull($summary['fuel_efficiency_km_l']);
        $this->assertSame('unknown', $summary['fuel_efficiency_level']);
        $this->assertNull($summary['target_achievement_pct']);
        $this->assertSame('unknown', $summary['target_achievement_level']);
    }
}
