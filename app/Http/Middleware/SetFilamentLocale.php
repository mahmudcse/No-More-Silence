<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetFilamentLocale
{
    /**
     * Handle an incoming request for the Filament admin panel.
     * Defaults to English ('en') unless explicitly switched in session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('filament_locale', 'en');

        if (! in_array($locale, ['en', 'bn'], true)) {
            $locale = 'en';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
