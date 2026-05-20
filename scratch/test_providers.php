<?php
require 'config/config.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query('SELECT * FROM providers');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
