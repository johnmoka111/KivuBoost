<?php
// ============================================================
// BukavuBoost — Script d'importation automatique de la base
// ============================================================

require_once __DIR__ . '/config/config.php';

try {
    // Connexion initiale sans spécifier la base de données
    $pdo = new PDO(
        "mysql:host=" . DB_HOST,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "Connexion au serveur MySQL réussie.<br>";

    // Lire le contenu de database.sql
    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Le fichier database.sql est introuvable à la racine.");
    }

    $sqlContent = file_get_contents($sqlFile);
    
    // Remplacer les délimiteurs complexes si présents ou exécuter par blocs
    // Supprimer les commentaires simples sql -- et #
    $sqlContent = preg_replace('/^\s*(?:--|#).*/m', '', $sqlContent);

    // Diviser les requêtes par point-virgule
    $queries = explode(';', $sqlContent);

    echo "Importation en cours...<br>";
    $executed = 0;
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) {
            continue;
        }

        try {
            $pdo->exec($query);
            $executed++;
        } catch (PDOException $e) {
            // Afficher mais continuer si ce sont des tables déjà créées
            echo "Avertissement / Info sur requête : " . $e->getMessage() . "<br>";
        }
    }

    echo "<strong>Succès !</strong> $executed requêtes exécutées avec succès dans la base 'bukavuboost'.<br>";
    echo "Vous pouvez maintenant vous connecter à la plateforme !";

} catch (Exception $e) {
    echo "<strong>Erreur d'importation :</strong> " . $e->getMessage();
}
