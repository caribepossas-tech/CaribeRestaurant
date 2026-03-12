<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        try {
            $user = auth()->user();
        } catch (\Exception $e) {

            return $next($request);
        }

        if (session()->has('locale')) {
            App::setLocale(session('locale'));
        } elseif (isset($user) && !is_null($user->locale)) {
            App::setLocale($user->locale);
        } else {
            try {
                App::setLocale(global_setting()?->locale ?? config('app.locale'));
            } catch (\Exception $e) {
                App::setLocale(config('app.locale'));
            }
        }

        if (is_null($user?->restaurant_id) && is_null($user?->branch_id)) {
            return $next($request);
        }

        if (!$user->isRestaurantApproved() && Route::currentRouteName() !== 'account_unverified') {
            return redirect()->route('account_unverified');
        }

        return $next($request);
    }
}
