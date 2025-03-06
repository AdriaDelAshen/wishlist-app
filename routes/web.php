<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\WishlistItemController;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/','/dashboard');

Route::get('/dashboard', function () {
     return Inertia::render('Dashboard', [
         'wishlistItems' => WishlistItem::query()
             ->where('user_id', Auth::user()->id)
             ->get()
             ->load('wishlist')
             ->load('wishlist.user')
     ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('/users', UserController::class);
    Route::patch('/users/{user}/password', [UserController::class, 'updatePassword'])->name('users.update_password');
    Route::resource('/users', UserController::class);
    Route::resource('/wishlists', WishlistController::class);
    Route::resource('/wishlist_items', WishlistItemController::class);
    Route::patch('/wishlist_link_item_user/{wishlist_item}', [WishlistItemController::class, 'linkItemToUser'])->name('wishlist_items.linkItemToUser');
    Route::patch('/wishlist_unlink_item_user/{wishlist_item}', [WishlistItemController::class, 'unlinkItemToUser'])->name('wishlist_items.unlinkItemToUser');
});

require __DIR__.'/auth.php';
