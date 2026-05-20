<?php
require 'config/config.php';
$db = \App\Core\Database::getInstance();
$email = 'johnmoka2024@gmail.com';
$password = password_hash('KivuBoosterAdmin2026.', PASSWORD_BCRYPT);

$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    $db->prepare('UPDATE users SET password = ?, role = "admin" WHERE email = ?')->execute([$password, $email]);
    echo "User updated.\n";
} else {
    $db->prepare('INSERT INTO users (name, email, password, role, balance) VALUES (?, ?, ?, ?, ?)')
       ->execute(['John Moka', $email, $password, 'admin', 0]);
    echo "User created.\n";
}
