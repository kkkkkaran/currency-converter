<?php

namespace Tests\Feature;

use App\Models\ReportRequest;
use App\Models\User;
use App\Services\CurrencyLayerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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

        $reportRequestData = [
            'start_date' => '2021-01-01',
            'end_date' => '2021-01-31',
            'currency' => 'USD',
            'interval' => 'daily',
        ];

        $response = $this->actingAs($user)->postJson('/api/currencies/reports', $reportRequestData);

        $response->assertStatus(201);

        $this->assertDatabaseHas(ReportRequest::class, [
            "start_date" => "2021-01-01 00:00:00",
            "end_date" => "2021-01-31 00:00:00",
            "currency" => "USD",
            "interval" => "daily",
            "user_id" => $user->id,
        ]);
    }
}
