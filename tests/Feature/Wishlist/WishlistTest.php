<?php

namespace Tests\Feature\Wishlist;

use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlists_page_is_displayed(): void
    {
        // ARRANGE
        $user = User::factory()->create();

        // ACT
        $response = $this
            ->actingAs($user)
            ->get('/wishlists');

        // ASSERT
        $response->assertOk();
    }

    public function test_wishlists_can_be_filtered_by_expiration_date(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $this->actingAs($user);

        $date1 = '2024-12-25';
        $date2 = '2025-01-10';

        $wishlistsDate1 = Wishlist::factory()->count(2)->create([
            'user_id' => $user->id,
            'expiration_date' => $date1,
        ]);
        Wishlist::factory()->create([
            'user_id' => $user->id,
            'expiration_date' => $date2,
        ]);

        // ACT & ASSERT for filtered request
        $responseFiltered = $this->getJson(route('wishlists.get_current_data_page', [
            'expiration_date' => $date1,
            'page' => 1,
            'perPage' => 10
        ]));

        $responseFiltered->assertOk();
        $responseFiltered->assertJsonCount(2, 'pagination.data');

        $expectedIdsDate1 = $wishlistsDate1->pluck('id')->toArray();
        foreach ($responseFiltered->json('pagination.data') as $wishlistData) {
            $this->assertContains($wishlistData['id'], $expectedIdsDate1);
            $this->assertEquals($date1, Carbon::parse($wishlistData['expiration_date'])->toDateString());
        }

        // ACT & ASSERT for unfiltered request
        $responseUnfiltered = $this->getJson(route('wishlists.get_current_data_page', [
            'page' => 1,
            'perPage' => 10
        ]));

        $responseUnfiltered->assertOk();
        $responseUnfiltered->assertJsonCount(3, 'pagination.data'); // All wishlists for the user
        $responseUnfiltered->assertJsonPath('pagination.total', 3);
    }

    public function test_wishlists_can_be_filtered_by_scope_and_date(): void
    {
        // ARRANGE
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $date1 = '2024-12-01';
        $date2 = '2024-12-25';

        // Wishlists for $user1
        $w1_user1_mine_date1 = Wishlist::factory()->create([
            'user_id' => $user1->id,
            'name' => 'User1 Mine Date1',
            'is_shared' => false,
            'expiration_date' => $date1,
        ]);
        $w2_user1_mine_date2 = Wishlist::factory()->create([
            'user_id' => $user1->id,
            'name' => 'User1 Mine Date2',
            'is_shared' => false,
            'expiration_date' => $date2,
        ]);

        // Wishlists for $user2
        $w3_user2_shared_date1 = Wishlist::factory()->create([
            'user_id' => $user2->id,
            'name' => 'User2 Shared Date1',
            'is_shared' => true,
            'expiration_date' => $date1,
        ]);
        $w4_user2_mine_date2 = Wishlist::factory()->create([
            'user_id' => $user2->id,
            'name' => 'User2 Mine Date2',
            'is_shared' => false,
            'expiration_date' => $date2,
        ]);

        // --- Test Case: Scope 'mine' ---
        $responseMine = $this->actingAs($user1)->getJson(route('wishlists.get_current_data_page', [
            'wishlist_scope' => 'mine',
            'page' => 1, 'perPage' => 10
        ]));
        $responseMine->assertOk();
        $responseMine->assertJsonPath('pagination.total', 2);
        $responseMine->assertJsonFragment(['id' => $w1_user1_mine_date1->id]);
        $responseMine->assertJsonFragment(['id' => $w2_user1_mine_date2->id]);
        $responseMine->assertJsonMissing(['id' => $w3_user2_shared_date1->id]);
        $responseMine->assertJsonMissing(['id' => $w4_user2_mine_date2->id]);

        // --- Test Case: Scope 'all' ---
        $responseAll = $this->actingAs($user1)->getJson(route('wishlists.get_current_data_page', [
            'wishlist_scope' => 'all',
            'page' => 1, 'perPage' => 10
        ]));
        $responseAll->assertOk();
        $responseAll->assertJsonPath('pagination.total', 3);
        $responseAll->assertJsonFragment(['id' => $w1_user1_mine_date1->id]);
        $responseAll->assertJsonFragment(['id' => $w2_user1_mine_date2->id]);
        $responseAll->assertJsonFragment(['id' => $w3_user2_shared_date1->id]); // Shared by user2, visible to user1
        $responseAll->assertJsonMissing(['id' => $w4_user2_mine_date2->id]); // Private to user2

        // --- Test Case: Scope 'mine' with expiration_date filter ---
        $responseMineDate = $this->actingAs($user1)->getJson(route('wishlists.get_current_data_page', [
            'wishlist_scope' => 'mine',
            'expiration_date' => $date1,
            'page' => 1, 'perPage' => 10
        ]));
        $responseMineDate->assertOk();
        $responseMineDate->assertJsonPath('pagination.total', 1);
        $responseMineDate->assertJsonFragment(['id' => $w1_user1_mine_date1->id]);
        $responseMineDate->assertJsonMissing(['id' => $w2_user1_mine_date2->id]);

        // --- Test Case: Scope 'all' with expiration_date filter ---
        $responseAllDate = $this->actingAs($user1)->getJson(route('wishlists.get_current_data_page', [
            'wishlist_scope' => 'all',
            'expiration_date' => $date1,
            'page' => 1, 'perPage' => 10
        ]));
        $responseAllDate->assertOk();
        $responseAllDate->assertJsonPath('pagination.total', 2);
        $responseAllDate->assertJsonFragment(['id' => $w1_user1_mine_date1->id]);
        $responseAllDate->assertJsonFragment(['id' => $w3_user2_shared_date1->id]);
        $responseAllDate->assertJsonMissing(['id' => $w2_user1_mine_date2->id]);
        $responseAllDate->assertJsonMissing(['id' => $w4_user2_mine_date2->id]);
    }

    public function test_user_can_create_a_wishlist(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $expirationDate = Carbon::today()->addDays(30);

        // ACT
        $response = $this->actingAs($user)
            ->post('/wishlists', [
                'name' => 'My wishlist',
                'expiration_date' => $expirationDate->format('Y-m-d'),
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/wishlists');

        $wishlists = Wishlist::all();
        $wishlist = $wishlists->first();
        $this->assertCount(1, $wishlists);
        $this->assertEquals('My wishlist', $wishlist->name);
        $this->assertEquals($expirationDate, $wishlist->expiration_date);
        $this->assertFalse($wishlist->is_shared);
    }

    public function test_user_cannot_create_a_wishlist_due_in_the_past(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $expirationDate = Carbon::today();

        // ACT
        $response = $this->actingAs($user)
            ->post('/wishlists', [
                'name' => 'My wishlist',
                'expiration_date' => $expirationDate->format('Y-m-d'),
            ]);

        // ASSERT
        $response->assertSessionHasErrors('expiration_date');

        $wishlists = Wishlist::all();
        $this->assertCount(0, $wishlists);
    }

    public function test_wishlist_information_can_be_updated(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $user->id,
        ]);
        $expirationDate = Carbon::today()->addDays(30);

        // ACT
        $response = $this
            ->actingAs($user)
            ->patch('/wishlists/' . $wishlist->id, [
                'name' => 'My wishlist',
                'expiration_date' => $expirationDate->format('Y-m-d'),
                'is_shared' => true,
            ]);

        // ASSERT
        $wishlist->refresh();

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/wishlists');

        $wishlists = Wishlist::all();
        $this->assertCount(1, $wishlists);
        $this->assertEquals('My wishlist', $wishlist->name);
        $this->assertEquals($expirationDate, $wishlist->expiration_date);
        $this->assertTrue($wishlist->is_shared);
    }

    public function test_user_can_delete_their_wishlist(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $user->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($user)
            ->delete('/wishlists/' . $wishlist->id);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/wishlists');

        $this->assertCount(0, Wishlist::all());
    }

    public function test_user_cannot_delete_not_owned_wishlist(): void
    {
        // ARRANGE
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $wishlist = Wishlist::factory()->create([
            'user_id' => $userB->id,
        ]);

        // ACT
        $response = $this
            ->actingAs($userA)
            ->delete('/wishlists/' . $wishlist->id);

        // ASSERT
        $response->assertForbidden();

        $this->assertCount(1, Wishlist::all());
    }

    public function test_user_can_duplicate_own_wishlist_with_items(): void
    {
        // ARRANGE
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $originalWishlist = Wishlist::factory()
            ->for($user)
            ->create([
                'name' => 'Birthday Wishlist',
                'is_shared' => true,
                'expiration_date' => Carbon::today()->subDay(),
                'can_be_duplicated' => false,
            ]);
        WishlistItem::factory()->for($originalWishlist)->create([
            'name' => 'Barbie doll',
            'description' => 'A doll for kid',
            'url_link' => 'www.google.com',
            'price' => 14.99,
            'priority' => 1,
            'in_shopping_list' => true,
            'is_bought' => true,
            'user_id' => $otherUser,
        ]);

        // ACT
        $response = $this->actingAs($user)
            ->post(route('wishlists.duplicate', $originalWishlist));

        // ASSERT
        $newWishlist = Wishlist::query()->where('name', 'Birthday Wishlist (Copy)')->first();
        $response->assertRedirect('/wishlists/'.$newWishlist->id);
        $this->assertDatabaseHas('wishlists', [
            'name' => 'Birthday Wishlist (Copy)',
            'user_id' => $user->id,
        ]);

        // Ensure new wishlist is not null and has same number of items
        $this->assertNotNull($newWishlist);
        $this->assertFalse($newWishlist->is_shared);
        $this->assertFalse($newWishlist->can_be_duplicated);
        $this->assertEquals(Carbon::now()->addDays(30)->toDateString(), $newWishlist->expiration_date->toDateString());
        $this->assertCount(1, $newWishlist->wishlistItems);

        $newWishlistItem = $newWishlist->wishlistItems->first();
        $this->assertEquals('Barbie doll', $newWishlistItem->name);
        $this->assertEquals('A doll for kid', $newWishlistItem->description);
        $this->assertEquals('www.google.com', $newWishlistItem->url_link);
        $this->assertEquals(14.99, $newWishlistItem->price);
        $this->assertEquals(1, $newWishlistItem->priority);
        $this->assertFalse($newWishlistItem->in_shopping_list);
        $this->assertFalse($newWishlistItem->is_bought);
        $this->assertNull($newWishlistItem->user_id);
    }

    public function test_user_cannot_duplicate_someone_wishlist_that_cannot_be_duplicated(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $originalWishlist = Wishlist::factory()
            ->for($otherUser)
            ->create([
                'name' => 'Birthday Wishlist',
                'is_shared' => true,
                'expiration_date' => Carbon::today()->subDay(),
                'can_be_duplicated' => false,
            ]);
        WishlistItem::factory()->for($originalWishlist)->create([
            'name' => 'Barbie doll',
            'description' => 'A doll for kid',
            'url_link' => 'www.google.com',
            'price' => 14.99,
            'priority' => 1,
            'in_shopping_list' => true,
            'is_bought' => true,
            'user_id' => $user,
        ]);

        // ACT
        $response = $this->actingAs($user)
            ->post(route('wishlists.duplicate', $originalWishlist));

        $response->assertForbidden(); // Or assertStatus(403) depending on policy
    }

    public function test_user_can_duplicate_someone_wishlist_if_it_can_be_duplicated(): void
    {
        // ARRANGE
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $originalWishlist = Wishlist::factory()
            ->for($otherUser)
            ->create([
                'name' => 'Birthday Wishlist',
                'can_be_duplicated' => true,
            ]);
        WishlistItem::factory()->for($originalWishlist)->create();

        // ACT
        $response = $this->actingAs($user)
            ->post(route('wishlists.duplicate', $originalWishlist));

        // ASSERT
        $newWishlist = Wishlist::query()->where('name', 'Birthday Wishlist (Copy)')->first();
        $response->assertRedirect('/wishlists/'.$newWishlist->id);
        $this->assertDatabaseHas('wishlists', [
            'name' => 'Birthday Wishlist (Copy)',
            'user_id' => $user->id,
        ]);

        // Ensure new wishlist is not null and has same number of items
        $this->assertNotNull($newWishlist);
        $this->assertCount(1, $newWishlist->wishlistItems);
    }
}
