<?php
declare(strict_types=1);

namespace App\Core;

class Audit
{
    public static function log(string $action, ?string $details = null): void
    {
        try {
            $db = Database::getInstance();
            
            // Assurer que la session est démarrée
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $userId   = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
            $username = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : null;
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, username, action, details, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $username, $action, $details, $ip, $ua]);
        } catch (\Throwable $e) {
            error_log("Erreur lors de l'enregistrement de l'audit : " . $e->getMessage());
        }
    }

    public static function getAll(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 500");
        return $stmt->fetchAll();
    }
}
