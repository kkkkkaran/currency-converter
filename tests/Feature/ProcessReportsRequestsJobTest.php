<?php

namespace Tests\Feature;

use App\Enums\IntervalEnum;
use App\Enums\StatusEnum;
use App\Jobs\ProcessReportsRequestsJob;
use App\Models\ReportRequest;
use App\Services\CurrencyLayerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessReportsRequestsJobTest extends TestCase
{
    use DatabaseTransactions;
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_processes_pending_report_requests_and_generates_csv_files()
    {
        $mockData = [
            "2021-01-01" => 0.75,
            "2021-01-02" => 0.7398,
            "2021-01-03" => 0.7381,
        ];

        $this->mock(CurrencyLayerService::class)
            ->expects('getHistoricalRateForTimeFrame')
            ->andReturn($mockData);

        ReportRequest::factory()->create([
            'start_date' => '2021-01-01',
            'end_date' => '2021-01-03',
            'interval' => IntervalEnum::Daily,
        ]);

        ProcessReportsRequestsJob::dispatchSync();

        $this->assertDatabaseHas(ReportRequest::class, [
            'status' => StatusEnum::Completed,
        ]);

        Storage::disk('public')->assertExists('report_1.csv');

        $contents = Storage::disk('public')->get('report_1.csv');

        $this->assertStringContainsString("2021-01-01,0.75", $contents);
        $this->assertStringContainsString("2021-01-02,0.7398", $contents);
        $this->assertStringContainsString("2021-01-03,0.7381", $contents);
    }

    /** @test */
    public function it_marks_report_request_as_failed_and_logs_error_on_exception()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'Failed to process report request');
            });

        $this->mock(CurrencyLayerService::class, function ($mock) {
            $mock->shouldReceive('getHistoricalRateForTimeFrame')
                ->andThrow(new \Exception('Test exception'));
        });

        $reportRequest = ReportRequest::factory()->create([
            'start_date' => '2021-01-01',
            'end_date' => '2021-01-03',
            'interval' => IntervalEnum::Daily,
            'status' => StatusEnum::Pending,
        ]);

        ProcessReportsRequestsJob::dispatchSync();

        $this->assertDatabaseHas(ReportRequest::class, [
            'id' => $reportRequest->id,
            'status' => StatusEnum::Failed,
        ]);
    }
}
