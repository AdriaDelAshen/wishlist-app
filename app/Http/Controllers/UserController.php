<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserDeleteRequest;
use App\Http\Requests\User\UserPasswordUpdateRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\User;
use App\Notifications\SendNewAccountNotification;
use App\Notifications\SendNewAccountSetupPasswordNotification;
use App\Utils\StringUtils;
use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('User/Index', [
            'users' => User::all()
        ]);
    }

    public function show(User $user)
    {
        return Inertia::render('User/Show', [
            'user' => $user
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('User/Create');
    }

    public function edit(User $user)
    {
        return Inertia::render('User/Edit', [
            'user' => $user,
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * @throws Exception
     */
    public function store(UserStoreRequest $request): RedirectResponse
    {
        $password = $request->password;
        if(!$password) {
            $password = StringUtils::randomString(8);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'is_active' => $request->is_active
        ]);
        if ($request->password) {
            $user->notify(new SendNewAccountNotification());
        } else {
            $user->notify(new SendNewAccountSetupPasswordNotification());
        }

        return redirect('/users');
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('users.index');
    }

    public function destroy(UserDeleteRequest $request, User $user): RedirectResponse
    {
        $user->delete();

        return Redirect::route('users.index');
    }

    public function updatePassword(UserPasswordUpdateRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return Redirect::route('users.index');
    }
}
