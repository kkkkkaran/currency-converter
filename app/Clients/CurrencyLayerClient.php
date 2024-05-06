<?php

namespace App\Clients;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CurrencyLayerClient
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('currencylayer.api_key');
        $this->baseUrl = config('currencylayer.base_url');
    }

    /**
     * @throws RequestException
     */
    public function fetchLiveRates(array $currencies = [], string $source = 'USD'): array
    {
        $query = [
            'source' => $source,
        ];

        if (!empty($currencies)) {
            $query['currencies'] = implode(',', $currencies);
        }

        return $this->makeRequest('live', $query)->json('quotes');
    }

    /**
     * @throws RequestException
     */
    public function fetchHistoricalRatesForTimeFrame(
        CarbonImmutable $startDate,
        CarbonImmutable $endDate,
        array           $currencies = [],
        string          $source = 'USD'
    ): array
    {
        $query = [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'source' => $source,
        ];

        if (!empty($currencies)) {
            $query['currencies'] = implode(',', $currencies);
        }

        return $this->makeRequest('timeframe', $query)->json('quotes');
    }

    /**
     * @throws RequestException
     */
    public function fetchSupportedCurrencies(): array
    {
        return $this->makeRequest('list')->json('currencies');
    }

    /**
     * @throws RequestException
     */
    private function makeRequest(string $endpoint, array $query = []): Response
    {
        return Http::get($this->baseUrl . $endpoint, array_merge(['access_key' => $this->apiKey], $query))->throw();
    }
}
