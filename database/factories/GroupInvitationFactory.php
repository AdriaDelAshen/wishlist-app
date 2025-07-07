<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupInvitation>
 */
class GroupInvitationFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['email', 'link']);
        $email = null;
        if($type === 'link') {
            $expiresAt = now()->addMinutes(10);
        } else {
            $expiresAt = now()->addDay();
            $email = fake()->email;
        }

        return [
            'email' => $email,
            'type' => fake()->randomElement(['email', 'link']),
            'token' => Str::uuid()->toString(),
            'accepted_at' => fake()->randomElement([null, now()->addMinutes(3)]),
            'expires_at' => $expiresAt,
            'group_id' => Group::factory(),
        ];
    }
}
