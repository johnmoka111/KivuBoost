<?php
require 'config/config.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query('DESCRIBE users');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
