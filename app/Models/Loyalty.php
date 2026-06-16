<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Loyalty
{
    private PDO $db;

    // Configuration des paliers (tiers)
    public static array $tiers = [
        [
            'name' => 'Démarreur',
            'min_spent' => 0.0,
            'rate' => 0.003, // 0.3%
            'pts_per_100' => 30
        ],
        [
            'name' => 'Régulier',
            'min_spent' => 100.0,
            'rate' => 0.004, // 0.4%
            'pts_per_100' => 40
        ],
        [
            'name' => 'Avancé',
            'min_spent' => 500.0,
            'rate' => 0.006, // 0.6%
            'pts_per_100' => 60
        ],
        [
            'name' => 'VIP',
            'min_spent' => 2500.0,
            'rate' => 0.008, // 0.8%
            'pts_per_100' => 80
        ],
        [
            'name' => 'Maître',
            'min_spent' => 10000.0,
            'rate' => 0.010, // 1.0%
            'pts_per_100' => 100
        ],
        [
            'name' => 'Empire',
            'min_spent' => 50000.0,
            'rate' => 0.012, // 1.2%
            'pts_per_100' => 120
        ]
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Calcule le total dépensé par l'utilisateur sur des commandes terminées
     */
    public function getUserTotalSpent(int $userId): float
    {
        $stmt = $this->db->prepare("SELECT SUM(cost) FROM orders WHERE user_id = ? AND status = 'Completed'");
        $stmt->execute([$userId]);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Récupère le palier actuel en fonction du total dépensé
     */
    public function getUserTier(int $userId): array
    {
        $spent = $this->getUserTotalSpent($userId);
        return self::getTierForSpent($spent);
    }

    public static function getTierForSpent(float $spent): array
    {
        $currentTier = self::$tiers[0];
        foreach (self::$tiers as $tier) {
            if ($spent >= $tier['min_spent']) {
                $currentTier = $tier;
            }
        }
        return $currentTier;
    }

    public static function getNextTierForSpent(float $spent): ?array
    {
        foreach (self::$tiers as $tier) {
            if ($tier['min_spent'] > $spent) {
                return $tier;
            }
        }
        return null;
    }

    /**
     * Ajoute des points de cashback lors du passage d'une commande
     */
    public function addPointsForOrder(int $userId, int $orderId, float $amountUsd): int
    {
        $tier = $this->getUserTier($userId);
        $points = (int)round($amountUsd * $tier['rate'] * 100);

        if ($points <= 0) {
            return 0;
        }

        // Mettre à jour l'utilisateur
        $stmt = $this->db->prepare("UPDATE users SET loyalty_points = loyalty_points + ?, lifetime_points = lifetime_points + ? WHERE id = ?");
        $stmt->execute([$points, $points, $userId]);

        // Enregistrer le log
        $stmtLog = $this->db->prepare("INSERT INTO loyalty_logs (user_id, points, description) VALUES (?, ?, ?)");
        $stmtLog->execute([$userId, $points, "Points accumulés pour la commande #" . str_pad((string)$orderId, 5, '0', STR_PAD_LEFT)]);

        return $points;
    }

    /**
     * Convertit les points cumulés de l'utilisateur en solde (100 points = 1.00 USD)
     * Seuil minimum de 500 points (5.00 USD)
     */
    public function redeemPoints(int $userId): bool
    {
        // Récupérer le solde de points actuel
        $stmt = $this->db->prepare("SELECT loyalty_points FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $points = (int)$stmt->fetchColumn();

        if ($points < 500) {
            return false;
        }

        $amountUsd = $points / 100.0;

        $this->db->beginTransaction();
        try {
            // Déduire les points et créditer le portefeuille
            $stmtUpdate = $this->db->prepare("UPDATE users SET loyalty_points = 0, balance = balance + ? WHERE id = ?");
            $stmtUpdate->execute([$amountUsd, $userId]);

            // Enregistrer le log négatif
            $stmtLog = $this->db->prepare("INSERT INTO loyalty_logs (user_id, points, description) VALUES (?, ?, ?)");
            $stmtLog->execute([$userId, -$points, "Conversion de " . $points . " points en crédit solde (+" . number_format($amountUsd, 2) . " \$USD)"]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Récupère l'historique des points d'un utilisateur
     */
    public function getLogsByUserId(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare("SELECT * FROM loyalty_logs WHERE user_id = ? ORDER BY id DESC LIMIT ?");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
