<?php
// ============================================================
// KivuBoost — Script d'adaptation de la base de données
// Utilise ob_flush + flush pour éviter les timeouts 502 sur
// les hébergements mutualisés avec proxy OpenResty/Nginx.
// ============================================================

// Désactiver la mise en mémoire tampon de sortie
if (ob_get_level()) { ob_end_clean(); }
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', false);
@set_time_limit(120);

// Fonction helper pour afficher et vider le buffer immédiatement
function out(string $msg, bool $ok = true): void {
    $color  = $ok ? '#34d399' : '#f87171';
    $prefix = $ok ? '✓' : '✗';
    echo "<div style='color:{$color};font-size:13px;padding:2px 0;'>{$prefix} {$msg}</div>";
    if (ob_get_level()) { ob_flush(); }
    flush();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>KivuBoost — Migration DB</title>
  <style>
    body { background:#0a0f1a; color:#e2e8f0; font-family:monospace; padding:30px; max-width:800px; margin:0 auto; }
    h1 { color:#00ff88; border-bottom:1px solid #1a2332; padding-bottom:10px; margin-bottom:20px; }
    .box { background:#0d1117; border:1px solid #1a2332; border-radius:10px; padding:20px; margin-bottom:20px; }
    .success { background:rgba(52,211,153,0.08); border-color:rgba(52,211,153,0.2); }
    .error { background:rgba(248,113,113,0.08); border-color:rgba(248,113,113,0.3); color:#f87171; padding:15px; border-radius:8px; margin-top:10px; }
    .warn { color:#fbbf24; }
  </style>
</head>
<body>
<h1>KivuBoost — Migration Base de Données</h1>
<div class="box">
<div style="color:#94a3b8;font-size:12px;margin-bottom:12px;">Démarrage de la migration...</div>
<?php

require_once __DIR__ . '/config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 30,
        ]
    );
    out("Connexion à la base de données réussie.");

    // 1. Colonne avatar dans users
    $checkAvatar = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    if (!$checkAvatar->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER role");
        out("Colonne 'avatar' ajoutée dans la table 'users'.");
    } else {
        out("Colonne 'avatar' existe déjà.");
    }

    // 2. Paramètre taux de change CDF
    $stmt = $pdo->prepare("SELECT id FROM settings WHERE cfg_key = 'usd_rate_cdf'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->exec("INSERT INTO settings (cfg_key, cfg_value, cfg_group, description) VALUES ('usd_rate_cdf', '2800', 'general', 'Taux de change 1 USD en CDF')");
        out("Paramètre 'usd_rate_cdf' inséré (1 USD = 2800 CDF).");
    } else {
        out("Paramètre 'usd_rate_cdf' existe déjà.");
    }

    // 2b. Table subscriptions
    $pdo->exec("CREATE TABLE IF NOT EXISTS `subscriptions` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `service_id` INT NOT NULL,
      `username` VARCHAR(100) NOT NULL,
      `min_quantity` INT NOT NULL,
      `max_quantity` INT NOT NULL,
      `posts` INT NOT NULL,
      `delay` INT DEFAULT 0,
      `status` ENUM('Active', 'Completed', 'Paused', 'Canceled') NOT NULL DEFAULT 'Active',
      `cost` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
      `external_subscription_id` VARCHAR(50) NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
      FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    out("Table 'subscriptions' créée ou déjà existante.");

    // 2c. Table audit_logs
    $pdo->exec("CREATE TABLE IF NOT EXISTS `audit_logs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NULL,
      `username` VARCHAR(50) NULL,
      `action` VARCHAR(100) NOT NULL,
      `details` TEXT NULL,
      `ip_address` VARCHAR(45) NULL,
      `user_agent` VARCHAR(255) NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    out("Table 'audit_logs' créée ou déjà existante.");

    // 2d. Table support_tickets
    $pdo->exec("CREATE TABLE IF NOT EXISTS `support_tickets` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `subject` VARCHAR(255) NOT NULL,
      `status` ENUM('open', 'answered', 'closed') NOT NULL DEFAULT 'open',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    out("Table 'support_tickets' créée ou déjà existante.");

    // 2d. Table support_messages
    $pdo->exec("CREATE TABLE IF NOT EXISTS `support_messages` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `ticket_id` INT NOT NULL,
      `sender_id` INT NOT NULL,
      `message` TEXT NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets`(`id`) ON DELETE CASCADE,
      FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    out("Table 'support_messages' créée ou déjà existante.");

    // 2e. Table pricing_rules
    $pdo->exec("CREATE TABLE IF NOT EXISTS `pricing_rules` (
      `id`            INT AUTO_INCREMENT PRIMARY KEY,
      `name`          VARCHAR(150) NOT NULL,
      `rule_type`     ENUM('category','provider') NOT NULL DEFAULT 'category',
      `target_value`  VARCHAR(150) NOT NULL,
      `markup_extra`  DECIMAL(6,2) NOT NULL DEFAULT 0.00,
      `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
      `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    out("Table 'pricing_rules' créée ou déjà existante.");

    // 2f. Colonne api_error_log dans orders
    $checkApiLog = $pdo->query("SHOW COLUMNS FROM orders LIKE 'api_error_log'");
    if (!$checkApiLog->fetch()) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN api_error_log TEXT NULL DEFAULT NULL AFTER external_order_id");
        out("Colonne 'api_error_log' ajoutée dans la table 'orders'.");
    } else {
        out("Colonne 'api_error_log' existe déjà.");
    }

    // 2g. Colonne loyalty_points dans users
    $checkLP = $pdo->query("SHOW COLUMNS FROM users LIKE 'loyalty_points'");
    if (!$checkLP->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN loyalty_points INT NOT NULL DEFAULT 0 AFTER balance");
        out("Colonne 'loyalty_points' ajoutée dans la table 'users'.");
    } else {
        out("Colonne 'loyalty_points' existe déjà.");
    }

    // 2g. Colonne lifetime_points dans users
    $checkLifetime = $pdo->query("SHOW COLUMNS FROM users LIKE 'lifetime_points'");
    if (!$checkLifetime->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN lifetime_points INT NOT NULL DEFAULT 0 AFTER loyalty_points");
        out("Colonne 'lifetime_points' ajoutée dans la table 'users'.");
    } else {
        out("Colonne 'lifetime_points' existe déjà.");
    }

    // 2h. Table loyalty_logs
    $pdo->exec("CREATE TABLE IF NOT EXISTS `loyalty_logs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `points` INT NOT NULL,
      `description` VARCHAR(255) NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    out("Table 'loyalty_logs' créée ou déjà existante.");

    // 3. Fournisseur SMM Panel
    $stmtProv = $pdo->prepare("SELECT id FROM providers WHERE api_url LIKE '%my.smm-panel.com%' LIMIT 1");
    $stmtProv->execute();
    $prov = $stmtProv->fetch();
    if ($prov) {
        $stmtUp = $pdo->prepare("UPDATE providers SET name = ?, api_key = ?, status = 1 WHERE id = ?");
        $stmtUp->execute(['My SMM Panel', 'c7d77ee03eb0d7c8b3a70077e0f72ecf', $prov['id']]);
        out("Fournisseur 'My SMM Panel' mis à jour avec les clés API.");
    } else {
        $stmtIn = $pdo->prepare("INSERT INTO providers (name, api_url, api_key, status) VALUES (?, ?, ?, ?)");
        $stmtIn->execute(['My SMM Panel', 'https://my.smm-panel.com/api/v2', 'c7d77ee03eb0d7c8b3a70077e0f72ecf', 1]);
        out("Fournisseur 'My SMM Panel' ajouté.");
    }

} catch (Exception $e) {
    echo "<div class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
</div>
<div class="box success" style="text-align:center;padding:15px;">
  <div style="color:#34d399;font-size:16px;font-weight:bold;">Migration terminee avec succes !</div>
  <div style="color:#6b7280;font-size:11px;margin-top:6px;">Vous pouvez maintenant retourner sur votre application.</div>
  <a href="/" style="display:inline-block;margin-top:12px;padding:8px 20px;background:#00ff88;color:#000;border-radius:8px;font-weight:bold;font-size:12px;text-decoration:none;">Retourner sur KivuBoost</a>
</div>
</body>
</html>
