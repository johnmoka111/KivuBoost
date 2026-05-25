<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=bukavuboost;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $logs = $db->query("SELECT * FROM audit_logs WHERE user_id IS NULL OR username IS NULL")->fetchAll();

    $updated = 0;
    foreach ($logs as $log) {
        $id = $log['id'];
        $details = $log['details'];
        $username = null;

        if (preg_match('/par ([a-zA-Z0-9_]+)$/', $details, $matches)) {
            $username = $matches[1];
        } 
        elseif (preg_match('/utilisateur : ([a-zA-Z0-9_]+) \(/', $details, $matches)) {
            $username = $matches[1];
        }

        if ($username) {
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            $userId = $user ? $user['id'] : null;

            $updateStmt = $db->prepare("UPDATE audit_logs SET username = ?, user_id = ? WHERE id = ?");
            $updateStmt->execute([$username, $userId, $id]);
            $updated++;
        }
    }
    echo "Fixed $updated logs.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
