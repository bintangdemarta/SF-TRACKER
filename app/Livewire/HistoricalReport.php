<?php

namespace App\Livewire;

use App\Services\HistoricalReportService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithPagination;

class HistoricalReport extends Component
{
    use WithPagination;

    public string $period = 'weekly';

    public string $customFrom = '';

    public string $customTo = '';

    public ?string $rangeError = null;

    public function updatingPeriod(): void
    {
        $this->resetPage();
    }

    public function updatingCustomFrom(): void
    {
        $this->resetPage();
    }

    public function updatingCustomTo(): void
    {
        $this->resetPage();
    }

    /**
     * @return array{from: CarbonImmutable, to: CarbonImmutable}|null
     */
    public function getRangeProperty(): ?array
    {
        $this->rangeError = null;

        try {
            return app(HistoricalReportService::class)->resolvePeriod(
                $this->period,
                $this->customFrom ?: null,
                $this->customTo ?: null,
            );
        } catch (InvalidArgumentException $e) {
            $this->rangeError = $e->getMessage();

            return null;
        }
    }

    public function getSummaryProperty(): ?array
    {
        $range = $this->range;

        return $range ? app(HistoricalReportService::class)->summary(Auth::user(), $range['from'], $range['to']) : null;
    }

    public function getShiftsProperty(): ?LengthAwarePaginator
    {
        $range = $this->range;

        return $range
            ? app(HistoricalReportService::class)->shiftsForPeriod(Auth::user(), $range['from'], $range['to'])
            : null;
    }

    public function exportUrl(string $format): ?string
    {
        if (! $this->range) {
            return null;
        }

        return route('reports.export', array_filter([
            'period' => $this->period,
            'from' => $this->period === 'custom' ? $this->customFrom : null,
            'to' => $this->period === 'custom' ? $this->customTo : null,
            'format' => $format,
        ]));
    }

    public function render()
    {
        return view('livewire.historical-report');
    }
}
