<?php

namespace Tests\Feature;

use App\Models\ExpenseCategory;
use App\Models\ShiftSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Rap2hpoutre\FastExcel\FastExcel;
use Tests\TestCase;

class HistoricalReportExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function makeShiftWithTrip(User $user, int $fareAmount = 50000): ShiftSession
    {
        $shift = ShiftSession::create([
            'user_id' => $user->id,
            'start_odometer' => 1000,
            'end_odometer' => 1040,
            'target_income' => 80000,
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
            'status' => 'closed',
        ]);
        $shift->tripLogs()->create(['fare_amount' => $fareAmount, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);

        return $shift;
    }

    public function test_export_requires_authentication(): void
    {
        $response = $this->get(route('reports.export', ['period' => 'weekly', 'format' => 'csv']));

        $response->assertRedirect(route('login'));
    }

    public function test_export_rejects_invalid_period(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.export', ['period' => 'yearly', 'format' => 'csv']));

        $response->assertSessionHasErrors('period');
    }

    public function test_export_rejects_invalid_format(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.export', ['period' => 'weekly', 'format' => 'pdf']));

        $response->assertSessionHasErrors('format');
    }

    public function test_export_rejects_custom_period_without_dates(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.export', ['period' => 'custom', 'format' => 'csv']));

        $response->assertSessionHasErrors(['from', 'to']);
    }

    public function test_export_rejects_custom_period_with_to_before_from(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.export', [
            'period' => 'custom', 'format' => 'csv', 'from' => '2026-02-01', 'to' => '2026-01-01',
        ]));

        $response->assertSessionHasErrors('to');
    }

    public function test_csv_export_streams_shift_data_for_the_period(): void
    {
        $user = User::factory()->create();
        $this->makeShiftWithTrip($user, 55000);

        $response = $this->actingAs($user)
            ->get(route('reports.export', ['period' => 'weekly', 'format' => 'csv']));

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Tanggal', $content);
        $this->assertStringContainsString('Net Profit', $content);
        $this->assertStringContainsString('55000', $content);
    }

    public function test_csv_export_excludes_other_users_shifts(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->makeShiftWithTrip($user, 10000);
        $this->makeShiftWithTrip($other, 999999);

        $response = $this->actingAs($user)
            ->get(route('reports.export', ['period' => 'weekly', 'format' => 'csv']));

        $content = $response->streamedContent();
        $this->assertStringContainsString('10000', $content);
        $this->assertStringNotContainsString('999999', $content);
    }

    public function test_xlsx_export_produces_readable_workbook_with_correct_data(): void
    {
        $user = User::factory()->create();
        $shift = $this->makeShiftWithTrip($user, 42000);

        $response = $this->actingAs($user)
            ->get(route('reports.export', ['period' => 'weekly', 'format' => 'xlsx']));

        $response->assertOk();

        $tempPath = tempnam(sys_get_temp_dir(), 'sf-tracker-export-test').'.xlsx';
        file_put_contents($tempPath, $response->streamedContent());

        $rows = (new FastExcel)->import($tempPath);

        unlink($tempPath);

        $this->assertCount(1, $rows);
        $this->assertSame(42000, (int) $rows->first()['Gross Revenue']);
        $this->assertSame($shift->started_at->format('Y-m-d'), $rows->first()['Tanggal']);
    }

    public function test_custom_period_export_only_includes_shifts_in_range(): void
    {
        $user = User::factory()->create();
        ExpenseCategory::create(['name' => 'BBM', 'type' => 'bbm']);

        $inRange = ShiftSession::create([
            'user_id' => $user->id, 'start_odometer' => 1000, 'end_odometer' => 1010,
            'target_income' => 50000, 'started_at' => '2026-03-15 08:00:00', 'ended_at' => '2026-03-15 09:00:00',
            'status' => 'closed',
        ]);
        $inRange->tripLogs()->create(['fare_amount' => 21000, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);

        $outOfRange = ShiftSession::create([
            'user_id' => $user->id, 'start_odometer' => 1010, 'end_odometer' => 1020,
            'target_income' => 50000, 'started_at' => '2026-04-01 08:00:00', 'ended_at' => '2026-04-01 09:00:00',
            'status' => 'closed',
        ]);
        $outOfRange->tripLogs()->create(['fare_amount' => 87654, 'tip_cash' => 0, 'tip_app' => 0, 'points_earned' => 0]);

        $response = $this->actingAs($user)->get(route('reports.export', [
            'period' => 'custom', 'format' => 'csv', 'from' => '2026-03-01', 'to' => '2026-03-31',
        ]));

        $content = $response->streamedContent();
        $this->assertStringContainsString('21000', $content);
        $this->assertStringNotContainsString('87654', $content);
    }
}
