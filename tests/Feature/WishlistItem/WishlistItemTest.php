<?php

namespace Tests\Feature\WishlistItem;

use App\Events\WishlistItemUserHasChanged;
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

    public function test_user_can_create_a_wishlist_item(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $wishlist = Wishlist::factory()->create(['user_id' => $user->id]);

        // ACT
        $response = $this->actingAs($user)
            ->post('/wishlist_items', [
                'item_name' => 'Pink unicorn',
                'item_description' => 'The pinkest unicorn ever made.',
                'item_url_link' => 'https://www.google.com',
                'item_price' => 13.99,
                'item_priority' => 1,
                'item_wishlist_id' => $wishlist->id,
            ]);

//        dd($response->json());

        // ASSERT
        $wishlistItems = WishlistItem::all();
        $wishlistItem = $wishlistItems->first();
        $this->assertCount(1, $wishlistItems);
        $this->assertEquals('Pink unicorn', $wishlistItem->name);
        $this->assertEquals('The pinkest unicorn ever made.', $wishlistItem->description);
        $this->assertEquals('https://www.google.com', $wishlistItem->url_link);
        $this->assertEquals(13.99, $wishlistItem->price);
        $this->assertEquals(1, $wishlistItem->priority);
        $this->assertEquals($wishlist->id, $wishlistItem->wishlist_id);
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
                'item_id' => $wishlistItem->id,
                'item_name' => 'Blue unicorn',
                'item_price' => 10.99,
                'item_priority' => 0,
            ]);

//        dd($response->json());

        // ASSERT
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

//        dd($response->json());
        // ASSERT
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

        $response->assertSessionHasErrors('wishlist_item');

        // ASSERT
        $this->assertCount(1, Wishlist::all());
    }

    public function test_user_can_add_wishlist_item_to_their_shopping_list(): void
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
            'is_bought' => false,
            'user_id' => null,
        ]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->patch('/wishlist_link_item_user/' . $wishlistItem->id, [
                'id' => $wishlistItem->id,
            ]);

//        dd($response->json());

        // ASSERT
        $wishlistItem->refresh();
        $this->assertEquals($user->id, $wishlistItem->user_id);
        $this->assertTrue($wishlistItem->is_bought);
        Event::assertDispatched(WishlistItemUserHasChanged::class);
    }

    public function test_user_can_remove_wishlist_item_to_their_shopping_list(): void
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
            'is_bought' => true,
            'user_id' => $user->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->patch('/wishlist_unlink_item_user/' . $wishlistItem->id, [
                'id' => $wishlistItem->id,
            ]);

//        dd($response->json());

        // ASSERT
        $wishlistItem->refresh();
        $this->assertNull($wishlistItem->user_id);
        $this->assertFalse($wishlistItem->is_bought);
        Event::assertDispatched(WishlistItemUserHasChanged::class);
    }
}
