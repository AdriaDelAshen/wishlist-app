<?php

namespace Tests\Feature\Wishlist;

use App\Models\User;
use App\Models\Wishlist;
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
            ])->assertSessionHasNoErrors()->assertRedirect('/wishlists');

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
        $response->assertSessionHasErrors('wishlist');

        $this->assertCount(1, Wishlist::all());
    }
}
