<?php

namespace Database\Factories;

use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WishlistItem>
 */
class WishlistItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'url_link' => fake()->url(),
            'price' => fake()->randomDigit(),
            'priority' => 0,
            'is_bought' => false,
            'wishlist_id' => Wishlist::factory(),
            'user_id' => null,
        ];
    }
}
