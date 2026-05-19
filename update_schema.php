<?php
// ============================================================
// BukavuBoost — Script d'adaptation de la base de données
// ============================================================

require_once __DIR__ . '/config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // 1. Ajouter la colonne avatar si elle n'existe pas
    $checkAvatar = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    if (!$checkAvatar->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER role");
        echo "Colonne 'avatar' ajoutée avec succès dans la table 'users'.<br>";
    } else {
        echo "La colonne 'avatar' existe déjà.<br>";
    }

    // 2. Ajouter le paramètre de taux de change dans settings s'il n'existe pas
    $stmt = $pdo->prepare("SELECT id FROM settings WHERE cfg_key = 'usd_rate_cdf'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->exec("INSERT INTO settings (cfg_key, cfg_value, cfg_group, description) VALUES ('usd_rate_cdf', '2800', 'general', 'Taux de change 1 USD en CDF (Franc Congolais)')");
        echo "Paramètre de taux 'usd_rate_cdf' inséré par défaut (1 USD = 2800 CDF).<br>";
    } else {
        echo "Paramètre 'usd_rate_cdf' existe déjà.<br>";
    }

    // 3. Ajouter ou mettre à jour le fournisseur d'API avec les clés réelles fournies
    $stmtProv = $pdo->prepare("SELECT id FROM providers WHERE api_url LIKE '%my.smm-panel.com%' LIMIT 1");
    $stmtProv->execute();
    $prov = $stmtProv->fetch();

    if ($prov) {
        // Mettre à jour les informations existantes
        $stmtUp = $pdo->prepare("UPDATE providers SET name = ?, api_key = ?, status = 1 WHERE id = ?");
        $stmtUp->execute(['My SMM Panel', 'c7d77ee03eb0d7c8b3a70077e0f72ecf', $prov['id']]);
        echo "Le fournisseur 'My SMM Panel' a été mis à jour avec vos nouvelles clés d'API !<br>";
    } else {
        // Insérer le nouveau fournisseur
        $stmtIn = $pdo->prepare("INSERT INTO providers (name, api_url, api_key, status) VALUES (?, ?, ?, ?)");
        $stmtIn->execute(['My SMM Panel', 'https://my.smm-panel.com/api/v2', 'c7d77ee03eb0d7c8b3a70077e0f72ecf', 1]);
        echo "Nouveau fournisseur 'My SMM Panel' ajouté avec vos clés d'API !<br>";
    }

    echo "<strong>Mise à jour du schéma et des données réussie !</strong>";
} catch (Exception $e) {
    echo "Erreur lors de la mise à jour de la base de données : " . $e->getMessage();
}
