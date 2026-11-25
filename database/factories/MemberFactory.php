<?php

namespace Database\Factories;

use App\Enums\MemberStatus;
use App\Models\Group;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'phone' => '+2547'.fake()->unique()->numerify('########'),
            'status' => MemberStatus::Active,
            'joined_on' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
