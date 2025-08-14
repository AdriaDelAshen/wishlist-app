<?php

namespace App\Providers;

use App\Events\GroupMemberJoined;
use App\Events\GroupMemberLeft;
use App\Listeners\NotifyGroupMembersOfNewJoin;
use App\Listeners\NotifyGroupMembersOfNewLeave;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\Wishlist;
use App\Policies\GroupInvitationPolicy;
use App\Policies\GroupPolicy;
use App\Policies\WishlistPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Gate::policy(Wishlist::class, WishlistPolicy::class);
        Gate::policy(Group::class, GroupPolicy::class);
        Gate::policy(GroupInvitation::class, GroupInvitationPolicy::class);

        Event::listen(
            GroupMemberJoined::class,
            NotifyGroupMembersOfNewJoin::class,
        );
        Event::listen(
            GroupMemberLeft::class,
            NotifyGroupMembersOfNewLeave::class,
        );
    }
}
