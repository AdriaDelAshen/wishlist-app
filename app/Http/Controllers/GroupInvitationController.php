<?php

namespace App\Http\Controllers;

use App\Mail\PendingGroupInvitationHasBeenRemovedMail;
use App\Models\Group;
use App\Models\GroupInvitation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class GroupInvitationController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(GroupInvitation::class, 'groupInvitation');
    }

    public function index(): Response
    {
        return Inertia::render('GroupInvitations/Index', [
            'group_invitations' => GroupInvitation::query()
                ->where(function ($query) {
                    $query->whereHas('group', function ($query) {
                        $query->where('user_id', Auth::id());
                    })->orWhere('email', Auth::user()->email);
                })->with('group')->get()
        ]);
    }

    public function destroy(GroupInvitation $groupInvitation): RedirectResponse
    {
        $groupInvitation->delete();

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

    public function removeInvitationFromGroup(Request $request, GroupInvitation $groupInvitation): RedirectResponse
    {
        Gate::authorize('delete', $groupInvitation);

        $groupInvitation->delete();
        Mail::to($groupInvitation->email)->send(new PendingGroupInvitationHasBeenRemovedMail($groupInvitation));

        return back();
    }
}
