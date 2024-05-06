<?php

namespace Tests\Feature;

use App\Clients\CurrencyLayerClient;
use App\Enums\IntervalEnum;
use App\Models\CurrencyRate;
use App\Services\CurrencyLayerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CurrencyLayerServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    /** @test */
    public function it_fetches_and_caches_live_rates_successfully()
    {
        $fakeApiResponse = [
            'USDGBP' => 0.75,
            'USDEUR' => 0.85,
        ];

        $this->mock(CurrencyLayerClient::class)
            ->expects('fetchLiveRates')
            ->andReturn($fakeApiResponse);

        $service = resolve(CurrencyLayerService::class);
        $rates = $service->getLiveRates(['USD', 'GBP', 'EUR']);

        $this->assertEquals(['USD' => 1, 'GBP' => 0.75, 'EUR' => 0.85], $rates);
        $this->assertEquals(Cache::get("live_rates_USD"), $fakeApiResponse);
    }

    /** @test */
    public function it_fetches_live_rates_from_cache()
    {
        $this->mock(CurrencyLayerClient::class)
            ->shouldNotHaveBeenCalled();

        Cache::put("live_rates_USD", ['GBP' => 0.75, 'EUR' => 0.85]);

        $service = resolve(CurrencyLayerService::class);
        $rates = $service->getLiveRates(['GBP', 'EUR']);

        $this->assertEquals(['GBP' => 0.75, 'EUR' => 0.85], $rates);
    }

    /** @test */
    public function supported_currencies_are_fetched_and_cached_successfully()
    {
        $this->mock(CurrencyLayerClient::class)
            ->expects('fetchSupportedCurrencies')
            ->andReturn(['USD' => 'United States Dollar', 'GBP' => 'British Pound Sterling']);

        $service = resolve(CurrencyLayerService::class);
        $currencies = $service->getSupportedCurrencies();

        $this->assertEquals(['USD' => 'United States Dollar', 'GBP' => 'British Pound Sterling'], $currencies);
    }

    /** @test */
    public function supported_currencies_are_retrieved_from_cache_on_subsequent_calls()
    {
        $this->mock(CurrencyLayerClient::class)->shouldNotHaveBeenCalled();

        Cache::put('supported_currencies', ['USD' => 'United States Dollar', 'GBP' => 'British Pound Sterling']);

        $service = resolve(CurrencyLayerService::class);

        $this->assertEquals(['USD' => 'United States Dollar', 'GBP' => 'British Pound Sterling'], $service->getSupportedCurrencies());
    }

    /** @test */
    public function it_returns_historical_rates_fully_from_database()
    {
        $currency = 'GBP';
        $sourceCurrency = 'USD';
        $startDate = Carbon::parse('2021-01-01');
        $endDate = Carbon::parse('2021-01-05');

        for($date = $startDate->clone(); $date->lte($endDate); $date->addDay()) {
            CurrencyRate::factory()->create([
                'date' => $date,
                'currency' => $currency,
                'source_currency' => $sourceCurrency,
                'rate' => 0.75,
            ]);
        }

        $this->mock(CurrencyLayerClient::class)->shouldNotHaveBeenCalled();

        $service = resolve(CurrencyLayerService::class);

        $rates = $service->getHistoricalRateForTimeFrame($startDate->toImmutable(), $endDate->toImmutable(), $currency, IntervalEnum::Daily, $sourceCurrency);

        $this->assertNotEmpty($rates);
        $this->assertEquals(0.75, $rates['2021-01-01']);
    }

    /** @test */
    public function it_fetches_historical_rates_partially_from_database_and_api()
    {
        $currency = 'GBP';
        $sourceCurrency = 'USD';
        $startDate = Carbon::parse('2021-01-01');
        $endDate = Carbon::parse('2021-01-03');

        CurrencyRate::factory()->create([
            'date' => '2021-01-01',
            'currency' => $currency,
            'source_currency' => $sourceCurrency,
            'rate' => 0.75,
        ]);

        $this->mock(CurrencyLayerClient::class)
            ->expects('fetchHistoricalRatesForTimeFrame')
            ->andReturn([
                "2021-01-01" => [
                    "USDUSD" => 1,
                    "USDGBP" => 0.7405,
                    "USDEUR" => 0.815,
                ],
                "2021-01-02" => [
                    "USDUSD" => 1,
                    "USDGBP" => 0.7398,
                    "USDEUR" => 0.8165,
                ],
                "2021-01-03" => [
                    "USDUSD" => 1,
                    "USDGBP" => 0.7381,
                    "USDEUR" => 0.818,
                ],
            ]);

        $service = resolve(CurrencyLayerService::class);

        $rates = $service->getHistoricalRateForTimeFrame($startDate->toImmutable(), $endDate->toImmutable(), $currency, IntervalEnum::Daily, $sourceCurrency);

        $expectedRates = [
            "2021-01-01" => 0.75,
            "2021-01-02" => 0.7398, // From API
            "2021-01-03" => 0.7381, // From API
        ];

        $this->assertEquals($expectedRates, $rates);

        $this->assertDatabaseHas(CurrencyRate::class, [
            'date' => '2021-01-01 00:00:00',
            'currency' => 'EUR',
            'source_currency' => 'USD',
            'rate' => 0.815,
        ]);
    }
}
