<?php
require 'config/config.php';
$db = \App\Core\Database::getInstance();
$userModel = new \App\Models\User();

$res = $userModel->updateAvatar(2, 'test.jpg'); // Assuming John Moka is id 2 or 1
echo "Update result: " . ($res ? 'Success' : 'Fail') . "\n";
