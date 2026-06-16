<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SupportMessage
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function allByTicketId(int $ticketId): array
    {
        $stmt = $this->db->prepare('
            SELECT m.*, u.username, u.role, u.avatar 
            FROM support_messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.ticket_id = ?
            ORDER BY m.created_at ASC, m.id ASC
        ');
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public function create(int $ticketId, int $senderId, string $message): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO support_messages (ticket_id, sender_id, message) 
            VALUES (?, ?, ?)
        ');
        $stmt->execute([$ticketId, $senderId, $message]);
        return (int)$this->db->lastInsertId();
    }
}
