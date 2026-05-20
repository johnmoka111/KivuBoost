<?php
require 'config/config.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query('SELECT DISTINCT category FROM services');
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Group by country heuristics
$countries = [];
foreach ($categories as $cat) {
    if (preg_match('/(France|USA|UK|Brazil|India|Russia|Germany|Nigeria|Arab|Africa|Global)/i', $cat, $matches)) {
        $country = ucfirst(strtolower($matches[1]));
        if (!isset($countries[$country])) {
            $countries[$country] = [];
        }
        $countries[$country][] = $cat;
    }
}
print_r($countries);
