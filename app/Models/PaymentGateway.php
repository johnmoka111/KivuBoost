<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class PaymentGateway
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupérer toutes les passerelles de paiement
     */
    public function all(): array
    {
        return $this->db->query('SELECT * FROM payment_gateways ORDER BY id ASC')->fetchAll();
    }

    /**
     * Récupérer toutes les passerelles de paiement actives
     */
    public function allActive(): array
    {
        return $this->db->query('SELECT * FROM payment_gateways WHERE is_active = 1 ORDER BY id ASC')->fetchAll();
    }

    /**
     * Trouver une passerelle par son identifiant
     */
    public function findByIdentifier(string $identifier): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM payment_gateways WHERE identifier = ? LIMIT 1');
        $stmt->execute([$identifier]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Mettre à jour la configuration d'une passerelle
     */
    public function update(string $identifier, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE payment_gateways 
             SET name = ?, public_key = ?, private_key = ?, signature_secret = ?, is_active = ?, api_url = ?
             WHERE identifier = ?'
        );
        $stmt->execute([
            $data['name'],
            $data['public_key'],
            $data['private_key'],
            $data['signature_secret'],
            (int)$data['is_active'],
            $data['api_url'],
            $identifier
        ]);
        return $stmt->rowCount() > 0;
    }
}
