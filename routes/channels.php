<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('wishlistItem', function ($user) {
    return Auth::check();
});
