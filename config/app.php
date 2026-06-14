<?php
/**
 * Application Configuration
 * Loads environment variables and defines application constants.
 */

// Load .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Application URL
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8000');
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));

// Supabase Configuration - supports both naming conventions
$supabaseUrl = getenv('SUPABASE_URL') ?: getenv('VITE_SUPABASE_URL') ?: '';
$supabaseKey = getenv('SUPABASE_KEY') ?: getenv('VITE_SUPABASE_PUBLISHABLE_KEY') ?: '';

define('SUPABASE_URL', $supabaseUrl);
define('SUPABASE_KEY', $supabaseKey);

// Application Settings
define('APP_NAME', 'ShopMart');
define('CURRENCY', 'INR');
define('CURRENCY_SYMBOL', '₹');

// Pagination
define('PRODUCTS_PER_PAGE', 20);

// Session config
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
}
