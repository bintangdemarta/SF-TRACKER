<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ShiftSession;
use App\Models\TripLog;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Pure financial-ledger arithmetic: money in, money out, profit, time.
 * Vehicle/operational efficiency metrics (Cost per KM, Fuel Efficiency,
 * Target Achievement) live in PerformanceMetricsService, which composes
 * this service for the figures it reuses (gross revenue, net profit,
 * hours worked) rather than duplicating them.
 */
class FinancialMetricsService
{
    /** Below this, dividing by elapsed hours amplifies noise into a misleadingly huge extrapolated rate. */
    protected const MIN_HOURS_FOR_RATE = 1 / 60;

    public function grossRevenue(ShiftSession $shift): int
    {
        return (int) $shift->tripLogs()
            ->selectRaw('COALESCE(SUM(fare_amount + tip_cash + tip_app), 0) as total')
            ->value('total');
    }

    public function operationalCost(ShiftSession $shift): int
    {
        return (int) $shift->expenses()->sum('amount');
    }

    public function netProfit(ShiftSession $shift): int
    {
        return $this->grossRevenue($shift) - $this->operationalCost($shift);
    }

    public function hoursWorked(ShiftSession $shift): float
    {
        $end = $shift->ended_at ?? now();
        $minutes = $shift->started_at->diffInMinutes($end);

        return round($minutes / 60, 2);
    }

    public function hourlyRate(ShiftSession $shift): ?float
    {
        $hours = $this->hoursWorked($shift);

        if ($hours < self::MIN_HOURS_FOR_RATE) {
            return null;
        }

        return round($this->netProfit($shift) / $hours, 2);
    }

    /**
     * @return array{gross_revenue:int,operational_cost:int,net_profit:int,distance_km:float,hours_worked:float,hourly_rate:?float}
     */
    public function summarize(ShiftSession $shift): array
    {
        $grossRevenue = $this->grossRevenue($shift);
        $operationalCost = $this->operationalCost($shift);

        return [
            'gross_revenue' => $grossRevenue,
            'operational_cost' => $operationalCost,
            'net_profit' => $grossRevenue - $operationalCost,
            'distance_km' => $shift->totalDistanceKm(),
            'hours_worked' => $this->hoursWorked($shift),
            'hourly_rate' => $this->hourlyRate($shift),
        ];
    }

    /**
     * Aggregate metrics across all of a user's shifts starting within [$from, $to].
     * Expenses/trips are matched by their own created_at so standalone
     * entries (shift_session_id null, e.g. servis dirumah) are still
     * counted for the period they actually happened in.
     *
     * @return array{shift_count:int,gross_revenue:int,operational_cost:int,net_profit:int,distance_km:float,hours_worked:float,hourly_rate:?float}
     */
    public function summarizeForPeriod(User $user, CarbonInterface $from, CarbonInterface $to): array
    {
        $grossRevenue = (int) TripLog::whereHas(
            'shiftSession',
            fn ($q) => $q->where('user_id', $user->id)
        )
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('COALESCE(SUM(fare_amount + tip_cash + tip_app), 0) as total')
            ->value('total');

        $operationalCost = (int) Expense::where('user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        $shifts = ShiftSession::where('user_id', $user->id)
            ->whereBetween('started_at', [$from, $to])
            ->get(['id', 'start_odometer', 'end_odometer', 'started_at', 'ended_at']);

        $distanceKm = round((float) $shifts->sum(fn (ShiftSession $s) => $s->totalDistanceKm()), 2);
        $hoursWorked = round((float) $shifts->sum(fn (ShiftSession $s) => $this->hoursWorked($s)), 2);
        $netProfit = $grossRevenue - $operationalCost;

        return [
            'shift_count' => $shifts->count(),
            'gross_revenue' => $grossRevenue,
            'operational_cost' => $operationalCost,
            'net_profit' => $netProfit,
            'distance_km' => $distanceKm,
            'hours_worked' => $hoursWorked,
            'hourly_rate' => $hoursWorked >= self::MIN_HOURS_FOR_RATE ? round($netProfit / $hoursWorked, 2) : null,
        ];
    }
}
