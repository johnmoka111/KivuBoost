<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Service
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT s.*, p.name AS provider_name 
             FROM services s 
             LEFT JOIN providers p ON p.id = s.provider_id 
             WHERE s.is_active = 1 AND (p.status = 1 OR p.status IS NULL)
             ORDER BY s.category, s.name'
        )->fetchAll();
    }

    public function allForAdmin(): array
    {
        return $this->db->query(
            'SELECT s.*, p.name AS provider_name 
             FROM services s 
             LEFT JOIN providers p ON p.id = s.provider_id 
             ORDER BY s.category, s.name'
        )->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, p.name AS provider_name, p.api_url, p.api_key, p.status AS provider_status
             FROM services s
             LEFT JOIN providers p ON p.id = s.provider_id
             WHERE s.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Retourne les services groupés par catégorie
     */
    public function groupedByCategory(): array
    {
        $rows = $this->all();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['category']][] = $row;
        }
        return $grouped;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO services (provider_id, external_service_id, category, name, buying_price, selling_price, min_quantity, max_quantity, is_active)
             VALUES (:provider_id, :external_service_id, :category, :name, :buying_price, :selling_price, :min_quantity, :max_quantity, :is_active)'
        );
        $stmt->execute([
            ':provider_id'         => (int)$data['provider_id'],
            ':external_service_id' => (int)$data['external_service_id'],
            ':category'            => $data['category'],
            ':name'                => $data['name'],
            ':buying_price'        => (float)$data['buying_price'],
            ':selling_price'       => (float)$data['selling_price'],
            ':min_quantity'        => (int)$data['min_quantity'],
            ':max_quantity'        => (int)$data['max_quantity'],
            ':is_active'           => isset($data['is_active']) ? (int)$data['is_active'] : 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE services SET 
                provider_id = :provider_id, 
                external_service_id = :external_service_id, 
                category = :category, 
                name = :name, 
                buying_price = :buying_price, 
                selling_price = :selling_price, 
                min_quantity = :min_quantity, 
                max_quantity = :max_quantity, 
                is_active = :is_active
             WHERE id = :id'
        );
        return $stmt->execute([
            ':provider_id'         => (int)$data['provider_id'],
            ':external_service_id' => (int)$data['external_service_id'],
            ':category'            => $data['category'],
            ':name'                => $data['name'],
            ':buying_price'        => (float)$data['buying_price'],
            ':selling_price'       => (float)$data['selling_price'],
            ':min_quantity'        => (int)$data['min_quantity'],
            ':max_quantity'        => (int)$data['max_quantity'],
            ':is_active'           => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            ':id'                  => $id,
        ]);
    }

    public function updatePrice(int $id, float $sellingPrice): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE services SET selling_price = ? WHERE id = ?'
        );
        return $stmt->execute([$sellingPrice, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM services WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function deleteByProvider(int $providerId): void
    {
        $stmt = $this->db->prepare('DELETE FROM services WHERE provider_id = ?');
        $stmt->execute([$providerId]);
    }
}
