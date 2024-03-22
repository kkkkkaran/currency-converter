<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CurrencyLayerService;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CurrencyControllerTest extends TestCase
{
    /** @test */
    public function it_lists_supported_currencies()
    {
        Sanctum::actingAs(User::factory()->create());

        $mockService = $this->mock(CurrencyLayerService::class);
        $mockService->expects('getSupportedCurrencies')
            ->andReturn(['USD' => 'United States Dollar', 'EUR' => 'Euro']);

        $response = $this->getJson('/api/currencies');

        $response->assertStatus(200)
            ->assertJson([
                'USD' => 'United States Dollar',
                'EUR' => 'Euro',
            ]);
    }

    /** @test */
    public function it_converts_selected_currencies_into_a_matrix()
    {
        Sanctum::actingAs(User::factory()->create());

        $mockService = $this->mock(CurrencyLayerService::class);
        $mockService->expects('getLiveRates')
            ->with(['USD', 'EUR', 'GBP'])
            ->andReturn(['USD' => 1, 'EUR' => 0.85, 'GBP' => 1.21]);

        $mockService->expects('getSupportedCurrencies')
            ->andReturn(['USD' => 'United States Dollar', 'EUR' => 'Euro', 'GBP' => 'Great British Pound']);

        $response = $this->getJson('/api/currencies/convert?currencies[]=USD&currencies[]=EUR&currencies[]=GBP');

        $expectedMatrix = [
            'USD' => ['USD' => 1, 'EUR' => 1.18, 'GBP' => 0.83],
            'EUR' => ['USD' => 0.85, 'EUR' => 1, 'GBP' => 0.7],
            'GBP' => ['USD' => 1.21, 'EUR' => 1.42, 'GBP' => 1],
        ];

        $response->assertStatus(200)
            ->assertJson([
                'currencies' => ['USD', 'EUR', 'GBP'],
                'matrix' => $expectedMatrix,
            ]);
    }
}
