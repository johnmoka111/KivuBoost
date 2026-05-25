<?php
require 'config/config.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query("SELECT id, username, email, password, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$candidates = [
    'Admin@2024',
    'KivuBoosterAdmin2026',
    'KivuBoosterAdmin2026.',
    'esthermoka2026.',
    'esthermoka2026',
    'admin',
    '12345678',
    '123456',
    'password',
    'johnmoka',
    'esthermoka',
    'KivuBoost',
    'KivuBoost2026',
    'KivuBoost@2026',
];

foreach ($users as $user) {
    echo "User: {$user['username']} ({$user['email']}) [{$user['role']}]\n";
    echo "  Hash: {$user['password']}\n";
    $found = false;
    foreach ($candidates as $cand) {
        if (password_verify($cand, $user['password'])) {
            echo "  --> MATCHED PASSWORD: '$cand'\n";
            $found = true;
        }
    }
    if (!$found) {
        echo "  --> NO MATCH found among candidates.\n";
    }
}
