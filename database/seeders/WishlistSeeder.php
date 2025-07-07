<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 users
        $users = User::factory(10)->create();

        // For each user, create 5 wishlists
        foreach ($users as $user) {
            $wishlists = Wishlist::factory(5)
                ->create([
                    'user_id' => $user,
                ]);

            foreach ($wishlists as $wishlist) {
                // Pick random users that are not the wishlist owner
                $otherUsers = $users->where('id', '!=', $user->id)->values();

                // Create 10–20 items for each wishlist
                WishlistItem::factory()
                    ->count(rand(10, 20))
                    ->create([
                        'wishlist_id' => $wishlist,
                        'user_id' => $otherUsers->random(),
                        'in_shopping_list' => true
                    ]);

                WishlistItem::factory()
                    ->count(rand(1, 2))
                    ->create([
                        'wishlist_id' => $wishlist,
                        'user_id' => null,
                        'in_shopping_list' => false,
                        'is_bought' => false
                    ]);
            }
        }
    }
}
