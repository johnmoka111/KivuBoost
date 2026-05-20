<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// RG-Rôles 3.1 : Protection d'Écran par Session
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Accès refusé. Zone réservée à l\'administrateur.'];
    header('Location: /KivuBoost/dashboard');
    exit;
}

require_once __DIR__ . '/config/config.php';

// Rôle admin : AUTORISÉ
header('Location: ' . APP_BASE . '/admin/settings');
exit;
