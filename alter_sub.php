<?php
require_once __DIR__ . '/config/config.php';
$db = \App\Core\Database::getInstance();
try {
    $db->exec('ALTER TABLE subscriptions ADD COLUMN external_subscription_id VARCHAR(50) DEFAULT NULL');
    $db->exec('ALTER TABLE subscriptions ADD COLUMN cost DECIMAL(10,4) NOT NULL DEFAULT 0');
    echo 'OK';
} catch (Exception $e) {
    echo $e->getMessage();
}
