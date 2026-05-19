<?php
// ============================================================
// BukavuBoost — Configuration Centrale
// ============================================================

// --- Base de données ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'bukavuboost');
define('DB_USER', 'root');
define('DB_PASS', '');

// --- Application ---
define('APP_NAME',    'BukavuBoost');
define('APP_BASE',    '/KivuBoost');        // Chemin de base dans l'URL
define('APP_URL',     'http://localhost/KivuBoost');

// --- Sécurité ---
define('CSRF_SECRET', 'b0k@vuB00st_C5RF_53cr3t_2024!');

// --- Chemins absolus ---
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('VIEW_PATH', APP_PATH  . '/Views');

// --- Autoload PSR-4 simplifié ---
spl_autoload_register(function (string $class): void {
    // Namespace: App\Controllers\AuthController → app/Controllers/AuthController.php
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file = APP_PATH . '/' . $relative . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
