<?php

namespace GroupInvitation;

use App\Models\GroupInvitation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CleanUpOldUnacceptedInvitationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_only_unaccepted_expired_group_invitations()
    {
        // ARRANGE
        // One unaccepted expired invitation
        GroupInvitation::factory()->create([
            'email' => 'expired@example.com',
            'accepted_at' => null,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        // One valid unaccepted invitation
        $validInvitation = GroupInvitation::factory()->create([
            'email' => 'valid@example.com',
            'accepted_at' => null,
            'expires_at' => Carbon::now()->addDay(),
        ]);

        // ACT
        Artisan::call('app:clean-up-old-unaccepted-group-invitations');

        // ASSERT: only valid invitation remains
        $this->assertCount(1, GroupInvitation::all());
        $this->assertNotNull(GroupInvitation::find($validInvitation->id));
    }

}
