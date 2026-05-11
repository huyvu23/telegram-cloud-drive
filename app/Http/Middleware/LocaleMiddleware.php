<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\TranslateHelper;

class LocaleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check URL parameter first
        $locale = $request->get('lang');
        
        // Then check session
        if (!$locale) {
            $locale = $request->session()->get('locale');
        }
        
        // Default to Vietnamese
        if (!$locale || !in_array($locale, ['vi', 'en'])) {
            $locale = 'vi';
        }
        
        TranslateHelper::setLocale($locale);
        
        // Store in session
        $request->session()->put('locale', $locale);
        
        // Share with views
        view()->share('currentLocale', $locale);
        view()->share('availableLocales', TranslateHelper::getAvailableLocales());
        
        return $next($request);
    }
}
