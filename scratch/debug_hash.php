<?php
$password = 'Admin@2024';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
echo "Generated hash: " . $hash . "\n";
echo "Verify immediately: " . (password_verify($password, $hash) ? "YES" : "NO") . "\n";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=bukavuboost;charset=utf8mb4", "root", "");
    
    // Save to temp table or update superadmin
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'superadmin'");
    $stmt->execute([$hash]);
    
    // Read back
    $stmt = $pdo->prepare("SELECT password FROM users WHERE username = 'superadmin'");
    $stmt->execute();
    $dbHash = $stmt->fetchColumn();
    echo "Retrieved hash from DB: " . $dbHash . "\n";
    echo "Verify retrieved: " . (password_verify($password, $dbHash) ? "YES" : "NO") . "\n";
    
    // Check lengths
    echo "Generated length: " . strlen($hash) . "\n";
    echo "Retrieved length: " . strlen($dbHash) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
