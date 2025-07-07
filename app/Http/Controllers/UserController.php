<?php

namespace App\Http\Controllers;

use App\Enums\LocaleEnum;
use App\Http\Requests\User\UserDeleteRequest;
use App\Http\Requests\User\UserPasswordUpdateRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\User;
use App\Notifications\SendAccountStateChangedNotification;
use App\Notifications\UserMadeAdminNotification;
use App\Notifications\SendNewAccountNotification;
use App\Notifications\SendNewAccountSetupPasswordNotification;
use App\Utils\StringUtils;
use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('User/Index');
    }

    public function show(User $user)
    {
        return Inertia::render('User/Show', [
            'user' => $user
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('User/Create', [
            'options' => LocaleEnum::getAvailableLocales()->map(function($locale, $key){
                return ['value'=>$key, 'label'=>$locale];
            }),
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('User/Edit', [
            'user' => $user,
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'options' => LocaleEnum::getAvailableLocales()->map(function($locale, $key){
                return ['value'=>$key, 'label'=>$locale];
            }),
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
            ...$request->validated(),
            'password' => Hash::make($password),
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

        if ($user->isDirty('is_active')) {
            $user->notify(new SendAccountStateChangedNotification());
        }

        if ($user->isDirty('is_admin') && $user->is_admin) {
            $user->notify(new UserMadeAdminNotification());
        }

        $user->save();

        return Redirect::route('users.edit', $user);
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

    public function getCurrentDataFromPage(Request $request): array
    {
        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'asc');
        $nameFilter = $request->get('name');
        $emailFilter = $request->get('email');
        $isAdminFilter = $request->get('is_admin');
        $isActiveFilter = $request->get('is_active');

        return [
            'pagination' => User::query()
                ->when($nameFilter, function ($query, $name) {
                    return $query->where('name', 'like', "%{$name}%");
                })
                ->when($emailFilter, function ($query, $email) {
                    return $query->where('email', 'like', "%{$email}%");
                })
                ->when($isAdminFilter !== null && $isAdminFilter !== '', function ($query) use ($isAdminFilter) {
                    return $query->where('is_admin', $isAdminFilter === '1');
                })
                ->when($isActiveFilter !== null && $isActiveFilter !== '', function ($query) use ($isActiveFilter) {
                    return $query->where('is_active', $isActiveFilter === '1');
                })
                ->orderBy($sortBy, $sortDirection)
                ->paginate($request->perPage, ['*'], 'page', $request->page),
        ];
    }
}
