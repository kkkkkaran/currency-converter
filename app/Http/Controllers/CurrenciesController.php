<?php

namespace App\Http\Controllers;

use App\Http\Requests\CurrencyConversionRequest;
use App\Services\CurrencyLayerService;
use Illuminate\Http\JsonResponse;

class CurrenciesController extends Controller
{
    public function list(CurrencyLayerService $currencyLayerService): JsonResponse
    {
        return response()->json($currencyLayerService->getSupportedCurrencies());
    }

    public function convert(CurrencyConversionRequest $request, CurrencyLayerService $currencyLayerService): JsonResponse
    {
        $selectedCurrencies = $request->input('currencies');
        $rates = $currencyLayerService->getLiveRates($selectedCurrencies);

        $matrix = [];

        foreach ($selectedCurrencies as $fromCurrency) {
            foreach ($selectedCurrencies as $toCurrency) {
                if ($fromCurrency === $toCurrency) {
                    $matrix[$fromCurrency][$toCurrency] = 1;
                } else {
                    $matrix[$fromCurrency][$toCurrency] = round($rates[$fromCurrency] / $rates[$toCurrency], 2);
                }
            }
        }

        return response()->json([
            'currencies' => $selectedCurrencies,
            'matrix' => $matrix,
        ]);
    }
}
