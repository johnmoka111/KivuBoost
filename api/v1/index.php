<?php

declare(strict_types=1);

// Autoriser CORS (optionnel, selon vos besoins)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');

// Charger l'environnement
require_once __DIR__ . '/../../config/config.php';

use App\Core\Database;
use App\Services\SmmApi;

// Fonction d'erreur utilitaire
function sendError(int $code, string $message) {
    http_response_code($code);
    echo json_encode(['statut' => 'erreur', 'message' => $message]);
    exit;
}

// 1. Vérifier que c'est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError(405, 'Méthode non autorisée. Utilisez POST.');
}

// 2. Récupérer les paramètres (Support JSON & URL-encoded)
$inputJSON = file_get_contents('php://input');
$inputData = json_decode($inputJSON, true);
if (is_array($inputData)) {
    $_POST = array_merge($_POST, $inputData);
}

$apiKey = $_POST['api_key'] ?? '';
$action = $_POST['action'] ?? '';

// 3. Authentification
if (empty($apiKey)) {
    sendError(401, 'Clé API manquante ou invalide.');
}

$db = Database::getInstance();
$stmt = $db->prepare("SELECT id, username, balance FROM users WHERE api_key = ? LIMIT 1");
$stmt->execute([$apiKey]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    sendError(401, 'Clé API non autorisée.');
}

// 4. Routage selon l'action
if ($action === 'wallet') {
    echo json_encode(['statut' => 'succes', 'solde' => $user['balance']]);
    exit;
}

if ($action === 'push') {
    $serviceId = (int)($_POST['service'] ?? 0);
    $link      = $_POST['link'] ?? '';
    $quantity  = (int)($_POST['quantity'] ?? 0);

    if ($serviceId <= 0 || empty($link) || $quantity <= 0) {
        sendError(400, 'Paramètres invalides pour la création du boost.');
    }

    // Récupérer les infos du service (tarifs locaux et provider)
    $stmtSvc = $db->prepare("SELECT provider_id, external_service_id, original_rate, calculated_rate, min_quantity, max_quantity, is_active FROM services WHERE id = ?");
    $stmtSvc->execute([$serviceId]);
    $serviceInfo = $stmtSvc->fetch(PDO::FETCH_ASSOC);

    if (!$serviceInfo || !$serviceInfo['is_active']) {
        sendError(404, 'Service introuvable ou désactivé.');
    }

    if ($quantity < $serviceInfo['min_quantity'] || $quantity > $serviceInfo['max_quantity']) {
        sendError(400, "La quantité doit être entre {$serviceInfo['min_quantity']} et {$serviceInfo['max_quantity']}.");
    }

    // Calcul du coût total
    // Rate est généralement pour 1000
    $totalCost = ($serviceInfo['calculated_rate'] / 1000) * $quantity;
    $totalCostBrut = ($serviceInfo['original_rate'] / 1000) * $quantity;

    // Vérification financière
    if ((float)$user['balance'] < $totalCost) {
        sendError(402, 'Solde insuffisant pour effectuer cette opération.');
    }

    // Récupération des accès du provider global
    $stmtProv = $db->prepare("SELECT api_url, api_key FROM providers WHERE id = ?");
    $stmtProv->execute([$serviceInfo['provider_id']]);
    $provider = $stmtProv->fetch(PDO::FETCH_ASSOC);

    if (!$provider) {
        sendError(500, 'Fournisseur SMM introuvable.');
    }

    // Appel API silencieux vers le grossiste
    // Adapter selon la signature exacte de SmmApi si nécessaire.
    $apiClient = new SmmApi($provider['api_url'], $provider['api_key']);
    $orderResponse = $apiClient->addOrder([
        'service'  => $serviceInfo['external_service_id'],
        'link'     => $link,
        'quantity' => $quantity
    ]);

    if (isset($orderResponse['error'])) {
        sendError(502, 'Erreur du fournisseur externe : ' . $orderResponse['error']);
    }

    $externalOrderId = $orderResponse['order'] ?? null;

    if (!$externalOrderId) {
        sendError(502, 'Le fournisseur n\'a pas retourné d\'ID de commande valide.');
    }

    // Déduction du solde & Enregistrement
    $db->beginTransaction();
    try {
        // MAJ solde
        $stmtUpd = $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmtUpd->execute([$totalCost, $user['id']]);

        // Historisation dans commandes_locales
        $stmtIns = $db->prepare("
            INSERT INTO commandes_locales 
            (user_id, service_id, url_cible, quantite_demandee, id_fournisseur_externe, prix_achat_brut, prix_facture_client, statut) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'traitement')
        ");
        $stmtIns->execute([
            $user['id'],
            $serviceId,
            $link,
            $quantity,
            $externalOrderId,
            $totalCostBrut,
            $totalCost
        ]);
        
        $localOrderId = $db->lastInsertId();

        $db->commit();

        echo json_encode(['statut' => 'succes', 'id_boost' => $localOrderId]);
    } catch (\Exception $e) {
        $db->rollBack();
        sendError(500, 'Erreur interne lors de la sauvegarde de la commande.');
    }
    exit;
}

// Action non reconnue
sendError(400, 'Action non reconnue ou non supportée.');
