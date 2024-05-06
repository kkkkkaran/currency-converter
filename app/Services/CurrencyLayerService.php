<?php

namespace App\Services;

use App\Clients\CurrencyLayerClient;
use App\Enums\IntervalEnum;
use App\Models\CurrencyRate;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\RequestException;
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
            return $this->client->fetchLiveRates([], $source);
        });

        return collect($cachedResponse)
            ->mapWithKeys(fn (float $value, string $key) => [str_replace($source, '', $key) => $value])
            ->put($source, 1)
            ->when(!empty($currencies), function (Collection $collection) use ($currencies) {
                return $collection->filter(fn (float $value, string $key) => in_array($key, $currencies));
            })->toArray();
    }

    /**
     * @throws RequestException
     */
    public function getHistoricalRateForTimeFrame(
        CarbonImmutable $startDate,
        CarbonImmutable $endDate,
        string $currency,
        IntervalEnum $interval = IntervalEnum::Daily,
        string $sourceCurrency = 'USD'
    ): array
    {
        $results = collect();

        $availableRates = CurrencyRate::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereSourceCurrency($sourceCurrency)
            ->whereCurrency($currency)
            ->get();

        for ($date = $startDate; $date->lte($endDate); $date = $this->incrementDateForInterval($date, $interval)) {
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

    public function getSupportedCurrencies(): array
    {
        return Cache::remember('supported_currencies', 3600, function () {
            return $this->client->fetchSupportedCurrencies();
        });
    }

    /**
     * @throws RequestException
     */
    private function fetchAndStoreHistoricalRates(
        CarbonImmutable $startDate,
        CarbonImmutable $endDate,
        string $currency,
        string $sourceCurrency = 'USD'
    ): array
    {
        $response = $this->client->fetchHistoricalRatesForTimeFrame($startDate, $endDate, [], $sourceCurrency);

        $results = [];

        foreach ($response as $date => $rates) {
            foreach ($rates as $currencyPair => $rate) {
                $responseCurrency = substr($currencyPair, -3);

                CurrencyRate::query()->firstOrCreate([
                    'date' => Carbon::parse($date),
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

    private function incrementDateForInterval(CarbonImmutable $date, IntervalEnum $interval): CarbonImmutable
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
