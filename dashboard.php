<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/config.php';

use App\Core\Auth;

// Vérifier la session
if (!Auth::isLoggedIn()) {
    header('Location: ' . APP_BASE . '/login');
    exit;
}

// Rôle client : AUTORISÉ, Rôle admin : AUTORISÉ
header('Location: ' . APP_BASE . '/dashboard');
exit;
