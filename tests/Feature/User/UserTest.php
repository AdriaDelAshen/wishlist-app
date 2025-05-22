<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Notifications\SendAccountStateChangedNotification;
use App\Notifications\UpcomingBirthdayNotification;
use Carbon\Carbon;
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
                'preferred_locale' => 'fr',
                'birthday_date' => '2000-01-01',
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
        $this->assertEquals('fr', $user->preferred_locale);
        $this->assertEquals(new Carbon('2000-01-01'), $user->birthday_date);
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
        $response->assertRedirect('/users/'.$user->id.'/edit');

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

    public function it_sends_birthday_notification_to_users_with_upcoming_birthdays(): void
    {
        // Prevent actual notifications
        Notification::fake();

        // Create a user whose birthday is 14 days from now
        $user = User::factory()->create([
            'birthday_date' => now()->subYears(20)->addDays(14)->format('Y-m-d'),
        ]);

        // Run the command
        $this->artisan('notify:upcoming-birthdays')
            ->assertExitCode(0);

        // Assert notification was sent
        Notification::assertSentTo(
            $user,
            UpcomingBirthdayNotification::class
        );
    }

    public function it_does_not_notify_users_without_upcoming_birthdays(): void
    {
        Notification::fake();

        // User with birthday far away
        User::factory()->create([
            'birthday_date' => now()->subYears(20)->addDays(30)->format('Y-m-d'),
        ]);

        $this->artisan('notify:upcoming-birthdays')
            ->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function it_does_not_notify_users_with_no_birthday_date(): void
    {
        Notification::fake();

        // User with a null birthday
        User::factory()->create([
            'birthday_date' => null,
        ]);

        $this->artisan('notify:upcoming-birthdays')
            ->assertExitCode(0);

        Notification::assertNothingSent();
    }
}
