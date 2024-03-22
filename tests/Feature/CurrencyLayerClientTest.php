<?php

namespace Tests\Feature;

use App\Clients\CurrencyLayerClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CurrencyLayerClientTest extends TestCase
{
    /** @test */
    public function it_fetches_live_rates_successfully()
    {
        Http::fake([
            '*/live*' => Http::response([
                'success' => true,
                'terms' => 'https://currencylayer.com/terms',
                'privacy' => 'https://currencylayer.com/privacy',
                'quotes' => [
                    'USDGBP' => 0.75,
                    'USDEUR' => 0.85,
                ],
            ]),
        ]);

        $client = new CurrencyLayerClient();
        $response = $client->fetchLiveRates(['GBP', 'EUR']);

        $this->assertEquals(0.75, $response['USDGBP']);
        $this->assertEquals(0.85, $response['USDEUR']);
    }

    /** @test */
    public function it_throws_an_exception_when_fetching_live_rates_fails()
    {
        Http::fake(['*/live*' => Http::response([], 500)]);

        $client = new CurrencyLayerClient();

        $this->expectException(RequestException::class);

        $client->fetchLiveRates(['GBP', 'EUR']);
    }

    /** @test */
    public function it_fetches_historical_rates_successfully()
    {
        Http::fake([
            '*/timeframe*' => Http::response([
                'success' => true,
                'terms' => 'https://currencylayer.com/terms',
                'privacy' => 'https://currencylayer.com/privacy',
                'quotes' => [
                    '2023-03-01' => [
                        'USDGBP' => 0.75,
                        'USDEUR' => 0.85,
                    ],
                    '2023-03-02' => [
                        'USDGBP' => 0.76,
                        'USDEUR' => 0.86,
                    ],
                ],
            ]),
        ]);

        $client = new CurrencyLayerClient();
        $startDate = Carbon::createFromDate(2023, 3, 1);
        $endDate = Carbon::createFromDate(2023, 3, 2);

        $response = $client->fetchHistoricalRatesForTimeFrame($startDate, $endDate, ['GBP', 'EUR']);

        $this->assertTrue($response['2023-03-01']['USDGBP'] === 0.75);
        $this->assertTrue($response['2023-03-02']['USDEUR'] === 0.86);
    }

    /** @test */
    public function it_throws_an_exception_when_fetching_historical_rates_fails()
    {
        Http::fake(['*/timeframe*' => Http::response([], 404)]);

        $client = new CurrencyLayerClient();
        $startDate = Carbon::now()->subMonth();
        $endDate = Carbon::now();

        $this->expectException(RequestException::class);

        $client->fetchHistoricalRatesForTimeFrame($startDate, $endDate, ['GBP'], 'USD');
    }

    /** @test */
    public function it_fetches_supported_currencies_successfully()
    {
        Http::fake([
            '*/list*' => Http::response([
                'success' => true,
                'currencies' => [
                    'USD' => 'United States Dollar',
                    'GBP' => 'British Pound',
                    'EUR' => 'Euro'
                ],
            ]),
        ]);

        $client = new CurrencyLayerClient();
        $response = $client->fetchSupportedCurrencies();

        $this->assertArrayHasKey('USD', $response);
        $this->assertEquals('United States Dollar', $response['USD']);
    }

    /** @test */
    public function it_throws_an_exception_when_fetching_supported_currencies_fails()
    {
        Http::fake(['*/list*' => Http::response([], 403)]);

        $client = new CurrencyLayerClient();

        $this->expectException(RequestException::class);

        $client->fetchSupportedCurrencies();
    }
}
