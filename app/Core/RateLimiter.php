<?php

namespace App\Core;

use App\Core\Database;
use PDO;

/**
 * RateLimiter — Limitation des tentatives de connexion par IP
 * 
 * Stocke les tentatives en base de données dans la table `login_attempts`.
 * Bloque après N tentatives sur une fenêtre de temps configurable.
 * Fonctionne sur les hébergements mutualisés sans Redis/Memcached.
 */
class RateLimiter
{
    // Nombre maximum de tentatives autorisées
    public const MAX_ATTEMPTS = 5;

    // Fenêtre de temps en minutes (ex: 15 = bloquer si 5 échecs en 15 min)
    private const WINDOW_MINUTES = 15;

    // Durée de blocage en minutes (après MAX_ATTEMPTS dépassé)
    private const LOCKOUT_MINUTES = 15;

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Vérifie si l'IP est actuellement bloquée
     * 
     * @param string $ip  Adresse IP du client
     * @return bool       true = bloqué, false = autorisé
     */
    public function isBlocked(string $ip): bool
    {
        $since = date('Y-m-d H:i:s', strtotime('-' . self::WINDOW_MINUTES . ' minutes'));

        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM login_attempts
                WHERE ip_address = ?
                  AND attempted_at >= ?
                  AND success = 0
            ");
            $stmt->execute([$ip, $since]);
            $count = (int)$stmt->fetchColumn();

            return $count >= self::MAX_ATTEMPTS;
        } catch (\Throwable $e) {
            // Si la table n'existe pas encore, on n'bloque pas
            return false;
        }
    }

    /**
     * Retourne le nombre de minutes restantes avant déblocage
     * 
     * @param string $ip
     * @return int  Minutes restantes (0 si pas bloqué)
     */
    public function getRemainingLockoutMinutes(string $ip): int
    {
        try {
            $since = date('Y-m-d H:i:s', strtotime('-' . self::WINDOW_MINUTES . ' minutes'));
            $stmt = $this->db->prepare("
                SELECT MIN(attempted_at) as first_attempt
                FROM login_attempts
                WHERE ip_address = ?
                  AND attempted_at >= ?
                  AND success = 0
            ");
            $stmt->execute([$ip, $since]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || !$row['first_attempt']) {
                return 0;
            }

            $unlockAt = strtotime($row['first_attempt']) + (self::LOCKOUT_MINUTES * 60);
            $remaining = max(0, (int)ceil(($unlockAt - time()) / 60));
            return $remaining;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Enregistre une tentative de connexion échouée
     * 
     * @param string $ip
     * @param string $email  Email utilisé lors de la tentative
     */
    public function recordFailedAttempt(string $ip, string $email = ''): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO login_attempts (ip_address, email, success, attempted_at)
                VALUES (?, ?, 0, NOW())
            ");
            $stmt->execute([$ip, $email]);
        } catch (\Throwable $e) {
            // Silencieux si table non migrée
        }
    }

    /**
     * Enregistre une connexion réussie et efface les échecs précédents
     * 
     * @param string $ip
     * @param string $email
     */
    public function recordSuccess(string $ip, string $email = ''): void
    {
        try {
            // Effacer les tentatives échouées de cette IP
            $stmt = $this->db->prepare("
                DELETE FROM login_attempts
                WHERE ip_address = ? AND success = 0
            ");
            $stmt->execute([$ip]);

            // Enregistrer la connexion réussie (pour audit)
            $stmt2 = $this->db->prepare("
                INSERT INTO login_attempts (ip_address, email, success, attempted_at)
                VALUES (?, ?, 1, NOW())
            ");
            $stmt2->execute([$ip, $email]);
        } catch (\Throwable $e) {
            // Silencieux si table non migrée
        }
    }

    /**
     * Nettoie les anciennes tentatives (à appeler périodiquement)
     * Supprime les entrées de plus de 24h
     */
    public function purgeOldAttempts(): void
    {
        try {
            $this->db->exec("
                DELETE FROM login_attempts
                WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
        } catch (\Throwable $e) {
            // Silencieux
        }
    }

    /**
     * Retourne l'IP réelle du client (gère les proxies/reverse proxy InfinityFree)
     */
    public static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',    // Cloudflare
            'HTTP_X_FORWARDED_FOR',     // Proxy/LoadBalancer
            'HTTP_X_REAL_IP',           // Nginx reverse proxy
            'REMOTE_ADDR',              // IP directe
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                // X-Forwarded-For peut contenir plusieurs IPs (ex: "client, proxy1, proxy2")
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
