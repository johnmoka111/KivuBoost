<?php
require_once __DIR__ . '/../config/config.php';
$db = App\Core\Database::getInstance();

echo "--- USERS TABLE ---\n";
try {
    $stmt = $db->query('DESCRIBE users');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n--- RECHARGES TABLE ---\n";
try {
    $stmt = $db->query('DESCRIBE recharges');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
