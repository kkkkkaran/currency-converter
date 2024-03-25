<?php

namespace Tests\Feature;

use App\Models\ReportRequest;
use App\Models\User;
use App\Services\CurrencyLayerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CurrencyReportControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function authenticated_users_can_retrieve_their_report_requests()
    {
        $user = User::factory()->create();
        ReportRequest::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/currencies/reports');

        $response->assertOk();
        $response->assertJsonCount(5, 'data');
    }

    /** @test */
    public function authenticated_users_can_submit_new_report_requests()
    {
        $this->mock(CurrencyLayerService::class)
            ->expects('getSupportedCurrencies')
            ->andReturn(['USD' => 'USD']);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $reportRequestData = [
            'start_date' => '2021-01-01',
            'end_date' => '2021-01-31',
            'currency' => 'USD',
            'interval' => 'Daily',
        ];

        $response = $this->postJson('/api/currencies/reports', $reportRequestData);
        $response->assertStatus(201);

        $this->assertDatabaseHas(ReportRequest::class, [
            "start_date" => "2021-01-01 00:00:00",
            "end_date" => "2021-01-31 00:00:00",
            "currency" => "USD",
            "interval" => "Daily",
            "user_id" => $user->id,
        ]);
    }

    /** @test */
    public function end_date_must_be_after_or_equal_to_start_date()
    {
        $this->mock(CurrencyLayerService::class)
            ->expects('getSupportedCurrencies')
            ->andReturn(['USD' => 'USD']);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/currencies/reports', [
            'currency' => 'USD',
            'start_date' => '2023-01-31',
            'end_date' => '2023-01-01', // Invalid: before start_date
            'interval' => 'daily',
        ]);

        $response->assertJsonValidationErrors('end_date');
    }

    /** @test */
    public function interval_must_be_one_of_the_specified_values()
    {
        $this->mock(CurrencyLayerService::class)
            ->expects('getSupportedCurrencies')
            ->andReturn(['USD' => 'USD']);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/currencies/reports', [
            'currency' => 'USD',
            'start_date' => '2023-01-01',
            'end_date' => '2023-01-31',
            'interval' => 'yearly', // Invalid: not in the specified set of values
        ]);

        $response->assertJsonValidationErrors('interval');
    }
}
