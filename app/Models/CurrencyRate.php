<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
