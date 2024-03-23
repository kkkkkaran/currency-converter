<?php

namespace Database\Factories;

use App\Enums\IntervalEnum;
use App\Enums\StatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReportRequest>
 */
class ReportRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'currency' => 'USD',
            'start_date' => $this->faker->date,
            'end_date' => $this->faker->date,
            'interval' => $this->faker->randomElement(IntervalEnum::cases()),
            'status' => StatusEnum::Pending,
            'user_id' => User::factory(),
        ];
    }
}
