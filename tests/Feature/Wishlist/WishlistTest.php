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


    public static function filtersProvider(): array
    {
        $dateInTheFuture = new Carbon();
        $dateInTheFuture->addDay();
        $dateInThePast = new Carbon();
        $dateInThePast->subDay();
        //after_expiration_date, wishlist_scope, count, ids of current wishlists
        return [
            ['', 'all', 4, [1,2,3,4]],
            [$dateInTheFuture->toDateString(), 'all', 1, [1]],
            [$dateInThePast->toDateString(), 'all', 3, [1,2,4]],
            ['', 'mine', 2, [1,2]],
            [$dateInTheFuture->toDateString(), 'mine', 1, [1,2]],
            [$dateInThePast->toDateString(), 'mine', 2, [1,2]],
        ];
    }

    /**
     * @dataProvider filtersProvider
     */
    public function test_wishlists_can_be_filtered(string $afterExpirationDate, string $wishlistScope, int $count, array $ids): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $this->actingAs($user);

        $dateInTheFuture = new Carbon();
        $dateInTheFuture->addYear();
        $dateInThePast = new Carbon();
        $dateInThePast->subYear();

        Wishlist::factory()->create([
            'id' => 1,
            'user_id' => $user->id,
            'expiration_date' => $dateInTheFuture,
        ]);
        Wishlist::factory()->create([
            'id' => 2,
            'user_id' => $user->id,
            'is_shared' => false,
            'expiration_date' => new Carbon(),
        ]);
        Wishlist::factory()->create([
            'id' => 3,
            'is_shared' => true,
            'expiration_date' => $dateInThePast,
        ]);
        Wishlist::factory()->create([
            'id' => 4,
            'is_shared' => true,
            'expiration_date' => new Carbon(),
        ]);

        // ACT
        $responseFiltered = $this->getJson(route('wishlists.get_current_data_page', [
            'after_expiration_date' => $afterExpirationDate,
            'wishlist_scope' => $wishlistScope,
            'page' => 1,
            'perPage' => 10
        ]));

        // ASSERT
        $responseFiltered->assertOk();
        $responseFiltered->assertJsonCount($count, 'pagination.data');

        foreach ($responseFiltered->json('pagination.data') as $wishlistData) {
            $this->assertContains($wishlistData['id'], $ids);
        }
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
