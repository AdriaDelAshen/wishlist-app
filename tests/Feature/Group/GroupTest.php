<?php

namespace Group;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JsonException;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_groups_page_is_displayed(): void
    {
        // ARRANGE
        $user = User::factory()->create();

        // ACT
        $response = $this
            ->actingAs($user)
            ->get('/groups');

        // ASSERT
        $response->assertOk();
    }

    /**
     * @throws JsonException
     */
    public function test_user_can_create_a_group(): void
    {
        // ARRANGE
        $user = User::factory()->create();

        // ACT
        $response = $this->actingAs($user)
            ->post('/groups', [
                'name' => 'Family',
                'description' => 'Family',
                'is_private' => false,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/groups');

        $groups = Group::all();
        $group = $groups->first();
        $this->assertCount(1, $groups);
        $this->assertEquals('Family', $group->name);
        $this->assertEquals('Family', $group->description);
        $this->assertFalse($group->is_private);
        $this->assertTrue($group->is_active);
        $this->assertEquals($user->id, $group->user_id);
    }

    /**
     * @throws JsonException
     */
    public function test_group_information_can_be_updated(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $group = Group::factory()->create([
            'user_id' => $user->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->patch('/groups/' . $group->id, [
                'name' => 'Work',
                'is_active' => false,
            ]);

        // ASSERT
        $group->refresh();

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/groups');

        $groups = Group::all();
        $this->assertCount(1, $groups);
        $this->assertEquals('Work', $group->name);
        $this->assertFalse($group->is_active);
    }

    /**
     * @throws JsonException
     */
    public function test_user_can_delete_their_group(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $group = Group::factory()->create([
            'user_id' => $user->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->delete('/groups/' . $group->id);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/groups');

        $this->assertCount(0, Group::all());
    }

    public function test_user_cannot_delete_not_owned_wishlist(): void
    {
        // ARRANGE
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $group = Group::factory()->create([
            'user_id' => $userB->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($userA)
            ->delete('/groups/' . $group->id);

        // ASSERT
        $response->assertForbidden();

        $this->assertCount(1, Group::all());
    }
}
