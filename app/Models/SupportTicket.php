<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SupportTicket
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        return $this->db
            ->query('SELECT t.*, u.username FROM support_tickets t 
                     JOIN users u ON t.user_id = u.id 
                     ORDER BY t.updated_at DESC, t.id DESC')
            ->fetchAll();
    }

    public function allByUserId(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM support_tickets WHERE user_id = ? ORDER BY updated_at DESC, id DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT t.*, u.username FROM support_tickets t 
                                    JOIN users u ON t.user_id = u.id 
                                    WHERE t.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(int $userId, string $subject): int
    {
        $stmt = $this->db->prepare('INSERT INTO support_tickets (user_id, subject, status) VALUES (?, ?, "open")');
        $stmt->execute([$userId, $subject]);
        return (int)$this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE support_tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM support_tickets WHERE id = ?');
        $stmt->execute([$id]);
    }
}
