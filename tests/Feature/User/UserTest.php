<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Notifications\SendAccountStateChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_page_is_displayed_for_admin(): void
    {
        // ARRANGE
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        // ACT
        $response = $this
            ->actingAs($admin)
            ->get('/users');

        // ASSERT
        $response->assertOk();
    }

    public function test_admin_can_create_a_user(): void
    {
        // ARRANGE
        $admin = User::factory()->create([
            'email' => 'admin@admin.com',
            'is_admin' => true,
        ]);

        // ACT
        $response = $this->actingAs($admin)
            ->post('/users', [
                'name' => 'Test',
                'email' => 'test@test.com',
                'is_active' => false,
                'is_admin' => false,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/users');

        $users = User::all();
        $user = $users->where('email', 'test@test.com')->first();
        $this->assertCount(2, $users);
        $this->assertEquals('Test', $user->name);
        $this->assertEquals('test@test.com', $user->email);
        $this->assertFalse($user->is_admin);
        $this->assertFalse($user->is_active);
    }

    public function test_non_admin_user_cannot_create_another_user(): void
    {
        // ARRANGE
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        // ACT
        $response = $this->actingAs($user)
            ->post('/users', [
                'name' => 'Test',
                'email' => 'test@test.com',
                'is_active' => false,
                'is_admin' => false,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/dashboard');

        $users = User::all();
        $this->assertCount(1, $users);
        $response->assertStatus(302);
    }

    public function test_admin_can_update_user(): void
    {
        // ARRANGE
        Notification::fake();
        $admin = User::factory()->create([
            'email' => 'admin@admin.com',
            'is_admin' => true,
        ]);
        $user = User::factory()->create([
            'name' => 'Test',
            'email' => 'test@test.com',
            'is_admin' => false,
        ]);

        // ACT
        $response = $this->actingAs($admin)
            ->put('/users/'.$user->id, [
                'is_active' => false,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/users');

        $users = User::all();
        $user = $users->where('email', 'test@test.com')->first();
        $this->assertCount(2, $users);
        $this->assertFalse($user->is_active);
        Notification::assertSentTo($user, SendAccountStateChangedNotification::class);
    }

    public function test_non_admin_user_cannot_update_user(): void
    {
        // ARRANGE
        $user = User::factory()->create([
            'is_admin' => false,
        ]);
        $userToUpdate = User::factory()->create([
            'name' => 'Test',
            'email' => 'test@test.com',
            'is_admin' => false,
        ]);

        // ACT
        $response = $this->actingAs($user)
            ->put('/users/'.$userToUpdate->id, [
                'is_active' => false,
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/dashboard');

        $users = User::all();
        $userToUpdate->refresh();
        $this->assertCount(2, $users);
        $this->assertTrue($userToUpdate->is_active);
        $response->assertStatus(302);
    }

    public function test_admin_can_delete_user(): void
    {
        // ARRANGE
        $admin = User::factory()->create([
            'email' => 'admin@admin.com',
            'is_admin' => true,
        ]);
        $user = User::factory()->create([
            'name' => 'Test',
            'email' => 'test@test.com',
            'is_admin' => false,
        ]);

        // ACT
        $response = $this
            ->actingAs($admin)
            ->delete('/users/' . $user->id, [
                'password' => 'password',
            ]);

        // ASSERT
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/users');

        $this->assertCount(1, User::all());
    }
}
