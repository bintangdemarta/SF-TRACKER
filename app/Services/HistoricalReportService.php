<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ShiftSession;
use App\Models\TripLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;

/**
 * FR-4.2 Historical Reporting: date-range filtering (daily/weekly/monthly/
 * custom) over past shifts, the period-aggregate summary (reusing
 * PerformanceMetricsService — no duplicate arithmetic), and the per-shift
 * breakdown list used for both the on-screen table and CSV/Excel export.
 *
 * The per-shift breakdown is built with correlated subqueries (not N+1
 * per-shift service calls) so it stays cheap to stream for export even as
 * shift history grows.
 */
class HistoricalReportService
{
    public const PERIODS = ['daily', 'weekly', 'monthly', 'custom'];

    public function __construct(protected PerformanceMetricsService $performance) {}

    /**
     * @return array{from: CarbonImmutable, to: CarbonImmutable}
     */
    public function resolvePeriod(string $period, ?string $from = null, ?string $to = null): array
    {
        if (! in_array($period, self::PERIODS, true)) {
            throw new InvalidArgumentException("Unknown report period: {$period}");
        }

        if ($period === 'custom') {
            if (! $from || ! $to) {
                throw new InvalidArgumentException('Custom period requires both from and to dates.');
            }

            $fromDate = CarbonImmutable::parse($from)->startOfDay();
            $toDate = CarbonImmutable::parse($to)->endOfDay();

            if ($fromDate->greaterThan($toDate)) {
                throw new InvalidArgumentException('Custom period "from" date must not be after "to" date.');
            }

            return ['from' => $fromDate, 'to' => $toDate];
        }

        $now = CarbonImmutable::now();

        return match ($period) {
            'daily' => ['from' => $now->startOfDay(), 'to' => $now->endOfDay()],
            'weekly' => ['from' => $now->startOfWeek(), 'to' => $now->endOfWeek()],
            'monthly' => ['from' => $now->startOfMonth(), 'to' => $now->endOfMonth()],
        };
    }

    /**
     * Period-aggregate figures (Total Net Profit, Total Jarak, Efisiensi
     * BBM, Rata-rata Cost/KM, etc.) — delegates to PerformanceMetricsService
     * so this stays the single source of truth for the arithmetic.
     */
    public function summary(User $user, CarbonInterface $from, CarbonInterface $to): array
    {
        return $this->performance->summarizeForPeriod($user, $from, $to);
    }

    /**
     * Per-shift breakdown query for the period, with gross revenue,
     * operational cost, direct operational cost, and fuel liters computed
     * via correlated subqueries (one query total, no N+1).
     *
     * @return Builder<ShiftSession>
     */
    public function shiftsQuery(User $user, CarbonInterface $from, CarbonInterface $to): Builder
    {
        $directCostTypes = $this->performance->directCostCategoryTypes();

        return ShiftSession::query()
            ->where('user_id', $user->id)
            ->whereBetween('started_at', [$from, $to])
            ->select('shift_sessions.*')
            ->selectSub(
                TripLog::query()
                    ->selectRaw('COALESCE(SUM(fare_amount + tip_cash + tip_app), 0)')
                    ->whereColumn('shift_session_id', 'shift_sessions.id'),
                'gross_revenue'
            )
            ->selectSub(
                Expense::query()
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('shift_session_id', 'shift_sessions.id'),
                'operational_cost'
            )
            ->selectSub(
                Expense::query()
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('shift_session_id', 'shift_sessions.id')
                    ->whereHas('category', fn (Builder $q) => $q->whereIn('type', $directCostTypes)),
                'direct_operational_cost'
            )
            ->selectSub(
                Expense::query()
                    ->selectRaw('COALESCE(SUM(fuel_liters), 0)')
                    ->whereColumn('shift_session_id', 'shift_sessions.id')
                    ->whereHas('category', fn (Builder $q) => $q->where('type', 'bbm')),
                'fuel_liters'
            )
            ->orderByDesc('started_at');
    }

    /**
     * $page left null lets Eloquent fall back to its bound current-page
     * resolver — Livewire's WithPagination trait registers one pointing at
     * the component's own `page` property, so leaving it null keeps
     * pagination links working from the component. Tests pass it explicitly.
     */
    public function shiftsForPeriod(User $user, CarbonInterface $from, CarbonInterface $to, int $perPage = 15, ?int $page = null): LengthAwarePaginator
    {
        return $this->shiftsQuery($user, $from, $to)->paginate($perPage, page: $page)
            ->through(fn (ShiftSession $shift) => $this->rowForShift($shift));
    }

    /**
     * Memory-safe row generator for CSV/Excel export — iterates via
     * cursor() so the full shift history is never loaded into memory at
     * once, no matter how large it grows.
     *
     * @return LazyCollection<int, array{
     *     id:int,tanggal:string,jam_mulai:string,jam_selesai:string,
     *     jarak_km:float,gross_revenue:int,operational_cost:int,net_profit:int,
     *     cost_per_km:?float,fuel_liters:float,fuel_efficiency_km_l:?float,
     *     jam_kerja:float,target_income:int,target_achievement_pct:?float
     * }>
     */
    public function exportRows(User $user, CarbonInterface $from, CarbonInterface $to): LazyCollection
    {
        return $this->shiftsQuery($user, $from, $to)->cursor()
            ->map(fn (ShiftSession $shift) => $this->rowForShift($shift));
    }

    /**
     * @return array{
     *     id:int,tanggal:string,jam_mulai:string,jam_selesai:string,
     *     jarak_km:float,gross_revenue:int,operational_cost:int,net_profit:int,
     *     cost_per_km:?float,fuel_liters:float,fuel_efficiency_km_l:?float,
     *     jam_kerja:float,target_income:int,target_achievement_pct:?float
     * }
     */
    protected function rowForShift(ShiftSession $shift): array
    {
        $grossRevenue = (int) $shift->getAttribute('gross_revenue');
        $operationalCost = (int) $shift->getAttribute('operational_cost');
        $directOperationalCost = (int) $shift->getAttribute('direct_operational_cost');
        $fuelLiters = round((float) $shift->getAttribute('fuel_liters'), 2);
        $distanceKm = $shift->totalDistanceKm();
        $netProfit = $grossRevenue - $operationalCost;

        $end = $shift->ended_at ?? CarbonImmutable::now();
        $hoursWorked = round($shift->started_at->diffInMinutes($end) / 60, 2);

        $costPerKm = $distanceKm > 0 ? round($directOperationalCost / $distanceKm, 2) : null;
        $fuelEfficiency = ($fuelLiters > 0 && $distanceKm > 0) ? round($distanceKm / $fuelLiters, 2) : null;
        $targetPct = $shift->target_income > 0 ? round($grossRevenue / $shift->target_income * 100, 1) : null;

        return [
            'id' => $shift->id,
            'tanggal' => $shift->started_at->format('Y-m-d'),
            'jam_mulai' => $shift->started_at->format('H:i'),
            'jam_selesai' => $shift->ended_at?->format('H:i') ?? '—',
            'jarak_km' => $distanceKm,
            'gross_revenue' => $grossRevenue,
            'operational_cost' => $operationalCost,
            'net_profit' => $netProfit,
            'cost_per_km' => $costPerKm,
            'fuel_liters' => $fuelLiters,
            'fuel_efficiency_km_l' => $fuelEfficiency,
            'jam_kerja' => $hoursWorked,
            'target_income' => (int) $shift->target_income,
            'target_achievement_pct' => $targetPct,
        ];
    }
}
