<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\WishlistItemController;
use App\Http\Middleware\ActiveUserRequests;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\OnlyForAdminRequests;
use Illuminate\Support\Facades\Route;

Route::redirect('/','/dashboard');

Route::get('/dashboard', [DashboardController::class, 'show'])
    ->middleware(['auth', 'verified', ActiveUserRequests::class, LocaleMiddleware::class])
    ->name('dashboard');

Route::middleware(['auth', ActiveUserRequests::class, LocaleMiddleware::class])->group(function () {
    //Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Wishlists
    Route::resource('/wishlists', WishlistController::class);
    Route::post('/wishlists/{wishlist}/duplicate', [WishlistController::class, 'duplicate'])->name('wishlists.duplicate');

    //Wishlist items
    Route::patch('/wishlist_item_link_item_user/{wishlist_item}', [WishlistItemController::class, 'linkItemToUser'])->name('wishlist_items.link_item_to_user');
    Route::patch('/wishlist_item_unlink_item_user/{wishlist_item}', [WishlistItemController::class, 'unlinkItemToUser'])->name('wishlist_items.unlink_item_to_user');
    Route::patch('/wishlist_item_state_has_changed/{wishlist_item}', [WishlistItemController::class, 'itemStateHasChanged'])->name('wishlist_items.state_has_changed');
    Route::resource('/wishlist_items', WishlistItemController::class);

    //Users
    Route::middleware(OnlyForAdminRequests::class)->group(function () {
        Route::patch('/users/{user}/password', [UserController::class, 'updatePassword'])->name('users.update_password');
        Route::resource('/users', UserController::class);
    });
});

//Route for paging (excluding LocaleMiddleware)
Route::get('/current_dashboard_wishlist_items', [DashboardController::class, 'getCurrentDataFromPage'])
    ->name('dashboard.get_current_data_page');

Route::middleware(['auth', ActiveUserRequests::class])
    ->get('/current_wishlists', [WishlistController::class, 'getCurrentDataFromPage'])
    ->name('wishlists.get_current_data_page');

Route::middleware(['auth', ActiveUserRequests::class])
    ->get('/current_wishlist_items', [WishlistItemController::class, 'getCurrentDataFromPage'])
    ->name('wishlist_items.get_current_data_page');

Route::middleware(['auth', ActiveUserRequests::class, OnlyForAdminRequests::class])
    ->get('/current_users', [UserController::class, 'getCurrentDataFromPage'])
    ->name('users.get_current_data_page');

require __DIR__.'/auth.php';
