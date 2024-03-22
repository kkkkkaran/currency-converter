<?php

namespace App\Clients;

use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class CurrencyLayerClient
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('currencylayer.api_key');
        $this->baseUrl = config('currencylayer.base_url');

        if (empty($this->apiKey)) {
            throw new InvalidArgumentException("CurrencyLayer API key is not set.");
        }

        if (empty($this->baseUrl)) {
            throw new InvalidArgumentException("CurrencyLayer base URL is not set.");
        }
    }

    /**
     * @throws RequestException
     */
    public function fetchLiveRates(string $source = 'USD', array $currencies = []): array
    {
        $endpoint = 'live';
        $query = [
            'access_key' => $this->apiKey,
            'source' => $source,
        ];

        if (!empty($currencies)) {
            $query['currencies'] = implode(',', $currencies);
        }

        $response = Http::get($this->baseUrl . $endpoint, $query);
        $response->throwIf($response->failed());

        return $response->json();
    }

    /**
     * @throws RequestException
     */
    public function fetchHistoricalRatesForTimeFrame(Carbon $startDate, Carbon $endDate, array $currencies = []): array
    {
        $endpoint = 'timeframe';
        $query = [
            'access_key' => $this->apiKey,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];

        if (!empty($currencies)) {
            $query['currencies'] = implode(',', $currencies);
        }

        $response = Http::get($this->baseUrl . $endpoint, $query);

        $response->throwIf($response->failed());

        return $response->json();
    }

    /**
     * @throws RequestException
     */
    public function fetchSupportedCurrencies(): array
    {
        $endpoint = 'list';
        $response = Http::get($this->baseUrl . $endpoint, ['access_key' => $this->apiKey]);
        $response->throwIf($response->failed());

        return $response->json()['currencies'];
    }
}
