<?php

namespace App\Http\Resources;

use App\Enums\StatusEnum;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;


class ReportRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'currency' => $this->currency,
            'interval' => $this->interval,
            'status' => $this->status,
            'file_url' => $this->when($this->status === StatusEnum::Completed,
                Storage::disk('public')->url("report_{$this->id}.csv")),
            'created_at' => $this->created_at,
        ];
    }
}
