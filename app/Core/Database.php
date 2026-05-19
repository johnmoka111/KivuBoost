<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database — Singleton PDO
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=utf8mb4',
                    DB_HOST,
                    DB_NAME
                );
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(['error' => 'Erreur de connexion à la base de données.']));
            }
        }

        return self::$instance;
    }

    // Empêcher l'instanciation directe
    private function __construct() {}
    private function __clone() {}
}
