<?php
require_once __DIR__ . '/config/config.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query('SHOW CREATE TABLE subscriptions');
$row = $stmt->fetch();
print_r($row);
