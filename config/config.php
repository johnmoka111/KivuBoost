<?php
// ============================================================
// KivuBoost — Configuration Centrale
// ============================================================

// --- Base de données & Environnement ---
if (PHP_SAPI === 'cli' || (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1'))) {
    // --- Configuration Locale (Dev) ---
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'bukavuboost');
    define('DB_USER', 'root');
    define('DB_PASS', '');

    define('APP_BASE',    '/KivuBoost');        // Chemin de base local
    define('APP_URL',     'http://localhost/KivuBoost');
} else {
    // --- Configuration Production (InfinityFree) ---
    define('DB_HOST', 'sql101.infinityfree.com');
    define('DB_NAME', 'if0_42103229_kivubooster');
    define('DB_USER', 'if0_42103229');
    define('DB_PASS', 'kivubooster');

    define('APP_BASE',    '');        // Racine du domaine sur InfinityFree (chaîne vide pour éviter les doubles slashes // dans les chemins)
    define('APP_URL',     'https://kivubooster.kesug.com');
}

define('APP_NAME',    'KivuBoost');

// --- Google OAuth2 ---
// Pour la sécurité (GitHub), les clés sont stockées dans le fichier config/secrets.php
// Ce fichier n'est pas envoyé sur GitHub. Créez-le sur votre ordinateur et sur FileZilla.
if (file_exists(__DIR__ . '/secrets.php')) {
    require_once __DIR__ . '/secrets.php';
} else {
    define('GOOGLE_CLIENT_ID',     ''); // Remplacez dans secrets.php
    define('GOOGLE_CLIENT_SECRET', ''); // Remplacez dans secrets.php
}
// URI de callback — DOIT correspondre exactement à ce qui est enregistré dans Google Cloud Console
define('GOOGLE_REDIRECT_URI',  APP_URL . '/auth/google/callback');

// --- fuseau horaire local (Bukavu/Lubumbashi - UTC+2) ---
date_default_timezone_set('Africa/Lubumbashi');

// --- Sécurité ---
define('CSRF_SECRET', 'b0k@vuB00st_C5RF_53cr3t_2024!');

// --- Clé de test/placeholder pour les fournisseurs non encore configurés ---
// Un fournisseur dont l'api_key est égale à cette valeur ne sera PAS contacté via l'API.
define('SMM_PLACEHOLDER_KEY', 'CLE_SECRETE_SMM_FOLLOWS');

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

// --- Chargement du système de messagerie SMTP (PHPMailer) ---
require_once ROOT_PATH . '/config/mailer_config.php';
