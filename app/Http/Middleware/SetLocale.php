<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['cs', 'en'];

    public function handle(Request $request, Closure $next): mixed
    {
        $locale = Session::get('locale');
        if (in_array($locale, self::SUPPORTED_LOCALES, true)) {
            App::setLocale($locale);
        }
        return $next($request);
    }
}
