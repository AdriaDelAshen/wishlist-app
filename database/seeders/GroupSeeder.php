<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        // Create 10 groups
        $groups = Group::factory(10)
            ->create([
                'user_id' => $users->random()->id,
            ]);

        // For each group, link a user
        foreach ($groups as $group) {
            $otherUser = $users->where('id', '!=', $group->user_id)->values()->random();
            $group->members()->attach($group->user_id);
            $group->members()->attach($otherUser->id);
            GroupInvitation::factory()
                ->count(rand(1, 3))
                ->create([
                    'group_id' => $group->id,
                ]);
        }
    }
}
