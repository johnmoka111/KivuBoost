<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SupportAgent
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function allActive(): array
    {
        return $this->db
            ->query('SELECT * FROM support_agents WHERE is_active = 1 ORDER BY id ASC')
            ->fetchAll();
    }

    public function all(): array
    {
        return $this->db
            ->query('SELECT * FROM support_agents ORDER BY is_active DESC, id ASC')
            ->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM support_agents WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $name, string $city, string $whatsapp, ?string $photoPath): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO support_agents (name, city, whatsapp_number, photo_path, is_active)
             VALUES (?, ?, ?, ?, 1)'
        );
        $stmt->execute([$name, $city, $whatsapp, $photoPath]);
        return (int)$this->db->lastInsertId();
    }

    public function setActive(int $id, int $isActive): void
    {
        $stmt = $this->db->prepare('UPDATE support_agents SET is_active = ? WHERE id = ?');
        $stmt->execute([$isActive ? 1 : 0, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM support_agents WHERE id = ?');
        $stmt->execute([$id]);
    }
}
