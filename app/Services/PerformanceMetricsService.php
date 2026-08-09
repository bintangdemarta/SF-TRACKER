<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ShiftSession;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Vehicle/operational efficiency metrics: Cost per KM, Fuel Efficiency
 * (KM/L), Target Achievement Rate. Composes FinancialMetricsService for
 * the ledger figures it reuses (gross revenue, net profit, hours worked)
 * instead of recomputing them — single source of truth per figure.
 *
 * Threshold constants below are configured defaults for the badge
 * indicators, not a cited industry benchmark — tune them per fleet/region
 * if the app is used outside the ShopeeFood-motorcycle context they were
 * picked for.
 */
class PerformanceMetricsService
{
    /** Categories counted as "direct operational cost" for Cost/KM: fuel, upkeep, and micro costs like parking. */
    protected const DIRECT_COST_CATEGORY_TYPES = ['bbm', 'pemeliharaan', 'mikro'];

    protected const COST_PER_KM_GOOD_MAX = 500;   // Rp/km at or below this = efficient

    protected const COST_PER_KM_OK_MAX = 1000;    // Rp/km at or below this = acceptable; above = bad

    protected const FUEL_EFFICIENCY_GOOD_MIN = 40.0; // km/L at or above this = efficient

    protected const FUEL_EFFICIENCY_OK_MIN = 25.0;   // km/L at or above this = acceptable; below = bad

    protected const TARGET_GOOD_MIN = 100.0; // % at or above this = target met

    protected const TARGET_OK_MIN = 70.0;    // % at or above this = close; below = behind

    public function __construct(protected FinancialMetricsService $financials) {}

    public function directOperationalCost(ShiftSession $shift): int
    {
        return (int) $shift->expenses()
            ->whereHas('category', fn ($q) => $q->whereIn('type', self::DIRECT_COST_CATEGORY_TYPES))
            ->sum('amount');
    }

    public function costPerKm(ShiftSession $shift): ?float
    {
        $distance = $shift->totalDistanceKm();

        if ($distance <= 0) {
            return null;
        }

        return round($this->directOperationalCost($shift) / $distance, 2);
    }

    public function fuelLiters(ShiftSession $shift): float
    {
        return (float) $shift->expenses()
            ->whereHas('category', fn ($q) => $q->where('type', 'bbm'))
            ->sum('fuel_liters');
    }

    public function fuelEfficiency(ShiftSession $shift): ?float
    {
        $liters = $this->fuelLiters($shift);
        $distance = $shift->totalDistanceKm();

        if ($liters <= 0 || $distance <= 0) {
            return null;
        }

        return round($distance / $liters, 2);
    }

    public function targetAchievementRate(ShiftSession $shift): ?float
    {
        if ($shift->target_income <= 0) {
            return null;
        }

        return round($this->financials->grossRevenue($shift) / $shift->target_income * 100, 1);
    }

    /**
     * Lower-is-better threshold (Cost/KM): null when no data, 'good' when
     * cheap, degrading to 'bad' as cost climbs.
     */
    public function costPerKmLevel(?float $costPerKm): string
    {
        if ($costPerKm === null) {
            return 'unknown';
        }

        return match (true) {
            $costPerKm <= self::COST_PER_KM_GOOD_MAX => 'good',
            $costPerKm <= self::COST_PER_KM_OK_MAX => 'ok',
            default => 'bad',
        };
    }

    /**
     * Higher-is-better threshold (KM/L).
     */
    public function fuelEfficiencyLevel(?float $kmPerLiter): string
    {
        if ($kmPerLiter === null) {
            return 'unknown';
        }

        return match (true) {
            $kmPerLiter >= self::FUEL_EFFICIENCY_GOOD_MIN => 'good',
            $kmPerLiter >= self::FUEL_EFFICIENCY_OK_MIN => 'ok',
            default => 'bad',
        };
    }

    /**
     * Higher-is-better threshold (% of target income reached).
     */
    public function targetAchievementLevel(?float $pct): string
    {
        if ($pct === null) {
            return 'unknown';
        }

        return match (true) {
            $pct >= self::TARGET_GOOD_MIN => 'good',
            $pct >= self::TARGET_OK_MIN => 'ok',
            default => 'bad',
        };
    }

    /**
     * @return array{
     *     gross_revenue:int,operational_cost:int,net_profit:int,
     *     distance_km:float,hours_worked:float,hourly_rate:?float,
     *     direct_operational_cost:int,cost_per_km:?float,cost_per_km_level:string,
     *     fuel_liters:float,fuel_efficiency_km_l:?float,fuel_efficiency_level:string,
     *     target_achievement_pct:?float,target_achievement_level:string
     * }
     */
    public function summarize(ShiftSession $shift): array
    {
        $costPerKm = $this->costPerKm($shift);
        $fuelEfficiency = $this->fuelEfficiency($shift);
        $targetPct = $this->targetAchievementRate($shift);

        return [
            ...$this->financials->summarize($shift),
            'direct_operational_cost' => $this->directOperationalCost($shift),
            'cost_per_km' => $costPerKm,
            'cost_per_km_level' => $this->costPerKmLevel($costPerKm),
            'fuel_liters' => $this->fuelLiters($shift),
            'fuel_efficiency_km_l' => $fuelEfficiency,
            'fuel_efficiency_level' => $this->fuelEfficiencyLevel($fuelEfficiency),
            'target_achievement_pct' => $targetPct,
            'target_achievement_level' => $this->targetAchievementLevel($targetPct),
        ];
    }

    /**
     * @return array{
     *     shift_count:int,gross_revenue:int,operational_cost:int,net_profit:int,
     *     distance_km:float,hours_worked:float,hourly_rate:?float,
     *     direct_operational_cost:int,cost_per_km:?float,cost_per_km_level:string,
     *     fuel_liters:float,fuel_efficiency_km_l:?float,fuel_efficiency_level:string,
     *     target_achievement_pct:?float,target_achievement_level:string
     * }
     */
    public function summarizeForPeriod(User $user, CarbonInterface $from, CarbonInterface $to): array
    {
        $base = $this->financials->summarizeForPeriod($user, $from, $to);

        $directOperationalCost = (int) Expense::where('user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->whereHas('category', fn ($q) => $q->whereIn('type', self::DIRECT_COST_CATEGORY_TYPES))
            ->sum('amount');

        $fuelLiters = (float) Expense::where('user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->whereHas('category', fn ($q) => $q->where('type', 'bbm'))
            ->sum('fuel_liters');

        $distanceKm = $base['distance_km'];
        $costPerKm = $distanceKm > 0 ? round($directOperationalCost / $distanceKm, 2) : null;
        $fuelEfficiency = ($fuelLiters > 0 && $distanceKm > 0) ? round($distanceKm / $fuelLiters, 2) : null;

        $targetIncomeSum = (int) ShiftSession::where('user_id', $user->id)
            ->whereBetween('started_at', [$from, $to])
            ->sum('target_income');
        $targetPct = $targetIncomeSum > 0 ? round($base['gross_revenue'] / $targetIncomeSum * 100, 1) : null;

        return [
            ...$base,
            'direct_operational_cost' => $directOperationalCost,
            'cost_per_km' => $costPerKm,
            'cost_per_km_level' => $this->costPerKmLevel($costPerKm),
            'fuel_liters' => round($fuelLiters, 2),
            'fuel_efficiency_km_l' => $fuelEfficiency,
            'fuel_efficiency_level' => $this->fuelEfficiencyLevel($fuelEfficiency),
            'target_achievement_pct' => $targetPct,
            'target_achievement_level' => $this->targetAchievementLevel($targetPct),
        ];
    }
}
