<?php
require 'config/config.php';
$db = \App\Core\Database::getInstance();
try {
    $db->exec('ALTER TABLE providers ADD COLUMN markup_percentage INT NOT NULL DEFAULT 0 AFTER api_key');
    echo "Added markup_percentage to providers.\n";
} catch (\PDOException $e) {
    echo "markup_percentage column might already exist: " . $e->getMessage() . "\n";
}
try {
    $db->exec('ALTER TABLE services CHANGE COLUMN buying_price original_rate DECIMAL(10,4) NOT NULL');
    $db->exec('ALTER TABLE services CHANGE COLUMN selling_price calculated_rate DECIMAL(10,4) NOT NULL');
    echo "Renamed buying_price and selling_price to original_rate and calculated_rate in services.\n";
} catch (\PDOException $e) {
    echo "Error renaming columns in services (might already be renamed): " . $e->getMessage() . "\n";
}
