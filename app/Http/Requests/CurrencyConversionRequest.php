<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\CurrencyLayerService;
use Illuminate\Validation\Rule;

class CurrencyConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(CurrencyLayerService $currencyService): array
    {
        $supportedCurrencies = array_keys($currencyService->getSupportedCurrencies());

        return [
            'currencies' => ['required', 'array', 'min:2', 'max:5'],
            'currencies.*' => ['required', 'string', Rule::in($supportedCurrencies)],
        ];
    }
}
