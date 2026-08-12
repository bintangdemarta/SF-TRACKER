<?php

namespace App\Http\Controllers;

use App\Services\HistoricalReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rap2hpoutre\FastExcel\FastExcel;

class HistoricalReportExportController extends Controller
{
    /**
     * Streams the shift history for the requested period as CSV or XLSX.
     * FastExcel/openspout write row-by-row as the underlying LazyCollection
     * is iterated, so this stays memory-safe regardless of history size.
     */
    public function __invoke(Request $request, HistoricalReportService $reports)
    {
        $validated = $request->validate([
            'period' => ['required', 'in:'.implode(',', HistoricalReportService::PERIODS)],
            'from' => ['required_if:period,custom', 'nullable', 'date'],
            'to' => ['required_if:period,custom', 'nullable', 'date', 'after_or_equal:from'],
            'format' => ['required', 'in:csv,xlsx'],
        ]);

        $range = $reports->resolvePeriod(
            $validated['period'],
            $validated['from'] ?? null,
            $validated['to'] ?? null,
        );

        $rows = $reports->exportRows(Auth::user(), $range['from'], $range['to']);

        $filename = sprintf(
            'riwayat-shift_%s_%s-%s.%s',
            $validated['period'],
            $range['from']->format('Ymd'),
            $range['to']->format('Ymd'),
            $validated['format'],
        );

        return (new FastExcel($rows))->download($filename, fn (array $row) => [
            'Tanggal' => $row['tanggal'],
            'Jam Mulai' => $row['jam_mulai'],
            'Jam Selesai' => $row['jam_selesai'],
            'Jarak (KM)' => $row['jarak_km'],
            'Gross Revenue' => $row['gross_revenue'],
            'Pengeluaran' => $row['operational_cost'],
            'Net Profit' => $row['net_profit'],
            'Cost/KM' => $row['cost_per_km'],
            'BBM (Liter)' => $row['fuel_liters'],
            'Efisiensi (KM/L)' => $row['fuel_efficiency_km_l'],
            'Jam Kerja' => $row['jam_kerja'],
            'Target Income' => $row['target_income'],
            'Pencapaian Target (%)' => $row['target_achievement_pct'],
        ]);
    }
}
