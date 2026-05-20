<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// RG-Rôles 3.1 : Protection d'Écran par Session
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Accès refusé. Zone réservée à l\'administrateur.'
    ];
    header('Location: ./dashboard');
    exit;
}

require_once __DIR__ . '/config/config.php';
// Rediriger vers la route propre de régie
header('Location: ./admin/audit');
exit;
