<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupInvitationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\WishlistItemController;
use App\Http\Middleware\ActiveUserRequests;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\OnlyForAdminRequests;
use Illuminate\Support\Facades\Route;

Route::redirect('/','/dashboard');

//Group invitation for new users (unauthenticated)
Route::middleware(['guest', LocaleMiddleware::class])->group(function () {
    Route::get('/groups/invite/new/{token}', [GroupController::class, 'acceptNewInvite'])->name('groups.accept_invite.new');
});

Route::middleware(['auth', ActiveUserRequests::class])->group(function () {
    //Paging
    Route::get('/current_dashboard_wishlist_items', [DashboardController::class, 'getCurrentDataFromPage'])->name('dashboard.get_current_data_page');
    Route::get('/current_wishlists', [WishlistController::class, 'getCurrentDataFromPage'])->name('wishlists.get_current_data_page');
    Route::get('/current_wishlist_items', [WishlistItemController::class, 'getCurrentDataFromPage'])->name('wishlist_items.get_current_data_page');
    Route::get('/current_groups', [GroupController::class, 'getCurrentDataFromPage'])->name('groups.get_current_data_page');
    Route::get('/current_group_users', [GroupController::class, 'getCurrentUsersDataFromPage'])->name('groups.get_current_users_data_page');

    Route::middleware([OnlyForAdminRequests::class])->group(function () {
        Route::get('/current_users', [UserController::class, 'getCurrentDataFromPage'])->name('users.get_current_data_page');
    });

    //Localized content
    Route::middleware([LocaleMiddleware::class])->group(function () {
        //Dashboard
        Route::middleware(['verified'])->get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

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

        //Groups
        Route::resource('/groups', GroupController::class);
        Route::get('/group-utils/active_users', [GroupController::class, 'getActiveUsersNotInCurrentGroup'])->name('groups.get_active_users_not_in_current_group');
        Route::post('/groups/{group}/add_user_to_group', [GroupController::class, 'addUserToGroup'])->name('groups.add_user_to_group');
        Route::delete('/groups/{group}/remove_user_from_group', [GroupController::class, 'removeUserFromGroup'])->name('groups.remove_user_from_group');

        Route::post('/groups/{group}/invite', [GroupController::class, 'sendInvitations'])->name('groups.send_invitations');
        Route::get('/groups/invite/existing/{token}', [GroupController::class, 'acceptExistingInvite'])->name('groups.accept_invite.existing');
        Route::post('/groups/{group}/generate-link', [GroupController::class, 'generateLinkInvite'])->name('groups.generate_link');

        //Group invitations
        Route::resource('/group_invitations', GroupInvitationController::class);
        Route::delete('/group_invitations/{group_invitation}/remove_invitation_from_group', [GroupInvitationController::class, 'removeInvitationFromGroup'])->name('group_invitations.remove_invitation_from_group');
    });
});

require __DIR__.'/auth.php';
