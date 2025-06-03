<?php

namespace App\Http\Controllers;

use App\Http\Requests\Group\GroupStoreRequest;
use App\Http\Requests\Group\GroupUpdateRequest;
use App\Http\Requests\GroupInvitation\GroupInvitationStoreRequest;
use App\Mail\GroupInvitationMail;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Group::class, 'group');
    }

    public function index(): Response
    {
        return Inertia::render('Group/Index');
    }

    public function show(Group $group)
    {
        return Inertia::render('Group/Show', [
            'group' => $group->load('user'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Group/Create');
    }

    public function edit(Group $group)
    {
        Gate::authorize('update', $group);
        return Inertia::render('Group/Edit', [
            'group' => $group,
            'activeUsersNotInCurrentGroup' => User::query()
                ->where('is_active', true)
                ->whereDoesntHave('groups', function ($query) use ($group) {
                    $query->where('groups.id', $group->id);
                })
                ->get()
                ->mapWithKeys(fn($user) => [$user->id => $user->name . ' (' . $user->email.')'])
        ]);
    }

    public function store(GroupStoreRequest $request): RedirectResponse
    {
        $userId = Auth::user()->id;
        $group = Group::query()->create([
            ...$request->validated(),
            'user_id' => $userId
        ]);

        $group->users()->attach($userId, ['role' => 'owner']);

        return Redirect::route('groups.index');
    }

    public function update(GroupUpdateRequest $request, Group $group): RedirectResponse
    {
        $group->update($request->validated());

        return Redirect::route('groups.index');
    }

    public function destroy(Group $group): RedirectResponse
    {
        $group->delete();

        return Redirect::route('groups.index');
    }

    /*************
     * Functions *
     *************/

    public function getCurrentDataFromPage(Request $request): array
    {
        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'asc');

        return [
            'pagination' => Group::query()
                ->where(function ($query) {
                    $query->where('user_id', Auth::user()->id)
                        ->orWhere('is_private', false);
                })
                ->with('user')
                ->orderBy($sortBy, $sortDirection)
                ->paginate($request->perPage, ['*'], 'page', $request->page)
        ];
    }

    public function getCurrentUsersDataFromPage(Request $request): array
    {
        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'asc');
        $groupId = $request->group_id;
        $perPage = $request->get('perPage', 10);
        $page = $request->get('page', 1);

        // Fetch Users in group
        $users = User::query()
            ->whereHas('groups', function ($query) use ($groupId) {
                $query->where('groups.id', $groupId);
            })
            ->get()
            ->map(function ($user) {
                $user->type = 'user';
                $user->public_id = 'user-' . $user->id;
                return $user;
            });

        // Fetch pending, not expired invitations
        $invitations = GroupInvitation::query()
            ->where('group_id', $groupId)
            ->whereNull('accepted_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get()
            ->map(function ($invitation) {
                $invitation->name = __('group.invitation_pending');
                $invitation->type = 'invitation';
                $invitation->public_id = 'inv-' . $invitation->id;
                return $invitation;
            });

        // Merge and sort
        $merged = $users->merge($invitations)->sortBy([
            [$sortBy, $sortDirection === 'asc' ? SORT_ASC : SORT_DESC],
        ])->values();

        // Paginate manually
        $paginated = new LengthAwarePaginator(
            $merged->forPage($page, $perPage),
            $merged->count(),
            $perPage,
            $page,
            ['path' => url()->current()]
        );

        return [
            'pagination' => $paginated,
        ];
    }

    public function getActiveUsersNotInCurrentGroup(Request $request): array
    {
        $groupId = $request->group_id;

        return [
            'users' => User::query()
                ->where('is_active', true)
                ->whereDoesntHave('groups', function ($query) use ($groupId) {
                    $query->where('groups.id', $groupId);
                })
                ->get()
                ->mapWithKeys(fn($user) => [$user->id => $user->name . ' (' . $user->email.')'])
        ];
    }

    public function generateLinkInvite(Group $group): JsonResponse
    {
        Gate::authorize('update', $group);

        // Check if a non-expired invite already exists
        $existing = $group->invitations()
            ->where('type', 'link')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return response()->json(['url' => route('groups.accept_invite.new', $existing->token)]);
        }

        $token = Str::uuid()->toString();

        $group->invitations()->create([
            'email' => null, // since this is link-based
            'token' => $token,
            'type' => 'link',
            'expires_at' => now()->addMinutes(10),
        ]);

        $url = route('groups.accept_invite.new', $token);

        return response()->json(['url' => $url]);
    }

    public function sendInvitations(GroupInvitationStoreRequest $request, Group $group): RedirectResponse
    {
        Gate::authorize('update', $group);

        foreach ($request->emails as $email) {
            $group->invitations()->where('email', $email)->where('accepted_at', null)->delete();

            $token = Str::uuid();
            GroupInvitation::query()->create([
                'group_id' => $group->id,
                'email' => $email,
                'token' => $token,
                'type' => 'email',
                'expires_at' => now()->addHours(24),
            ]);

            Mail::to($email)->send(new GroupInvitationMail($group, $token, User::query()->where('email', $email)->exists()));
        }

        return back()->with('success', 'Invitations sent!');
    }

    public function acceptExistingInvite(string $token): RedirectResponse
    {
        $invitation = GroupInvitation::query()->where('token', $token)->first();

        if (!$invitation || $invitation->expires_at->isPast()) {
            abort(403, __('group.invitation_expired_or_invalid'));
        } else if (!$invitation->isAccepted()) {
            $invitation->update(['accepted_at' => now()]);

            $invitation->group->users()->syncWithoutDetaching([Auth::id() => ['role' => 'member']]);
        }

        return redirect()->route('groups.show', ['group' => $invitation->group->load('user')]);
    }

    public function acceptNewInvite(string $token): RedirectResponse
    {
        $invitation = GroupInvitation::query()->where('token', $token)->first();

        if (!$invitation || $invitation->expires_at->isPast()) {
            abort(403, __('group.invitation_expired_or_invalid'));
        }
        session(['group_invite_token' => $token]);
        return redirect()->route('register');
    }

    public function addUserToGroup(Request $request, Group $group): RedirectResponse
    {
//        Gate::authorize('update', $group); //TODO authorize only for admin
        $group->users()->attach($request->user_id, ['role' => 'member']);

        return back();
    }

    public function removeUserFromGroup(Request $request, Group $group): RedirectResponse
    {
        Gate::authorize('update', $group);
        $group->users()->detach($request->user_id);

        return back();
    }
}
