<?php

namespace App\Events;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMemberJoined
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Group $group,
        public User $newMember
    ) {}
}
