<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wishlist\WishlistDeleteRequest;
use App\Http\Requests\Wishlist\WishlistStoreRequest;
use App\Http\Requests\Wishlist\WishlistUpdateRequest;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Wishlist/Index', [
            'wishlists' => Wishlist::query()
                ->where('user_id', Auth::user()->id)
                ->orWhere('is_shared', true)->get()
                ->load('user')//todo do not show from deactivated users
            ->map(fn (Wishlist $wishlist) => $wishlist->toDisplayData())
        ]);
    }

    public function show(Wishlist $wishlist)
    {
        return Inertia::render('Wishlist/Show', [
            'wishlist' => $wishlist->toDisplayData(),
            'wishlistItems' => $wishlist->wishlistItems,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Wishlist/Create');
    }

    public function edit(Wishlist $wishlist)
    {
        return Inertia::render('Wishlist/Edit', [
            'wishlist' => $wishlist->toDisplayData(),
            'wishlistItems' => $wishlist->wishlistItems()->orderBy('priority')->get(),
        ]);
    }

    public function store(WishlistStoreRequest $request): RedirectResponse
    {
        Wishlist::create([
            'name' => $request->name,
            'expiration_date' => $request->expiration_date,
            'user_id' => Auth::id()
        ]);

//        event(new WishlistShared($wishlist));todo send notif to ppl in the same groups

        return redirect('/wishlists');
    }

    public function update(WishlistUpdateRequest $request, Wishlist $wishlist): RedirectResponse
    {
        $wishlist->update([
            'name' => $request->name,
            'expiration_date' => $request->expiration_date,
            'is_shared' => $request->is_shared
        ]);

        return Redirect::route('wishlists.index');
    }

    public function destroy(WishlistDeleteRequest $request, Wishlist $wishlist): RedirectResponse
    {
        $wishlist->delete();

        return Redirect::route('wishlists.index');
    }
}
