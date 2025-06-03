<?php

namespace App\Console\Commands;

use App\Models\GroupInvitation;
use Illuminate\Console\Command;

class CleanUpOldUnacceptedInvitations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-up-old-unaccepted-group-invitations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old unaccepted group invitations.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        GroupInvitation::query()->where('expires_at', '<', now())->where('accepted_at', null)->delete();

        $this->info("Old group invitations (not accepted) have been cleared.");
    }
}
