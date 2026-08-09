<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ShiftSession;
use App\Models\TripLog;
use App\Models\User;
use Carbon\CarbonInterface;

class FinancialMetricsService
{
    protected const COST_CATEGORY_TYPES = ['bbm', 'pemeliharaan'];

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

    public function fuelMaintenanceCost(ShiftSession $shift): int
    {
        return (int) $shift->expenses()
            ->whereHas('category', fn ($q) => $q->whereIn('type', self::COST_CATEGORY_TYPES))
            ->sum('amount');
    }

    public function costPerKm(ShiftSession $shift): ?float
    {
        $distance = $shift->totalDistanceKm();

        if ($distance <= 0) {
            return null;
        }

        return round($this->fuelMaintenanceCost($shift) / $distance, 2);
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

        if ($hours <= 0) {
            return null;
        }

        return round($this->netProfit($shift) / $hours, 2);
    }

    /**
     * @return array{gross_revenue:int,operational_cost:int,net_profit:int,fuel_maintenance_cost:int,distance_km:float,cost_per_km:?float,hours_worked:float,hourly_rate:?float}
     */
    public function summarize(ShiftSession $shift): array
    {
        $grossRevenue = $this->grossRevenue($shift);
        $operationalCost = $this->operationalCost($shift);

        return [
            'gross_revenue' => $grossRevenue,
            'operational_cost' => $operationalCost,
            'net_profit' => $grossRevenue - $operationalCost,
            'fuel_maintenance_cost' => $this->fuelMaintenanceCost($shift),
            'distance_km' => $shift->totalDistanceKm(),
            'cost_per_km' => $this->costPerKm($shift),
            'hours_worked' => $this->hoursWorked($shift),
            'hourly_rate' => $this->hourlyRate($shift),
        ];
    }

    /**
     * Aggregate metrics across all of a user's shifts starting within [$from, $to].
     * Expenses are matched by their own created_at so standalone entries
     * (shift_session_id null, e.g. servis dirumah) are still counted for
     * the period they actually happened in.
     *
     * @return array{shift_count:int,gross_revenue:int,operational_cost:int,net_profit:int,fuel_maintenance_cost:int,distance_km:float,cost_per_km:?float,hours_worked:float,hourly_rate:?float}
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

        $fuelMaintenanceCost = (int) Expense::where('user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->whereHas('category', fn ($q) => $q->whereIn('type', self::COST_CATEGORY_TYPES))
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
            'fuel_maintenance_cost' => $fuelMaintenanceCost,
            'distance_km' => $distanceKm,
            'cost_per_km' => $distanceKm > 0 ? round($fuelMaintenanceCost / $distanceKm, 2) : null,
            'hours_worked' => $hoursWorked,
            'hourly_rate' => $hoursWorked > 0 ? round($netProfit / $hoursWorked, 2) : null,
        ];
    }
}
