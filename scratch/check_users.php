<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=bukavuboost;charset=utf8mb4", "root", "");
    $stmt = $pdo->query("SELECT id, username, email, password, role FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
