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

// Rôle admin : Bloqué (Redirection)
if (Auth::isAdmin()) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Accès interdit aux administrateurs sur cette page.'];
    header('Location: ' . APP_BASE . '/admin');
    exit;
}

// Rôle client : AUTORISÉ
header('Location: ' . APP_BASE . '/recharge');
exit;
