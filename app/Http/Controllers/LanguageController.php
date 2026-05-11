<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\TranslateHelper;

class LanguageController extends Controller
{
    public function switchLocale(Request $request, $locale)
    {
        // Validate locale
        if (!in_array($locale, ['vi', 'en'])) {
            $locale = 'vi';
        }
        
        // Store in session
        $request->session()->put('locale', $locale);
        TranslateHelper::setLocale($locale);
        
        // Get intended URL or default to dashboard
        $intendedUrl = session()->get('url_intended', '/drive');
        
        // Redirect back
        return redirect()->intended($intendedUrl);
    }
}