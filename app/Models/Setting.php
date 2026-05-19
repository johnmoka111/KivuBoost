<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Setting — Gestion des paramètres configurables (table `settings`)
 */
class Setting
{
    private PDO $db;
    private static array $cache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Lire une valeur par sa clé
     */
    public static function get(string $key, string $default = ''): string
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT cfg_value FROM settings WHERE cfg_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();

        self::$cache[$key] = ($value !== false) ? (string)$value : $default;
        return self::$cache[$key];
    }

    /**
     * Récupérer tous les paramètres
     */
    public function all(): array
    {
        return $this->db->query(
            'SELECT * FROM settings ORDER BY cfg_group, cfg_key'
        )->fetchAll();
    }

    /**
     * Récupérer les paramètres d'un groupe
     */
    public function getGroup(string $group): array
    {
        $stmt = $this->db->prepare(
            'SELECT cfg_key, cfg_value, description FROM settings WHERE cfg_group = ?'
        );
        $stmt->execute([$group]);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['cfg_key']] = $row['cfg_value'];
        }
        return $result;
    }

    /**
     * Mettre à jour une valeur
     */
    public function set(string $key, string $value): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE settings SET cfg_value = ? WHERE cfg_key = ?'
        );
        $stmt->execute([$value, $key]);
        self::$cache[$key] = $value; // Invalider le cache
        return $stmt->rowCount() > 0;
    }

    /**
     * Mettre à jour plusieurs valeurs en une fois
     */
    public function setMany(array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE settings SET cfg_value = ? WHERE cfg_key = ?'
        );
        foreach ($data as $key => $value) {
            $stmt->execute([(string)$value, $key]);
            self::$cache[$key] = (string)$value;
        }
    }

    /**
     * Retourne tous les settings sous forme clé => valeur
     */
    public function toArray(): array
    {
        $rows = $this->all();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['cfg_key']] = $row['cfg_value'];
        }
        return $result;
    }
}
