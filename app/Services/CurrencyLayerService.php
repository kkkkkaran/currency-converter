<?php

namespace App\Services;

use App\Clients\CurrencyLayerClient;
use App\Enums\IntervalEnum;
use App\Models\CurrencyRate;
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
            return $this->client->fetchLiveRates($currencies, $source);
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
    public function getHistoricalRateForTimeFrame(
        Carbon $startDate,
        Carbon $endDate,
        string $currency,
        IntervalEnum $interval = IntervalEnum::Daily,
        string $sourceCurrency = 'USD'
    ): array
    {
        $results = collect();

        $availableRates = CurrencyRate::query()
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->where('source_currency', $sourceCurrency)
            ->where('currency', $currency)
            ->get();

        for ($date = $startDate->copy(); $date->lte($endDate); $this->incrementDateForInterval($date, $interval)) {
            $results->put($date->toDateString(), $availableRates->firstWhere('date', $date)?->rate);
        }

        $missingDates = $results->filter(fn ($rate, $date) => $rate === null);

        if ($missingDates->isEmpty()) {
            return $results->toArray();
        }

        $apiResults = $this->fetchAndStoreHistoricalRates($startDate, $endDate, $currency, $sourceCurrency);

        foreach ($missingDates as $date => $rate) {
            if (array_key_exists($date, $apiResults)){
                $results->put($date, $apiResults[$date]);
            }
        }

        return $results->toArray();
    }

    public function getSupportedCurrencies()
    {
        return Cache::remember('supported_currencies', 3600, function () {
            return $this->client->fetchSupportedCurrencies();
        });
    }

    /**
     * @throws RequestException
     */
    private function fetchAndStoreHistoricalRates(
        Carbon $startDate,
        Carbon $endDate,
        string $currency,
        string $sourceCurrency = 'USD'
    ): array
    {
        $response = $this->client->fetchHistoricalRatesForTimeFrame($startDate, $endDate, [], $sourceCurrency);

        $results = [];

        foreach ($response as $date => $rates) {
            foreach ($rates as $currencyPair => $rate) {
                $responseCurrency = substr($currencyPair, -3);

                CurrencyRate::query()->updateOrCreate([
                    'date' => $date,
                    'source_currency' => substr($currencyPair, 0, 3),
                    'currency' => $responseCurrency,
                ], [
                    'rate' => $rate
                ]);

                if ($currency === $responseCurrency) {
                    $results[$date] = $rate;
                }
            }
        }

        return $results;
    }

    private function incrementDateForInterval(Carbon $date, IntervalEnum $interval): Carbon
    {
        if ($interval === IntervalEnum::Daily) {
            return $date->addDay();
        }

        if ($interval === IntervalEnum::Weekly) {
            return $date->addWeek();
        }

        return $date->addMonth();
    }
}
