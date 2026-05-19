<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Provider
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT * FROM providers ORDER BY name'
        )->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM providers WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $apiUrl, string $apiKey, int $status = 1): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO providers (name, api_url, api_key, status) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $apiUrl, $apiKey, $status]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $apiUrl, string $apiKey, int $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE providers SET name = ?, api_url = ?, api_key = ?, status = ? WHERE id = ?'
        );
        return $stmt->execute([$name, $apiUrl, $apiKey, $status, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM providers WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
