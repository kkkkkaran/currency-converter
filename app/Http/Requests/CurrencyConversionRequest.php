<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\CurrencyLayerService;
use Illuminate\Validation\Validator;

class CurrencyConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currencies' => ['required', 'array', 'min:2', 'max:5'],
            'currencies.*' => ['required', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $currencyLayerService = resolve(CurrencyLayerService::class);
            $supportedCurrencies = $currencyLayerService->getSupportedCurrencies();
            $currencies = $this->currencies ?? [];

            foreach ($currencies as $currency) {
                if (!array_key_exists($currency, $supportedCurrencies)) {
                    $validator->errors()->add('currencies', $currency . ' is not a supported currency.');
                }
            }
        });
    }
}
