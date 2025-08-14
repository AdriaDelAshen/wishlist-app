<?php

namespace Tests\Feature\WishlistItem;

use App\Enums\WishlistItemTypeEnum;
use App\Events\GroupMemberJoined;
use App\Events\GroupMemberLeft;
use App\Events\WishlistItemUserHasChanged;
use App\Models\Group;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WishlistItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_item_page_is_displayed(): void
    {
        $this->withoutExceptionHandling();
        // ARRANGE
        $user = User::factory()->create();
        $wishlistItem = WishlistItem::factory()->create();

        // ACT (Show page)
        $response = $this
            ->actingAs($user)
            ->get('/wishlist_items/'.$wishlistItem->id);

        // ASSERT
        $response->assertOk();
    }

    public function test_user_can_create_a_wishlist_item_as_a_single_person_gift(): void
    {
        $this->withoutExceptionHandling();
        // ARRANGE
        $user = User::factory()->create();
        $wishlist = Wishlist::factory()->create(['user_id' => $user->id]);

        // ACT
        $response = $this->actingAs($user)
            ->post('/wishlist_items', [
                'name' => 'Pink unicorn',
                'description' => 'The pinkest unicorn ever made.',
                'url_link' => 'https://www.google.com',
                'price' => 13.99,
                'type' => WishlistItemTypeEnum::ONE_PERSON_GIFT->value,
                'priority' => 1,
                'wishlist_id' => $wishlist->id,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $wishlistItems = WishlistItem::all();
        $wishlistItem = $wishlistItems->first();
        $this->assertCount(1, $wishlistItems);
        $this->assertEquals('Pink unicorn', $wishlistItem->name);
        $this->assertEquals('The pinkest unicorn ever made.', $wishlistItem->description);
        $this->assertEquals('https://www.google.com', $wishlistItem->url_link);
        $this->assertEquals(13.99, $wishlistItem->price);
        $this->assertEquals(WishlistItemTypeEnum::ONE_PERSON_GIFT, $wishlistItem->type);
        $this->assertEquals(1, $wishlistItem->priority);
        $this->assertEquals($wishlist->id, $wishlistItem->wishlist_id);
        $this->assertFalse($wishlistItem->in_shopping_list);
        $this->assertFalse($wishlistItem->is_bought);
        $this->assertNull($wishlistItem->user_id);
    }

    public function test_user_can_create_a_wishlist_item_as_a_group_gift(): void
    {
        $this->withoutExceptionHandling();
        // ARRANGE
        $user = User::factory()->create();
        $wishlist = Wishlist::factory()->create(['user_id' => $user->id]);

        // ACT
        $response = $this->actingAs($user)
            ->post('/wishlist_items', [
                'name' => 'Pink unicorn',
                'description' => 'The pinkest unicorn ever made.',
                'url_link' => 'https://www.google.com',
                'price' => 13.99,
                'type' => WishlistItemTypeEnum::GROUP_GIFT->value,
                'priority' => 1,
                'wishlist_id' => $wishlist->id,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $wishlistItems = WishlistItem::all();
        $wishlistItem = $wishlistItems->first();
        $this->assertCount(1, $wishlistItems);
        $this->assertEquals('Pink unicorn', $wishlistItem->name);
        $this->assertEquals('The pinkest unicorn ever made.', $wishlistItem->description);
        $this->assertEquals('https://www.google.com', $wishlistItem->url_link);
        $this->assertEquals(13.99, $wishlistItem->price);
        $this->assertEquals(WishlistItemTypeEnum::GROUP_GIFT, $wishlistItem->type);
        $this->assertEquals(1, $wishlistItem->priority);
        $this->assertEquals($wishlist->id, $wishlistItem->wishlist_id);
        $this->assertFalse($wishlistItem->in_shopping_list);
        $this->assertFalse($wishlistItem->is_bought);
        $this->assertNull($wishlistItem->user_id);
    }

    public function test_wishlist_item_information_can_be_updated(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $wishlist = Wishlist::factory()->create(['user_id' => $user->id]);
        $wishlistItem = WishlistItem::factory()->create([
            'name' => 'Pink unicorn',
            'price' => 13.99,
            'priority' => 1,
            'wishlist_id' => $wishlist->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->patch('/wishlist_items/' . $wishlistItem->id, [
                'id' => $wishlistItem->id,
                'name' => 'Blue unicorn',
                'price' => 10.99,
                'priority' => 0,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $wishlistItem->refresh();

        $WishlistItem = WishlistItem::all();
        $this->assertCount(1, $WishlistItem);
        $this->assertEquals('Blue unicorn', $wishlistItem->name);
        $this->assertEquals(10.99, $wishlistItem->price);
        $this->assertEquals(0, $wishlistItem->priority);
    }

    public function test_user_can_delete_their_wishlist_item(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $wishlist = Wishlist::factory()->create(['user_id' => $user->id]);
        $wishlistItem = WishlistItem::factory()->create(['wishlist_id' => $wishlist->id]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->delete('/wishlist_items/' . $wishlistItem->id);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertCount(0, WishlistItem::all());
    }

    public function test_user_cannot_delete_not_owned_wishlist_item(): void
    {
        // ARRANGE
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $userB->id,
        ]);
        $wishlistItem = WishlistItem::factory()->create(['wishlist_id' => $wishlist->id]);

        // ACT
        $response = $this
            ->actingAs($userA)
            ->delete('/wishlist_items/' . $wishlistItem->id);

        // ASSERT
        $response->assertSessionHasErrors('wishlist_item');
        $response->assertRedirect();

        $this->assertCount(1, Wishlist::all());
    }

    public function test_user_can_add_single_person_gift_item_to_their_shopping_list(): void
    {
        // ARRANGE
        Event::fake();
        $user = User::factory()->create();
        $wishlistOwner = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $wishlistOwner->id,
        ]);
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'type' => WishlistItemTypeEnum::ONE_PERSON_GIFT->value,
            'in_shopping_list' => false,
            'user_id' => null,
        ]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->patch('/wishlist_item_link_item_user/' . $wishlistItem->id, [
                'id' => $wishlistItem->id,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertOk();

        $wishlistItem->refresh();
        $this->assertEquals($user->id, $wishlistItem->user_id);
        $this->assertTrue($wishlistItem->in_shopping_list);
        Event::assertDispatched(WishlistItemUserHasChanged::class);
        Event::assertNotDispatched(GroupMemberJoined::class);
    }

    public function test_user_can_remove_single_person_gift_item_to_their_shopping_list(): void
    {
        // ARRANGE
        Event::fake();
        $user = User::factory()->create();
        $wishlistOwner = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $wishlistOwner->id,
        ]);
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'type' => WishlistItemTypeEnum::ONE_PERSON_GIFT->value,
            'in_shopping_list' => true,
            'is_bought' => false,
            'user_id' => $user->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->patch('/wishlist_item_unlink_item_user/' . $wishlistItem->id, [
                'id' => $wishlistItem->id,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertOk();

        $wishlistItem->refresh();
        $this->assertNull($wishlistItem->user_id);
        $this->assertFalse($wishlistItem->in_shopping_list);
        Event::assertDispatched(WishlistItemUserHasChanged::class);
        Event::assertNotDispatched(GroupMemberLeft::class);
    }

    public function test_first_user_that_adds_group_gift_item_to_their_shopping_list_also_creates_group(): void
    {
        $this->withoutExceptionHandling();
        // ARRANGE
        Event::fake();
        $currentUser = User::factory()->create();
        $wishlistOwner = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $wishlistOwner->id,
        ]);
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'type' => WishlistItemTypeEnum::GROUP_GIFT->value,
            'in_shopping_list' => false,
            'user_id' => null,
        ]);

        // ACT
        $response = $this
            ->actingAs($currentUser)
            ->patch('/wishlist_item_link_item_user/' . $wishlistItem->id, [
                'id' => $wishlistItem->id,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertOk();

        $wishlistItem->refresh();
        $this->assertEquals($currentUser->id, $wishlistItem->user_id);
        $this->assertTrue($wishlistItem->in_shopping_list);

        //Group
        $this->assertCount(1, Group::all());
        $giftGroup = Group::query()->first();
        $this->assertEquals('#' . $wishlistItem->id.' - '.$wishlistItem->name, $giftGroup->name);

        //Members
        $this->assertCount(1, $giftGroup->members);
        $member = $giftGroup->members->first();
        $this->assertEquals($currentUser->id, $member->id);

        Event::assertDispatched(WishlistItemUserHasChanged::class);
        Event::assertNotDispatched(GroupMemberJoined::class);
    }

    public function test_user_can_add_group_gift_item_to_their_shopping_list_and_becomes_member_of_gift_group(): void
    {
        $this->withoutExceptionHandling();
        // ARRANGE
        Event::fake();
        $firstGroupMember = User::factory()->create();
        $currentUser = User::factory()->create();
        $wishlistOwner = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $wishlistOwner->id,
        ]);
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'type' => WishlistItemTypeEnum::GROUP_GIFT->value,
            'in_shopping_list' => false,
            'user_id' => null,
            'group_id' => null,
        ]);
        $group = Group::factory()->create([
            'name' => '#' . $wishlistItem->id.' - '.$wishlistItem->name,
            'user_id' => null,
            'is_private' => true,
            'is_Active' => true,
        ]);
        $group->members()->attach($firstGroupMember);
        $wishlistItem->update([
           'group_id' => $group->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($currentUser)
            ->patch('/wishlist_item_link_item_user/' . $wishlistItem->id, [
                'id' => $wishlistItem->id,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertOk();

        $wishlistItem->refresh();
        $this->assertEquals($currentUser->id, $wishlistItem->user_id);
        $this->assertTrue($wishlistItem->in_shopping_list);

        //Group
        $this->assertCount(1, Group::all());
        $giftGroup = Group::query()->first();

        //Members
        $this->assertCount(2, $giftGroup->members);
        $member = $giftGroup->members->last();
        $this->assertEquals($currentUser->id, $member->id);

        Event::assertDispatched(WishlistItemUserHasChanged::class);
        Event::assertDispatched(GroupMemberJoined::class);
    }

    public function test_member_of_the_group_gift_can_remove_that_item_to_their_shopping_list(): void
    {
        // ARRANGE
        Event::fake();
        $firstGroupMember = User::factory()->create();
        $currentUser = User::factory()->create();
        $wishlistOwner = User::factory()->create();
        $group = Group::factory()->create([
            'user_id' => null,
            'is_private' => true,
            'is_Active' => true,
        ]);
        $group->members()->attach($firstGroupMember);
        $wishlist = Wishlist::factory()->create([
            'user_id' => $wishlistOwner->id,
        ]);
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'type' => WishlistItemTypeEnum::GROUP_GIFT->value,
            'in_shopping_list' => true,
            'is_bought' => false,
            'user_id' => $currentUser->id,
            'group_id' => $group->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($currentUser)
            ->patch('/wishlist_item_unlink_item_user/' . $wishlistItem->id, [
                'id' => $wishlistItem->id,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertOk();

        $wishlistItem->refresh();
        $this->assertNotNull($wishlistItem->user_id);
        $this->assertTrue($wishlistItem->in_shopping_list);

        $this->assertCount(1, Group::all());
        $giftGroup = Group::query()->first();

        //Members
        $this->assertCount(1, $giftGroup->members);
        Event::assertDispatched(WishlistItemUserHasChanged::class);
        Event::assertDispatched(GroupMemberLeft::class);
    }

    public function test_last_group_member_of_the_group_gift_can_remove_that_item_to_their_shopping_list(): void
    {
        // ARRANGE
        Event::fake();
        $currentUser = User::factory()->create();
        $wishlistOwner = User::factory()->create();
        $group = Group::factory()->create([
            'user_id' => null,
            'is_private' => true,
            'is_Active' => true,
        ]);
        $wishlist = Wishlist::factory()->create([
            'user_id' => $wishlistOwner->id,
        ]);
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'type' => WishlistItemTypeEnum::GROUP_GIFT->value,
            'in_shopping_list' => true,
            'is_bought' => false,
            'user_id' => $currentUser->id,
            'group_id' => $group->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($currentUser)
            ->patch('/wishlist_item_unlink_item_user/' . $wishlistItem->id, [
                'id' => $wishlistItem->id,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertOk();

        $wishlistItem->refresh();
        $this->assertNull($wishlistItem->user_id);
        $this->assertFalse($wishlistItem->in_shopping_list);

        $this->assertCount(0, Group::all());
        Event::assertDispatched(WishlistItemUserHasChanged::class);
        Event::assertNotDispatched(GroupMemberLeft::class);
    }

    public function test_user_cannot_remove_wishlist_item_from_their_shopping_list_if_item_is_bought(): void
    {
        // ARRANGE
        Event::fake();
        $user = User::factory()->create();
        $wishlistOwner = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $wishlistOwner->id,
        ]);
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'in_shopping_list' => true,
            'is_bought' => true,
            'user_id' => $user->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->patch('/wishlist_item_unlink_item_user/' . $wishlistItem->id, [
                'id' => $wishlistItem->id,
            ]);

        // ASSERT
        $response->assertSessionHasErrors();

        $wishlistItem->refresh();
        $this->assertEquals($user->id, $wishlistItem->user_id);
        $this->assertTrue($wishlistItem->in_shopping_list);
        $this->assertTrue($wishlistItem->is_bought);
        Event::assertNotDispatched(WishlistItemUserHasChanged::class);
    }

    public function test_user_can_set_wishlist_item_as_bought(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $wishlistOwner = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $wishlistOwner->id,
        ]);
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'in_shopping_list' => true,
            'is_bought' => false,
            'user_id' => $user->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->patch('/wishlist_item_state_has_changed/' . $wishlistItem->id, [
                'is_bought' => true,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertOk();

        $wishlistItem->refresh();
        $this->assertEquals($user->id, $wishlistItem->user_id);
        $this->assertTrue($wishlistItem->in_shopping_list);
        $this->assertTrue($wishlistItem->is_bought);
    }

    public function test_user_can_set_wishlist_item_as_unbought(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $wishlistOwner = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $wishlistOwner->id,
        ]);
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'in_shopping_list' => true,
            'is_bought' => true,
            'user_id' => $user->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->patch('/wishlist_item_state_has_changed/' . $wishlistItem->id, [
                'is_bought' => false,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertOk();

        $wishlistItem->refresh();
        $this->assertEquals($user->id, $wishlistItem->user_id);
        $this->assertTrue($wishlistItem->in_shopping_list);
        $this->assertFalse($wishlistItem->is_bought);
    }

    public function test_user_cannot_set_wishlist_item_as_bought_if_item_is_not_in_their_shopping_list(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $wishlistOwner = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $wishlistOwner->id,
        ]);
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'in_shopping_list' => false,
            'is_bought' => false,
            'user_id' => $otherUser->id,//Item is in another user's shopping list.
        ]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->patch('/wishlist_item_state_has_changed/' . $wishlistItem->id, [
                'is_bought' => true,
            ]);

        // ASSERT
        $response->assertSessionHasErrors();

        $wishlistItem->refresh();
        $this->assertEquals($otherUser->id, $wishlistItem->user_id);
        $this->assertFalse($wishlistItem->in_shopping_list);
        $this->assertFalse($wishlistItem->is_bought);
    }

    public function test_member_of_group_gift_can_contribute_to_the_associated_wishlist_item(): void
    {
        // ARRANGE
        $group = Group::factory()->create([
            'user_id' => null,
        ]);
        $wishListItemAsAGroupGift = WishlistItem::factory()->create([
            'price' => 100,
            'is_bought' => false,
            'in_shopping_list' => true,
            'type' => WishlistItemTypeEnum::GROUP_GIFT->value,
            'group_id' => $group->id,
            'user_id' => null,
        ]);
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $group->members()->attach($userA, ['contribution_amount' => 25]);
        $group->members()->attach($userB, ['contribution_amount' => 0]);

        // ACT
        $response = $this
            ->actingAs($userB)
            ->patch(route('wishlist_items.update_contribution', $wishListItemAsAGroupGift->id), [
                'contribution_amount' => 50.50,
            ]);

        // ASSERT
        $response->assertOk();
        $wishListItemAsAGroupGift->refresh();
        $memberA = User::with(['groups' => function ($query) use ($group) {
            $query->where('groups.id', $group->id);
        }])->find($userA->id);
        $memberB = User::with(['groups' => function ($query) use ($group) {
            $query->where('groups.id', $group->id);
        }])->find($userB->id);

        $this->assertEquals(25, $memberA->groups->first()->pivot->contribution_amount);
        $this->assertEquals(50.50, $memberB->groups->first()->pivot->contribution_amount);
        $this->assertEquals(75.50, $wishListItemAsAGroupGift->contributed_amount);
    }
}
