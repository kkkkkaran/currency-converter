<?php

namespace App\Jobs;

use App\Enums\StatusEnum;
use App\Models\ReportRequest;
use App\Services\CurrencyLayerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessReportsRequestsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(CurrencyLayerService $service): void
    {
        ReportRequest::query()
            ->where('status', StatusEnum::Pending)
            ->each(function (ReportRequest $request) use ($service) {
                try {
                    $request->status = StatusEnum::Processing;
                    $request->save();

                    $data = $service->getHistoricalRateForTimeFrame(
                        $request->start_date,
                        $request->end_date,
                        $request->currency,
                        $request->interval
                    );

                    $this->writeDataToCsv($data, "report_{$request->id}.csv");

                    $request->status = StatusEnum::Completed;
                    $request->save();
                } catch (\Exception $e) {
                    $request->status = StatusEnum::Failed;
                    $request->save();

                    Log::error("Failed to process report request {$request->id}: {$e->getMessage()}");
                }
            });
    }

    private function writeDataToCsv(array $data, string $fileName): void
    {
        $stream = fopen('php://temp', 'w+b');

        foreach ($data as $date => $rate) {
            fputcsv($stream, [$date, $rate]);
        }

        rewind($stream);

        Storage::disk('public')->writeStream($fileName, $stream);

        fclose($stream);
    }
}
