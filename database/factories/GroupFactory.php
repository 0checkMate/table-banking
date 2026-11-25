<?php

namespace Database\Factories;

use App\Enums\InterestMethod;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Chama',
            'currency' => 'KES',
            'interest_method' => InterestMethod::Flat,
            'interest_rate_bps' => 1000,
            'late_penalty_bps' => 0,
            'loan_limit_multiplier' => 3,
        ];
    }
}
