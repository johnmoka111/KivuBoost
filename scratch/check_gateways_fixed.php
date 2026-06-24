<?php
require_once __DIR__ . '/../config/config.php';
$db = App\Core\Database::getInstance();
$stmt = $db->query('SELECT * FROM payment_gateways');
print_r($stmt->fetchAll(\PDO::FETCH_ASSOC));
