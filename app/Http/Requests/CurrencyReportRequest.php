<?php

namespace App\Http\Requests;

use App\Enums\IntervalEnum;
use App\Services\CurrencyLayerService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class CurrencyReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $supportedCurrencies = array_keys(resolve(CurrencyLayerService::class)->getSupportedCurrencies());

        $basicRules =  [
            'currency' => ['required', 'string', Rule::in($supportedCurrencies)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'before_or_equal:today'],
            'interval' => ['required', 'string', Rule::in(IntervalEnum::cases())],
        ];

        if ($this->has('start_date') && $startDate = Carbon::parse($this->start_date)) {
            $oneYearLater = $startDate->copy()->addYear()->format('Y-m-d');
            $basicRules['end_date'] = ['after:'.$startDate, 'before_or_equal:'.$oneYearLater];
        }

        return $basicRules;
    }
}
