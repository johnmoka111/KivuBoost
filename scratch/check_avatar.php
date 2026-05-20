<?php
require 'config/config.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query('SELECT avatar FROM users WHERE email="johnmoka2024@gmail.com"');
print_r($stmt->fetch());
