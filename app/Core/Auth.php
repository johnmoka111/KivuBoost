<?php

namespace App\Core;

use App\Models\User;

/**
 * Auth — Helper de gestion de session et autorisations
 */
class Auth
{
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function isAdmin(): bool
    {
        return self::isLoggedIn()
            && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    public static function isSuperAdmin(): bool
    {
        return self::isAdmin();
    }

    public static function user(): ?array
    {
        if (!self::isLoggedIn()) return null;

        // Cache en session pour éviter trop de requêtes DB
        if (isset($_SESSION['user_cache'])) {
            return $_SESSION['user_cache'];
        }

        $userModel = new User();
        $user = $userModel->findById((int)$_SESSION['user_id']);

        if ($user) {
            $_SESSION['user_cache'] = $user;
        }

        return $user ?: null;
    }

    /**
     * Rafraîchir le cache utilisateur (après modification du solde, etc.)
     */
    public static function refreshUser(): void
    {
        unset($_SESSION['user_cache']);
    }

    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . APP_BASE . '/login');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: ' . APP_BASE . '/dashboard');
            exit;
        }
    }

    public static function requireSuperAdmin(): void
    {
        self::requireLogin();
        if (!self::isSuperAdmin()) {
            header('Location: ' . APP_BASE . '/admin');
            exit;
        }
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['role']       = $user['role']; // Pour compatibilité stricte vanilla PHP
        $_SESSION['user_cache'] = $user;
    }

    public static function logout(): void
    {
        session_unset();
        session_destroy();
    }

    // --- CSRF ---

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(): bool
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::csrfToken() . '">';
    }
}
