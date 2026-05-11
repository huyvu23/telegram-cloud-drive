<?php

namespace App\Helpers;

class TranslateHelper
{
    private static $locale = 'vi';
    
    public static function setLocale($locale)
    {
        self::$locale = $locale;
    }
    
    public static function getLocale()
    {
        return self::$locale;
    }
    
    public static function t($key, $locale = null)
    {
        $locale = $locale ?? self::$locale;
        $locale = in_array($locale, ['vi', 'en']) ? $locale : 'vi';
        
        $messages = include resource_path("lang/{$locale}/messages.php");
        
        return $messages[$key] ?? $key;
    }
    
    public static function getAvailableLocales()
    {
        return ['vi' => 'Tiếng Việt', 'en' => 'English'];
    }
}
