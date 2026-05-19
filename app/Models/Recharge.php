<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Recharge
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $userId, float $amount, string $network, string $transactionId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO recharges (user_id, amount, network, transaction_id, status)
             VALUES (?, ?, ?, ?, "Pending")'
        );
        $stmt->execute([$userId, $amount, $network, $transactionId]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.username, u.email
             FROM recharges r
             LEFT JOIN users u ON u.id = r.user_id
             WHERE r.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getPending(): array
    {
        return $this->db->query(
            'SELECT r.*, u.username, u.email
             FROM recharges r
             LEFT JOIN users u ON u.id = r.user_id
             WHERE r.status = "Pending"
             ORDER BY r.created_at ASC'
        )->fetchAll();
    }

    public function getAll(int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.username
             FROM recharges r
             LEFT JOIN users u ON u.id = r.user_id
             ORDER BY r.created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM recharges WHERE user_id = ? ORDER BY created_at DESC LIMIT 20'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status, ?string $notes = null): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE recharges SET status = ?, notes = ? WHERE id = ?'
        );
        $stmt->execute([$status, $notes, $id]);
        return $stmt->rowCount() > 0;
    }

    public function totalApproved(): float
    {
        return (float)$this->db->query(
            'SELECT COALESCE(SUM(amount),0) FROM recharges WHERE status = "Approved"'
        )->fetchColumn();
    }

    public function countPending(): int
    {
        return (int)$this->db->query(
            'SELECT COUNT(*) FROM recharges WHERE status = "Pending"'
        )->fetchColumn();
    }
}
