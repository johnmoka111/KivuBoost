<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Order
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $userId, int $serviceId, string $link, int $quantity, float $cost): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO orders (user_id, service_id, link, quantity, cost, status)
             VALUES (?, ?, ?, ?, ?, "Pending")'
        );
        $stmt->execute([$userId, $serviceId, $link, $quantity, $cost]);
        return (int)$this->db->lastInsertId();
    }

    public function updateExternalId(int $orderId, string $externalId, string $status = 'Processing'): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE orders SET external_order_id = ?, status = ? WHERE id = ?'
        );
        $stmt->execute([$externalId, $status, $orderId]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
        return $stmt->rowCount() > 0;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, s.name AS service_name, s.category
             FROM orders o
             LEFT JOIN services s ON s.id = o.service_id
             WHERE o.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getByUser(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, s.name AS service_name, s.category
             FROM orders o
             LEFT JOIN services s ON s.id = o.service_id
             WHERE o.user_id = ?
             ORDER BY o.created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public function getAll(int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, s.name AS service_name, u.username
             FROM orders o
             LEFT JOIN services s ON s.id = o.service_id
             LEFT JOIN users u ON u.id = o.user_id
             ORDER BY o.created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function totalRevenue(): float
    {
        return (float)$this->db->query(
            'SELECT COALESCE(SUM(cost),0) FROM orders WHERE status != "Canceled"'
        )->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM orders WHERE status = ?');
        $stmt->execute([$status]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Enregistre la reponse brute d'erreur API du grossiste sur la commande.
     */
    public function logApiError(int $orderId, string $errorPayload): void
    {
        $stmt = $this->db->prepare('UPDATE orders SET api_error_log = ? WHERE id = ?');
        $stmt->execute([mb_substr($errorPayload, 0, 65535), $orderId]);
    }

    /**
     * Rapport financier mensuel — chiffre d'affaires vs cout d'achat grossiste.
     * Retourne les 12 derniers mois.
     */
    public function getMonthlyFinancialReport(int $months = 12): array
    {
        $stmt = $this->db->prepare("
            SELECT
                DATE_FORMAT(o.created_at, '%Y-%m') AS month_key,
                DATE_FORMAT(o.created_at, '%b %Y')  AS month_label,
                COUNT(o.id)                          AS total_orders,
                ROUND(SUM(o.cost), 4)                AS total_revenue,
                ROUND(SUM(
                    (o.quantity / 1000.0) * COALESCE(s.original_rate, 0)
                ), 4)                                AS total_cost,
                ROUND(SUM(o.cost) - SUM(
                    (o.quantity / 1000.0) * COALESCE(s.original_rate, 0)
                ), 4)                                AS net_profit
            FROM orders o
            LEFT JOIN services s ON s.id = o.service_id
            WHERE o.status IN ('Completed', 'Partial', 'Processing')
              AND o.created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY month_key, month_label
            ORDER BY month_key ASC
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }
}
