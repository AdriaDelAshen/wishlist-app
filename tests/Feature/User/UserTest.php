<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Notifications\SendAccountStateChangedNotification;
use App\Notifications\UserMadeAdminNotification;
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

    public function test_it_sends_birthday_notification_to_users_with_upcoming_birthdays(): void
    {
        // ARRANGE
        Notification::fake();

        // Create a user whose birthday is 14 days from now
        $user = User::factory()->create([
            'birthday_date' => now()->subYears(20)->addDays(14)->format('Y-m-d'),
        ]);

        // ACT
        $this->artisan('app:notify-users-of-upcoming-birthdays')
            ->assertExitCode(0);

        // ASSERT
        Notification::assertSentTo(
            $user,
            UpcomingBirthdayNotification::class
        );
    }

    public function test_it_does_not_notify_users_without_upcoming_birthdays(): void
    {
        // ARRANGE
        Notification::fake();

        // User with birthday far away
        User::factory()->create([
            'birthday_date' => now()->subYears(20)->addDays(30)->format('Y-m-d'),
        ]);

        // ACT
        $this->artisan('app:notify-users-of-upcoming-birthdays')
            ->assertExitCode(0);

        // ASSERT
        Notification::assertNothingSent();
    }

    public function test_it_does_not_notify_users_with_no_birthday_date(): void
    {
        // ARRANGE
        Notification::fake();

        // User with a null birthday
        $user = User::factory()->create([
            'birthday_date' => null,
        ]);

        // ACT
        $this->artisan('app:notify-users-of-upcoming-birthdays')
            ->assertExitCode(0);

        // ASSERT
        Notification::assertNotSentTo($user, UpcomingBirthdayNotification::class);
    }

    public function test_user_who_opted_out_does_not_receive_birthday_notification(): void
    {
        // ARRANGE
        Notification::fake();

        // Create a user who opted out
        $user = User::factory()->create([
            'birthday_date' => now()->subYears(20)->addDays(14)->format('Y-m-d'),
            'wants_birthday_notifications' => false,
        ]);

        // ACT
        $this->artisan('app:notify-users-of-upcoming-birthdays')
            ->assertExitCode(0);

        // ASSERT
        Notification::assertNotSentTo($user, UpcomingBirthdayNotification::class);
    }

    public function test_sends_notification_when_user_becomes_admin(): void
    {
        // ARRANGE
        Notification::fake();

        $adminUser = User::factory()->create(['is_admin' => true]);
        $regularUser = User::factory()->create([
            'name' => 'Regular User',
            'is_admin' => false,
            'is_active' => true,
        ]);

        // ACT
        $this->actingAs($adminUser)
            ->put(route('users.update', $regularUser), [
                'is_admin' => true, // Changed to admin
            ]);

        // ASSERT
        Notification::assertSentTo(
            $regularUser,
            UserMadeAdminNotification::class,
            function ($notification, $channels, $notifiable) use ($regularUser) {
                return $notifiable->id === $regularUser->id;
            }
        );
        // Ensure it's sent only once
        $this->assertEquals(1, Notification::sent($regularUser, UserMadeAdminNotification::class)->count());
    }


    public function test_notification_not_sent_if_user_is_already_admin(): void
    {
        //ARRANGE
        //SCENARIO: User was already admin, no notification should be sent
        Notification::fake();

        $adminUser = User::factory()->create(['is_admin' => true]);

        $alreadyAdminUser = User::factory()->create([
            'name' => 'Already Admin',
            'is_admin' => true,
            'is_active' => true,
        ]);

        //ACT
        $this->actingAs($adminUser)
            ->put(route('users.update', $alreadyAdminUser), [
                'name' => 'Already Admin Updated Name', // Changing name, not admin status
                'is_admin' => true, // Remains admin
            ]);

        //ASSERT
        Notification::assertNotSentTo($alreadyAdminUser, UserMadeAdminNotification::class);
    }

    public function test_notification_not_sent_when_user_is_demoted_from_admin_privileges(): void
    {
        //ARRANGE
        //SCENARIO: User is demoted from admin, no notification should be sent
        Notification::fake();

        $adminUser = User::factory()->create(['is_admin' => true]);

        $demotedAdminUser = User::factory()->create([
            'is_admin' => true, // Starts as admin
            'is_active' => true,
        ]);

        // ACT
        $this->actingAs($adminUser)
            ->put(route('users.update', $demotedAdminUser), [
                'is_admin' => false, // Changed to not admin
            ]);

        //ASSERT
        Notification::assertNotSentTo($demotedAdminUser, UserMadeAdminNotification::class);
    }
}
