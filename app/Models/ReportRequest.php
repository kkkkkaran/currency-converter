<?php

namespace App\Models;

use App\Enums\IntervalEnum;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportRequest extends Model
{
    use HasFactory;

    protected $fillable = ['currency', 'start_date', 'end_date', 'interval', 'status', 'user_id'];

    protected function casts(): array
    {
        return [
            'interval' => IntervalEnum::class,
            'status' => StatusEnum::class,
            'start_date' => 'immutable_date',
            'end_date' => 'immutable_date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
