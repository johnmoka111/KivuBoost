<?php
// ============================================================
// BukavuBoost — Script de Setup (À SUPPRIMER après utilisation)
// Accès : http://localhost/KivuBoost/setup.php
// ============================================================

require_once __DIR__ . '/config/config.php';

use App\Core\Database;

$db = Database::getInstance();

// 1. Créer le hash du mot de passe superadmin
$password     = 'Admin@2024';
$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// 2. Mettre à jour le compte superadmin
$stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'superadmin'");
$stmt->execute([$passwordHash]);

echo '<h1 style="font-family:monospace;color:green;">✅ Setup terminé !</h1>';
echo '<p>Compte SuperAdmin créé :</p>';
echo '<ul style="font-family:monospace;">';
echo "<li>Email : admin@bukavuboost.cd</li>";
echo "<li>Mot de passe : $password</li>";
echo '</ul>';
echo '<p style="color:red;font-weight:bold;">⚠️ SUPPRIMEZ CE FICHIER MAINTENANT : <code>delete setup.php</code></p>';
