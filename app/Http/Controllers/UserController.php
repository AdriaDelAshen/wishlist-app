<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
            'wishlist' => $user
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('User/Create');
    }

//    public function store(WishlistStoreRequest $request): RedirectResponse
//    {
//        Wishlist::create([
//            'name' => $request->name,
//            'expiration_date' => $request->expiration_date,
//            'user_id' => Auth::id()
//        ]);
//
////        event(new WishlistShared($wishlist));
//
//        return redirect('/wishlists');
//    }
//
//    public function edit(Wishlist $wishlist)
//    {
//        return Inertia::render('Wishlist/Edit', [
//            'wishlist' => $wishlist->toDisplayData(),
//            'wishlistItems' => $wishlist->wishlistItems()->orderBy('priority')->get(),
//        ]);
//    }
//
//    public function update(WishlistUpdateRequest $request, Wishlist $wishlist): RedirectResponse
//    {
//        $wishlist->update([
//            'name' => $request->name,
//            'expiration_date' => $request->expiration_date,
//            'is_shared' => $request->is_shared
//        ]);
//
//        return Redirect::route('wishlists.index');
//    }
//
//    public function destroy(WishlistDeleteRequest $request, Wishlist $wishlist): RedirectResponse
//    {
//        $wishlist->delete();
//
//        return Redirect::route('wishlists.index');
//    }
}
