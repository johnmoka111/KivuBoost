<?php
require_once __DIR__ . '/../config/config.php';

echo "=== Test du Systeme de Fidelite et Cashback KivuBoost ===" . PHP_EOL . PHP_EOL;

// Test 1 : Connexion Database
try {
    $db = App\Core\Database::getInstance();
    echo "OK Connexion Database" . PHP_EOL;
} catch (Throwable $e) {
    echo "ERROR Connexion Database : " . $e->getMessage() . PHP_EOL;
    exit;
}

// Test 2 : Existence des colonnes et tables
try {
    $columns = $db->query("SHOW COLUMNS FROM users LIKE '%points%'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Colonnes trouvees dans 'users' : " . implode(', ', $columns) . PHP_EOL;
    if (in_array('loyalty_points', $columns) && in_array('lifetime_points', $columns)) {
        echo "OK Les colonnes de fidelite existent sur la table 'users'." . PHP_EOL;
    } else {
        echo "ERROR Les colonnes de fidelite sont manquantes dans la table 'users'." . PHP_EOL;
    }

    $tableLogs = $db->query("SHOW TABLES LIKE 'loyalty_logs'")->fetchColumn();
    if ($tableLogs) {
        echo "OK La table 'loyalty_logs' existe." . PHP_EOL;
    } else {
        echo "ERROR La table 'loyalty_logs' est manquante." . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "ERROR Verification structure : " . $e->getMessage() . PHP_EOL;
}

// Test 3 : Instanciation du modele Loyalty
try {
    $loyalty = new App\Models\Loyalty();
    echo "OK Modele Loyalty instancie avec succes." . PHP_EOL;
} catch (Throwable $e) {
    echo "ERROR Instanciation Modele Loyalty : " . $e->getMessage() . PHP_EOL;
}

// Test 4 : Simulation d'un utilisateur de test
try {
    // Recuperer le premier utilisateur de la base ou en creer un temporaire
    $testUser = $db->query("SELECT id, username, loyalty_points, lifetime_points, balance FROM users LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$testUser) {
        echo "Aucun utilisateur trouve en base pour executer les tests de transaction." . PHP_EOL;
    } else {
        echo PHP_EOL . "Simulation sur l'utilisateur : " . $testUser['username'] . " (ID: " . $testUser['id'] . ")" . PHP_EOL;
        echo "  Points actuels : " . $testUser['loyalty_points'] . " | Points a vie : " . $testUser['lifetime_points'] . PHP_EOL;
        echo "  Solde actuel : " . $testUser['balance'] . PHP_EOL;

        // Calculer le palier actuel
        $spent = $loyalty->getUserTotalSpent((int)$testUser['id']);
        $tier = $loyalty->getUserTier((int)$testUser['id']);
        $nextTier = App\Models\Loyalty::getNextTierForSpent($spent);

        echo "  Total depense : " . $spent . " USD" . PHP_EOL;
        echo "  Palier actuel : " . $tier['name'] . " (Taux: " . ($tier['rate'] * 100) . "%)" . PHP_EOL;
        if ($nextTier) {
            echo "  Prochain palier : " . $nextTier['name'] . " (Seuil minimum: " . $nextTier['min_spent'] . " USD)" . PHP_EOL;
        } else {
            echo "  Prochain palier : Aucun (Palier maximum atteint)" . PHP_EOL;
        }

        // Simuler l'ajout de points pour une commande de 50.00 USD
        echo PHP_EOL . "Ajout de points pour une commande de 50.00 USD..." . PHP_EOL;
        $pointsAdded = $loyalty->addPointsForOrder((int)$testUser['id'], 99999, 50.00);
        echo "  Points ajoutes : " . $pointsAdded . PHP_EOL;

        // Re-charger l'utilisateur
        $updatedUser = $db->query("SELECT loyalty_points, lifetime_points FROM users WHERE id = " . $testUser['id'])->fetch(PDO::FETCH_ASSOC);
        echo "  Nouveaux points : " . $updatedUser['loyalty_points'] . " | Nouveaux points a vie : " . $updatedUser['lifetime_points'] . PHP_EOL;

        // Tester la conversion (redeem)
        echo PHP_EOL . "Test de conversion des points..." . PHP_EOL;
        // Si points < 500, on en ajoute temporairement pour passer le test
        if ((int)$updatedUser['loyalty_points'] < 500) {
            echo "  Points insuffisants (< 500) pour tester la conversion. Ajout temporaire de 500 points..." . PHP_EOL;
            $db->prepare("UPDATE users SET loyalty_points = loyalty_points + 500 WHERE id = ?")->execute([$testUser['id']]);
            $updatedUser = $db->query("SELECT loyalty_points, lifetime_points FROM users WHERE id = " . $testUser['id'])->fetch(PDO::FETCH_ASSOC);
            echo "  Points temporaires : " . $updatedUser['loyalty_points'] . PHP_EOL;
        }

        $redeemSuccess = $loyalty->redeemPoints((int)$testUser['id']);
        if ($redeemSuccess) {
            $finalUser = $db->query("SELECT loyalty_points, lifetime_points, balance FROM users WHERE id = " . $testUser['id'])->fetch(PDO::FETCH_ASSOC);
            echo "OK Conversion reussie !" . PHP_EOL;
            echo "  Points finaux : " . $finalUser['loyalty_points'] . PHP_EOL;
            echo "  Nouveau solde : " . $finalUser['balance'] . " USD (Difference : +" . ($finalUser['balance'] - $testUser['balance']) . " USD)" . PHP_EOL;
        } else {
            echo "ERROR Echec de la conversion." . PHP_EOL;
        }
    }
} catch (Throwable $e) {
    echo "ERROR Erreur lors de la simulation : " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== Fin des tests ===" . PHP_EOL;
