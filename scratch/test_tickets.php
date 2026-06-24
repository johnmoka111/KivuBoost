<?php
require_once __DIR__ . '/../config/config.php';

echo "=== Test de validation KivuBoost ===" . PHP_EOL . PHP_EOL;

// Test 1 : Router
try {
    $r = new App\Core\Router();
    echo "✅ Router : OK" . PHP_EOL;
} catch (Throwable $e) {
    echo "❌ Router : " . $e->getMessage() . PHP_EOL;
}

// Test 2 : Database
try {
    $db = App\Core\Database::getInstance();
    echo "✅ Database : Connexion OK" . PHP_EOL;
} catch (Throwable $e) {
    echo "❌ Database : " . $e->getMessage() . PHP_EOL;
}

// Test 3 : SupportTicket model
try {
    $ticketModel = new App\Models\SupportTicket();
    $tickets = $ticketModel->all();
    echo "✅ SupportTicket model : OK (" . count($tickets) . " tickets)" . PHP_EOL;
} catch (Throwable $e) {
    echo "❌ SupportTicket model : " . $e->getMessage() . PHP_EOL;
}

// Test 4 : SupportMessage model
try {
    $msgModel = new App\Models\SupportMessage();
    echo "✅ SupportMessage model : OK" . PHP_EOL;
} catch (Throwable $e) {
    echo "❌ SupportMessage model : " . $e->getMessage() . PHP_EOL;
}

// Test 5 : Vérifier que les tables existent
try {
    $db = App\Core\Database::getInstance();
    $tables = $db->query("SHOW TABLES LIKE 'support_%'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        echo "✅ Table existante : `{$t}`" . PHP_EOL;
    }
    if (empty($tables)) {
        echo "⚠️  Aucune table support_* trouvée — exécutez update_schema.php" . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "❌ Vérification tables : " . $e->getMessage() . PHP_EOL;
}

// Test 6 : Vérifier les vues
$views = [
    'support/client_list',
    'support/client_view',
    'support/admin_list',
    'support/admin_view',
];
foreach ($views as $v) {
    $path = VIEW_PATH . '/' . $v . '.php';
    echo (file_exists($path) ? "✅" : "❌") . " Vue : {$v}.php" . PHP_EOL;
}

echo PHP_EOL . "=== Fin des tests ===" . PHP_EOL;
