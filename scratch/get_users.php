<?php
require 'config/config.php';
$db = \App\Core\Database::getInstance();
print_r($db->query('SELECT id, email, username FROM users')->fetchAll(PDO::FETCH_ASSOC));
