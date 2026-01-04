<?php
/**
 * TRANSLATION HELPER
 * Simple and fast multi-language system using PHP arrays
 */

class Translator {
    private static $translations = [];
    private static $currentLang = 'id';
    private static $fallbackLang = 'id';
    
    /**
     * Initialize translator with specified language
     * @param string $lang Language code ('id' or 'en')
     */
    public static function init($lang = 'id') {
        self::$currentLang = $lang;
        $langFile = __DIR__ . "/../lang/{$lang}.php";
        
        if (file_exists($langFile)) {
            self::$translations = require $langFile;
        } else {
            // Fallback to Indonesian if language file not found
            $fallbackFile = __DIR__ . "/../lang/" . self::$fallbackLang . ".php";
            if (file_exists($fallbackFile)) {
                self::$translations = require $fallbackFile;
            }
        }
    }
    
    /**
     * Get translation for a key
     * @param string $key Translation key
     * @param string|null $default Default value if key not found
     * @return string Translated text
     */
    public static function get($key, $default = null) {
        return self::$translations[$key] ?? $default ?? $key;
    }
    
    /**
     * Get current language code
     * @return string Current language code
     */
    public static function getCurrentLang() {
        return self::$currentLang;
    }
    
    /**
     * Check if a translation key exists
     * @param string $key Translation key
     * @return bool True if key exists
     */
    public static function has($key) {
        return isset(self::$translations[$key]);
    }
}

/**
 * Shorthand function for translation
 * @param string $key Translation key
 * @param string|null $default Default value if key not found
 * @return string Translated text
 */
function t($key, $default = null) {
    return Translator::get($key, $default);
}

/**
 * Shorthand function to get current language
 * @return string Current language code
 */
function currentLang() {
    return Translator::getCurrentLang();
}
