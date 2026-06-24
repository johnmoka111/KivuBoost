<?php
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query('SELECT id, name, identifier, is_active FROM payment_gateways');
print_r($stmt->fetchAll(\PDO::FETCH_ASSOC));
