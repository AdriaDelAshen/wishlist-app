<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GroupInvitation;
use App\Models\User;
use App\Notifications\SendRegistrationNeedsApprovalNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        $email = null;

        if (session()->has('group_invite_token')) {
            $token = session('group_invite_token');
            $invitation = GroupInvitation::query()->where('token', $token)->first();

            if ($invitation) {
                $email = $invitation->email;
            }
        }

        return Inertia::render('Auth/Register', ['email' => $email]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $token = session('group_invite_token');
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'preferred_locale' => ['required', 'string', 'max:2'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'preferred_locale' => $request->preferred_locale,
            'is_active' => true
        ]);

        if($user->is_active) {
            event(new Registered($user));
            Auth::login($user);
            if ($token) {
                $invitation = GroupInvitation::QUERY()->where('token', $token)->first();
                if ($invitation && !$invitation->isAccepted()) {
                    $invitation->update(['accepted_at' => now()]);
                    $invitation->group->users()->attach($user->id, ['role' => 'member']);
                    return redirect(route('groups.show', ['group' => $invitation->group->load('user')]));
                }
            }

            return redirect(route('dashboard', absolute: false));
        } else {
            //todo remove or keep as possible usable feature?
            $user->notify(new SendRegistrationNeedsApprovalNotification());
        }
        return redirect(route('login'))->with(['email' => __('messages.new_account_must_be_approved')]);
    }
}
