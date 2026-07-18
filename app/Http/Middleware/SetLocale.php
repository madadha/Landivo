<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale', 'ar'));
        $locale = in_array($locale, ['ar', 'en'], true) ? $locale : 'ar';
        app()->setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }
}
