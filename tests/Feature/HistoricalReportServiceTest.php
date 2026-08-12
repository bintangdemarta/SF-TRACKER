<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ShiftSession;
use App\Models\User;
use App\Services\HistoricalReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use Tests\TestCase;

class HistoricalReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HistoricalReportService $reports;

    protected ExpenseCategory $bbm;

    protected ExpenseCategory $parkir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reports = app(HistoricalReportService::class);
        $this->bbm = ExpenseCategory::create(['name' => 'BBM', 'type' => 'bbm']);
        $this->parkir = ExpenseCategory::create(['name' => 'Parkir', 'type' => 'mikro']);
    }

    protected function makeShift(User $user, array $overrides = []): ShiftSession
    {
        return ShiftSession::create(array_merge([
            'user_id' => $user->id,
            'start_odometer' => 1000,
            'target_income' => 100000,
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
            'status' => 'closed',
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

    // --- resolvePeriod() ---

    public function test_resolve_period_daily_spans_today(): void
    {
        $range = $this->reports->resolvePeriod('daily');

        $this->assertTrue($range['from']->isToday());
        $this->assertTrue($range['from']->isStartOfDay());
        $this->assertTrue($range['to']->isToday());
    }

    public function test_resolve_period_weekly_spans_this_week(): void
    {
        $range = $this->reports->resolvePeriod('weekly');

        $this->assertTrue(now()->between($range['from'], $range['to']));
        $this->assertSame(now()->startOfWeek()->timestamp, $range['from']->timestamp);
    }

    public function test_resolve_period_monthly_spans_this_month(): void
    {
        $range = $this->reports->resolvePeriod('monthly');

        $this->assertSame(now()->startOfMonth()->timestamp, $range['from']->timestamp);
        $this->assertSame(now()->endOfMonth()->timestamp, $range['to']->timestamp);
    }

    public function test_resolve_period_custom_uses_full_day_boundaries(): void
    {
        $range = $this->reports->resolvePeriod('custom', '2026-01-05', '2026-01-10');

        $this->assertSame('2026-01-05 00:00:00', $range['from']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-10 23:59:59', $range['to']->format('Y-m-d H:i:s'));
    }

    public function test_resolve_period_custom_without_dates_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->reports->resolvePeriod('custom');
    }

    public function test_resolve_period_custom_with_from_after_to_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->reports->resolvePeriod('custom', '2026-02-01', '2026-01-01');
    }

    public function test_resolve_period_unknown_period_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->reports->resolvePeriod('yearly');
    }

    // --- summary() ---

    public function test_summary_delegates_to_performance_metrics_service(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['start_odometer' => 1000, 'end_odometer' => 1050]);
        $shift->tripLogs()->create(['fare_amount' => 60000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);
        $this->expense($user, $shift, $this->bbm, 10000, 1.0);

        $summary = $this->reports->summary($user, now()->startOfDay(), now()->endOfDay());

        $this->assertSame(1, $summary['shift_count']);
        $this->assertSame(60000, $summary['gross_revenue']);
        $this->assertSame(50000, $summary['net_profit']);
        $this->assertSame(50.0, $summary['distance_km']);
    }

    // --- shiftsForPeriod() / rowForShift() ---

    public function test_shifts_for_period_computes_correct_per_shift_breakdown(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, [
            'started_at' => now()->subHours(3),
            'ended_at' => now()->subHours(1),
            'start_odometer' => 1000,
            'end_odometer' => 1050,
            'target_income' => 100000,
        ]);
        $shift->tripLogs()->create(['fare_amount' => 80000, 'tip_cash' => 5000, 'tip_app' => 0, 'points_earned' => 0]);
        $this->expense($user, $shift, $this->bbm, 15000, 1.25);
        $this->expense($user, $shift, $this->parkir, 10000);

        $page = $this->reports->shiftsForPeriod($user, now()->startOfDay(), now()->endOfDay());
        $row = $page->items()[0];

        $this->assertSame($shift->id, $row['id']);
        $this->assertSame(85000, $row['gross_revenue']);
        $this->assertSame(25000, $row['operational_cost']);
        $this->assertSame(60000, $row['net_profit']);
        $this->assertSame(50.0, $row['jarak_km']);
        // (15000 + 10000) / 50 km = 500 Rp/km
        $this->assertSame(500.0, $row['cost_per_km']);
        $this->assertSame(1.25, $row['fuel_liters']);
        // 50 km / 1.25 L = 40 km/L
        $this->assertSame(40.0, $row['fuel_efficiency_km_l']);
        $this->assertSame(2.0, $row['jam_kerja']);
        // 85000 / 100000 * 100 = 85%
        $this->assertSame(85.0, $row['target_achievement_pct']);
    }

    public function test_shifts_for_period_excludes_other_users_shifts(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->makeShift($user);
        $this->makeShift($other);

        $page = $this->reports->shiftsForPeriod($user, now()->startOfDay(), now()->endOfDay());

        $this->assertSame(1, $page->total());
    }

    public function test_shifts_for_period_excludes_shifts_outside_range(): void
    {
        $user = User::factory()->create();
        $this->makeShift($user, ['started_at' => now()->subDays(10), 'ended_at' => now()->subDays(10)->addHour()]);
        $inRange = $this->makeShift($user);

        $page = $this->reports->shiftsForPeriod($user, now()->startOfDay(), now()->endOfDay());

        $this->assertSame(1, $page->total());
        $this->assertSame($inRange->id, $page->items()[0]['id']);
    }

    public function test_shifts_for_period_orders_most_recent_first(): void
    {
        $user = User::factory()->create();
        $older = $this->makeShift($user, ['started_at' => now()->subHours(5), 'ended_at' => now()->subHours(4)]);
        $newer = $this->makeShift($user, ['started_at' => now()->subHours(2), 'ended_at' => now()->subHour()]);

        $page = $this->reports->shiftsForPeriod($user, now()->startOfDay(), now()->endOfDay());
        $ids = array_column($page->items(), 'id');

        $this->assertSame([$newer->id, $older->id], $ids);
    }

    public function test_shifts_for_period_paginates(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 3) as $i) {
            $this->makeShift($user, ['started_at' => now()->subHours($i + 1), 'ended_at' => now()->subHours($i)]);
        }

        $page = $this->reports->shiftsForPeriod($user, now()->startOfDay(), now()->endOfDay(), perPage: 2, page: 1);

        $this->assertSame(3, $page->total());
        $this->assertCount(2, $page->items());
        $this->assertSame(2, $page->lastPage());
    }

    // --- exportRows() ---

    public function test_export_rows_is_memory_safe_lazy_collection_with_matching_data(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShift($user, ['start_odometer' => 1000, 'end_odometer' => 1020]);
        $shift->tripLogs()->create(['fare_amount' => 30000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);

        $rows = $this->reports->exportRows($user, now()->startOfDay(), now()->endOfDay());

        $this->assertInstanceOf(LazyCollection::class, $rows);
        $collected = $rows->all();
        $this->assertCount(1, $collected);
        $this->assertSame($shift->id, $collected[0]['id']);
        $this->assertSame(30000, $collected[0]['gross_revenue']);
    }
}
