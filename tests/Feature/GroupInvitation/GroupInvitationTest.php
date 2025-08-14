<?php

namespace GroupInvitation;

use App\Mail\GroupInvitationMail;
use App\Mail\PendingGroupInvitationHasBeenRemovedMail;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use JsonException;
use Tests\TestCase;

class GroupInvitationTest extends TestCase
{
 use RefreshDatabase;

    /**
     * @throws JsonException
     */
    public function test_user_can_generate_a_group_link_to_invite_others_to_join()
    {
        $this->withoutExceptionHandling();

        // ARRANGE
        $user = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $user->id]);

        // ACT
        $response = $this->actingAs($user)
            ->post(route('groups.generate_link', $group));

        // ASSERT
        $response->assertSessionHasNoErrors();
        $this->assertCount(1, GroupInvitation::all());
        $groupInvitation = GroupInvitation::query()->first();
        $this->assertEquals($group->id, $groupInvitation->group_id);
        $this->assertEquals('link', $groupInvitation->type);
        $this->assertNull($groupInvitation->email);
        $this->assertNotNull($groupInvitation->token);
    }

    /**
     * @throws JsonException
     */
    public function test_user_cannot_generate_a_group_link_if_link_already_exists_and_not_expired()
    {
        $this->withoutExceptionHandling();

        // ARRANGE
        $user = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $user->id]);
        GroupInvitation::factory()->create([
            'type' => 'link',
            'email' => null,
            'token' => Str::uuid()->toString(),
            'accepted_at' => null,
            'expires_at' => now()->addMinutes(10),
            'group_id' => $group->id,
        ]);

        // ACT
        $response = $this->actingAs($user)
            ->post(route('groups.generate_link', $group));

        // ASSERT
        $response->assertSessionHasNoErrors();
        $this->assertCount(1, GroupInvitation::all());
        $groupInvitation = GroupInvitation::query()->first();
        $this->assertEquals($group->id, $groupInvitation->group_id);
    }

    /**
     * @throws JsonException
     */
    public function test_user_can_invite_multiple_emails_to_join_their_group()
    {
        $this->withoutExceptionHandling();

        // ARRANGE
        Mail::fake();
        $user = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $user->id]);

        // ACT
        $response = $this->actingAs($user)
            ->post(route('groups.send_invitations', $group),[
                'emails' => [
                    'test@example.com',
                    'test1@example.com',
                ],
                'force_send_email_to_pending_invites' => false
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        Mail::assertSent(GroupInvitationMail::class);
        $this->assertCount(2, GroupInvitation::all());
        $groupInvitation = GroupInvitation::query()->firstWhere('email', 'test@example.com');
        $this->assertEquals($group->id, $groupInvitation->group_id);
        $this->assertEquals('email', $groupInvitation->type);
        $this->assertNotNull($groupInvitation->token);
        $this->assertNull($groupInvitation->accepted_at);
        $this->assertTrue(now()->addHours(23) < $groupInvitation->expires_at);

        $groupInvitation1 = GroupInvitation::query()->firstWhere('email', 'test1@example.com');
        $this->assertEquals($group->id, $groupInvitation1->group_id);
        $this->assertEquals('email', $groupInvitation1->type);
        $this->assertNotNull($groupInvitation1->token);
        $this->assertNull($groupInvitation1->accepted_at);
        $this->assertTrue(now()->addHours(23) < $groupInvitation1->expires_at);
    }

    public function test_user_cannot_invite_an_email_that_is_already_in_their_group()
    {
        // ARRANGE
        Mail::fake();
        $user = User::factory()->create();
        $userAlreadyInGroup = User::factory()->create([
            'email' => 'already_in_group@example.com',
        ]);
        $group = Group::factory()->create(['user_id' => $user->id]);
        $group->members()->attach($userAlreadyInGroup);

        // ACT
        $response = $this->actingAs($user)
            ->post(route('groups.send_invitations', $group),[
                'emails' => [
                    'already_in_group@example.com'
                ],
                'force_send_email_to_pending_invites' => false
            ]);

        // ASSERT
        $response->assertSessionHasErrors([
            'emails.0' => __('validation.custom.group_invitation.already_in_group'),
        ]);
        Mail::assertNotSent(GroupInvitationMail::class);
        $this->assertCount(0, GroupInvitation::all());
    }

    public function test_user_cannot_invite_an_email_that_is_pending_in_their_group_and_if_force_option_is_false()
    {
        // ARRANGE
        Mail::fake();
        $user = User::factory()->create();
        User::factory()->create([
            'email' => 'pending_invite@example.com',
        ]);
        $group = Group::factory()->create(['user_id' => $user->id]);
        GroupInvitation::factory()->create([
            'type' => 'email',
            'email' => 'pending_invite@example.com',
            'token' => Str::uuid()->toString(),
            'accepted_at' => null,
            'expires_at' => now()->addHours(24),
            'group_id' => $group->id,
        ]);

        // ACT
        $response = $this->actingAs($user)
            ->post(route('groups.send_invitations', $group),[
                'emails' => [
                    'pending_invite@example.com'
                ],
                'force_send_email_to_pending_invites' => false
            ]);

        // ASSERT
        $response->assertSessionHasErrors([
            'emails.0' => __('validation.custom.group_invitation.is_still_pending'),
        ]);
        Mail::assertNotSent(GroupInvitationMail::class);
        $this->assertCount(1, GroupInvitation::all());
    }


    /**
     * @throws JsonException
     */
    public function test_user_can_invite_an_email_that_is_pending_in_their_group_and_if_force_option_is_true()
    {
        $this->withoutExceptionHandling();

        // ARRANGE
        Mail::fake();
        $user = User::factory()->create();
        User::factory()->create([
            'email' => 'pending_invite@example.com',
        ]);
        $group = Group::factory()->create(['user_id' => $user->id]);
        $oldToken = Str::uuid()->toString();
        $groupInvitation = GroupInvitation::factory()->create([
            'type' => 'email',
            'email' => 'pending_invite@example.com',
            'token' => $oldToken,
            'accepted_at' => null,
            'expires_at' => now()->addHours(24),
            'group_id' => $group->id,
        ]);

        // ACT
        $response = $this->actingAs($user)
            ->post(route('groups.send_invitations', $group),[
                'emails' => [
                    'pending_invite@example.com',
                ],
                'force_send_email_to_pending_invites' => true
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        Mail::assertSent(GroupInvitationMail::class);
        $this->assertNull(GroupInvitation::query()->find($groupInvitation->id));
        $this->assertCount(1, GroupInvitation::all());

        $newGroupInvitation = GroupInvitation::query()->first();
        $this->assertEquals('pending_invite@example.com', $newGroupInvitation->email);
        $this->assertEquals($group->id, $newGroupInvitation->group_id);
        $this->assertNotEquals($oldToken, $newGroupInvitation->token);
    }


    /**
     * @throws JsonException
     */
    public function test_user_can_delete_a_pending_group_invitation_from_a_group()
    {
        $this->withoutExceptionHandling();

        // ARRANGE
        Mail::fake();
        $user = User::factory()->create();
        User::factory()->create([
            'email' => 'pending_invite@example.com',
        ]);
        $group = Group::factory()->create(['user_id' => $user->id]);
        $groupInvitation = GroupInvitation::factory()->create([
            'type' => 'email',
            'email' => 'pending_invite@example.com',
            'token' => Str::uuid()->toString(),
            'accepted_at' => null,
            'expires_at' => now()->addHours(24),
            'group_id' => $group->id,
        ]);

        // ACT
        $response = $this->actingAs($user)
            ->delete(route('group_invitations.remove_invitation_from_group', $groupInvitation));

        // ASSERT
        $response->assertSessionHasNoErrors();
        Mail::assertSent(PendingGroupInvitationHasBeenRemovedMail::class);
        $this->assertNull(GroupInvitation::query()->find($groupInvitation->id));
        $this->assertCount(0, GroupInvitation::all());
    }
}
