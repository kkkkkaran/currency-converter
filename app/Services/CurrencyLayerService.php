<?php

namespace App\Services;

use App\Clients\CurrencyLayerClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CurrencyLayerService
{
    private CurrencyLayerClient $client;

    public function __construct(CurrencyLayerClient $currencyLayerClient)
    {
        $this->client = $currencyLayerClient;
    }

    /**
     * @throws Throwable
     */
    public function getLiveRates(array $currencies = [], string $source = 'USD'): ?array
    {
        $cachedResponse = Cache::remember("live_rates_{$source}", 120, function () use ($currencies, $source) {
            return $this->client->fetchLiveRates($source, $currencies);
        });

        return collect($cachedResponse)
            ->mapWithKeys(fn (float $value, string $key) => [str_replace($source, '', $key) => $value])
            ->when(!empty($currencies), function (Collection $collection) use ($currencies) {
                return $collection->filter(fn (float $value, string $key) => in_array($key, $currencies));
            });
    }

    /**
     * @throws RequestException
     */
    public function getHistoricalRateForTimeFrame(Carbon $startDate, Carbon $endDate, array $currencies = []): ?array
    {
        return $this->client->fetchHistoricalRatesForTimeFrame($startDate, $endDate, $currencies);
    }

    public function getSupportedCurrencies()
    {
        return Cache::remember('supported_currencies', 3600, function () {
            $this->client->fetchSupportedCurrencies();
        });
    }
}
