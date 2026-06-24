<?php
// ============================================================
// Script de migration pour les passerelles de paiement
// ============================================================

require_once __DIR__ . '/../config/config.php';
$db = App\Core\Database::getInstance();

try {
    echo "1. Modification de la colonne network dans la table recharges...\n";
    $db->exec("ALTER TABLE `recharges` MODIFY COLUMN `network` VARCHAR(50) NOT NULL");
    echo "Colonne network modifiée avec succès (de ENUM à VARCHAR(50)).\n";

    echo "2. Création de la table payment_gateways...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS `payment_gateways` (
        `id`               INT AUTO_INCREMENT PRIMARY KEY,
        `name`             VARCHAR(100) NOT NULL,
        `identifier`       VARCHAR(50) NOT NULL UNIQUE,
        `public_key`       TEXT NULL,
        `private_key`      TEXT NULL,
        `signature_secret` TEXT NULL,
        `api_url`          VARCHAR(255) NULL,
        `is_active`        TINYINT(1) NOT NULL DEFAULT 0,
        `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Table payment_gateways créée avec succès.\n";

    echo "3. Insertion des passerelles par défaut...\n";
    $defaultGateways = [
        [
            'name' => 'BkaPay',
            'identifier' => 'bkapay',
            'api_url' => 'https://bkapay.com/api/v1/payment-sessions',
            'is_active' => 0
        ],
        [
            'name' => 'PawaPay',
            'identifier' => 'pawapay',
            'api_url' => 'https://api.pawapay.io/deposits',
            'is_active' => 0
        ],
        [
            'name' => 'VisaPay',
            'identifier' => 'visapay',
            'api_url' => 'https://api.visapay.io/v1/charges',
            'is_active' => 0
        ]
    ];

    $stmtCheck = $db->prepare("SELECT COUNT(*) FROM payment_gateways WHERE identifier = ?");
    $stmtInsert = $db->prepare("INSERT INTO payment_gateways (name, identifier, api_url, is_active) VALUES (?, ?, ?, ?)");

    foreach ($defaultGateways as $gw) {
        $stmtCheck->execute([$gw['identifier']]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmtInsert->execute([$gw['name'], $gw['identifier'], $gw['api_url'], $gw['is_active']]);
            echo "Passerelle '{$gw['name']}' insérée.\n";
        } else {
            echo "Passerelle '{$gw['name']}' existe déjà, ignorée.\n";
        }
    }

    echo "Migration terminée avec succès !\n";
} catch (Exception $e) {
    echo "Erreur lors de la migration : " . $e->getMessage() . "\n";
}
