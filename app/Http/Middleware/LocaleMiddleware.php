<?php

namespace App\Http\Middleware;

use App\Enums\LocaleEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class LocaleMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        $user = Auth::user();

        if (session()->has('locale') && in_array(session()->get('locale'), LocaleEnum::getAvailableLocales()->keys())) {
            $locale = session()->get('locale');
        } elseif ($user) {
            $locale = $user->preferred_locale;
        } else {
            $locale = App::getLocale();
        }
        App::setLocale(strtolower($locale));

        return $next($request);
    }
}
