<?php

namespace Database\Factories;

use App\Models\User;
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
        $inShoppingList = fake()->boolean;
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'url_link' => fake()->url(),
            'price' => fake()->randomFloat(2, 10, 500),
            'priority' => fake()->numberBetween(0, 5),
            'in_shopping_list' => $inShoppingList,
            'is_bought' => $inShoppingList ? fake()->boolean : false,
            'wishlist_id' => Wishlist::factory(),
            'user_id' => $inShoppingList ? User::factory() : null,
        ];
    }
}
