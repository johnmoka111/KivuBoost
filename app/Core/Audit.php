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
            
            $currentUser = \App\Core\Auth::user();
            
            $userId   = $currentUser ? (int)$currentUser['id'] : null;
            $username = $currentUser ? $currentUser['username'] : null;
            
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
        $stmt = $db->query("
            SELECT a.id, a.user_id, a.action, a.details, a.ip_address, a.user_agent, a.created_at, 
                   COALESCE(u.username, a.username) AS username
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC 
            LIMIT 500
        ");
        return $stmt->fetchAll();
    }
}
