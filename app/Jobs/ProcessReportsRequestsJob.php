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
                $request->status = StatusEnum::Processing;
                $request->save();
                try {
                    $data = $service->getReportData($request);
                    $this->writeDataToCsv($data, "report_{$request->id}.csv");

                    $request->status = StatusEnum::Completed;
                } catch (\Exception $e) {
                    Log::error("Failed to process report request {$request->id}: {$e->getMessage()}");

                    $request->status = StatusEnum::Failed;
                }
                $request->save();
            });
    }

    private function writeDataToCsv(array $data, string $fileName): void
    {
        $stream = fopen('php://temp', 'w+b');

        fputcsv($stream, ['date', 'rate']);

        foreach ($data as $date => $rate) {
            fputcsv($stream, [$date, $rate]);
        }

        rewind($stream);

        Storage::disk('public')->writeStream($fileName, $stream);

        fclose($stream);
    }
}
