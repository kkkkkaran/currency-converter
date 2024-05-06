<?php

namespace App\Models;

use App\Enums\IntervalEnum;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property string $currency
 * @property \Carbon\CarbonImmutable $start_date
 * @property \Carbon\CarbonImmutable $end_date
 * @property IntervalEnum $interval
 * @property StatusEnum $status
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\ReportRequestFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|ReportRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportRequest whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportRequest whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportRequest whereInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportRequest whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportRequest whereUserId($value)
 * @mixin \Eloquent
 */
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
