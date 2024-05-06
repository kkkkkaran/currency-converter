<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property \Carbon\CarbonImmutable $date
 * @property string $source_currency
 * @property string $currency
 * @property string $rate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\CurrencyRateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyRate query()
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyRate whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyRate whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyRate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyRate whereSourceCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyRate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CurrencyRate extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'source_currency', 'currency', 'rate'];

    protected function casts(): array
    {
        return [
            'date' => 'immutable_date',
        ];
    }
}
