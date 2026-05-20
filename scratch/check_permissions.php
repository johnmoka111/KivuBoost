<?php
$uploadDir = 'C:\xampp\htdocs\KivuBoost\public\uploads\avatars\\';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$testFile = $uploadDir . 'test.txt';
if (file_put_contents($testFile, 'test')) {
    echo "Can write.\n";
} else {
    echo "Cannot write.\n";
}
