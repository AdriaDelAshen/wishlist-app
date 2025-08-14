<?php

namespace App\Listeners;

use App\Events\GroupMemberJoined;
use App\Notifications\NewGroupMemberNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyGroupMembersOfNewJoin implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(GroupMemberJoined $event)
    {
        $group = $event->group;
        $newMember = $event->newMember;

        $group->members()
            ->where('user_id', '!=', $newMember->id)
            ->each(fn($member) => $member->user->notify(new NewGroupMemberNotification($group, $newMember)));
    }
}
