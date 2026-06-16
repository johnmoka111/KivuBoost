<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class PricingRule
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        return $this->db
            ->query('SELECT * FROM pricing_rules ORDER BY rule_type ASC, name ASC')
            ->fetchAll();
    }

    public function allActive(): array
    {
        return $this->db
            ->query('SELECT * FROM pricing_rules WHERE is_active = 1')
            ->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM pricing_rules WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $ruleType, string $targetValue, float $markupExtra): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pricing_rules (name, rule_type, target_value, markup_extra, is_active)
             VALUES (?, ?, ?, ?, 1)'
        );
        $stmt->execute([$name, $ruleType, $targetValue, $markupExtra]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $ruleType, string $targetValue, float $markupExtra, int $isActive): void
    {
        $stmt = $this->db->prepare(
            'UPDATE pricing_rules SET name = ?, rule_type = ?, target_value = ?, markup_extra = ?, is_active = ? WHERE id = ?'
        );
        $stmt->execute([$name, $ruleType, $targetValue, $markupExtra, $isActive, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM pricing_rules WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Applique les regles de tarification actives a un service specifique.
     * La regle la plus precise l'emporte : category > provider (si conflit, on additionne).
     * Retourne le markup_extra total a ajouter (en points de pourcentage).
     */
    public function getExtraMarkupForService(string $category, int $providerId): float
    {
        $rules = $this->allActive();
        $extra = 0.0;

        foreach ($rules as $rule) {
            if ($rule['rule_type'] === 'category') {
                // Correspondance insensible a la casse, partielle
                if (mb_stripos($category, $rule['target_value']) !== false ||
                    mb_stripos($rule['target_value'], $category) !== false) {
                    $extra += (float)$rule['markup_extra'];
                }
            } elseif ($rule['rule_type'] === 'provider') {
                if ((int)$rule['target_value'] === $providerId) {
                    $extra += (float)$rule['markup_extra'];
                }
            }
        }

        return $extra;
    }
}
