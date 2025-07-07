<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::truncate();
        Wishlist::truncate();
        WishlistItem::truncate();
        Group::truncate();
        GroupInvitation::truncate();
        DB::table('group_user')->truncate();
        $this->call([
            WishlistSeeder::class,
            GroupSeeder::class,
        ]);
    }
}
