<?php
require 'config/config.php';

try {
    $db = \App\Core\Database::getInstance();
    
    // johnmoka
    $hash1 = password_hash('KivuBoosterAdmin2026', PASSWORD_BCRYPT);
    $stmt1 = $db->prepare("UPDATE users SET password = ? WHERE email = 'johnmoka2024@gmail.com'");
    $stmt1->execute([$hash1]);
    
    // esthermoka
    $hash2 = password_hash('esthermoka2026.', PASSWORD_BCRYPT);
    $stmt2 = $db->prepare("UPDATE users SET password = ? WHERE email = 'esthermoka@gmail.com'");
    $stmt2->execute([$hash2]);
    
    echo "Passwords updated successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
