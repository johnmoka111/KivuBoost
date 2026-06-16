<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Service;
use App\Models\Order;
use App\Models\User;
use App\Services\SmmApi;
use App\Core\Audit;

class OrderController extends Controller
{
    // -------------------------------------------------------
    // POST /orders/place (Traitement commande multi-API)
    // -------------------------------------------------------
    public function place(): void
    {
        Auth::requireLogin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/dashboard');
        }

        $serviceId = (int)($_POST['service_id'] ?? 0);
        $link      = trim($_POST['link'] ?? '');
        $quantity  = (int)($_POST['quantity'] ?? 0);

        // --- Validation de base ---
        if ($serviceId <= 0 || empty($link) || $quantity <= 0) {
            $this->flash('error', 'Tous les champs de commande sont obligatoires.');
            $this->redirect('/dashboard');
        }

        if (!filter_var($link, FILTER_VALIDATE_URL)) {
            $this->flash('error', 'L\'URL du lien est invalide.');
            $this->redirect('/dashboard');
        }

        $serviceModel = new Service();
        // Le findById contient déjà la jointure avec providers (api_url, api_key, etc.)
        $service = $serviceModel->findById($serviceId);

        if (!$service || !$service['is_active']) {
            $this->flash('error', 'Service introuvable ou désactivé.');
            $this->redirect('/dashboard');
        }

        // Vérifier si le fournisseur est actif
        if (isset($service['provider_status']) && (int)$service['provider_status'] !== 1) {
            $this->flash('error', 'Le serveur du fournisseur pour ce service est en maintenance. Réessayez plus tard.');
            $this->redirect('/dashboard');
        }

        // --- Validation quantité ---
        if ($quantity < $service['min_quantity'] || $quantity > $service['max_quantity']) {
            $this->flash('error', sprintf(
                'La quantité doit être entre %d et %d.',
                $service['min_quantity'],
                $service['max_quantity']
            ));
            $this->redirect('/dashboard');
        }

        // --- Calcul du coût dynamique basé sur le calculated_rate local ---
        $cost = round(($service['calculated_rate'] * $quantity) / 1000, 4);

        // --- Vérification du solde ---
        $user = Auth::user();
        if ((float)$user['balance'] < $cost) {
            $this->flash('error', sprintf(
                'Solde insuffisant. Coût : $%.2f — Votre solde : $%.2f. Veuillez recharger votre compte.',
                $cost,
                $user['balance']
            ));
            $this->redirect('/dashboard');
        }

        // --- Transaction atomique PDO ---
        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            $userModel  = new User();
            $orderModel = new Order();

            // 1. Débiter le solde du client local
            $debited = $userModel->debitBalance((int)$user['id'], $cost);
            if (!$debited) {
                throw new \RuntimeException('Échec du débit : solde insuffisant ou erreur.');
            }

            // 2. Créer la commande en base locale
            $orderId = $orderModel->create(
                (int)$user['id'],
                $serviceId,
                $link,
                $quantity,
                $cost
            );

            $db->commit();
            Audit::log('place_order', "Commande #{$orderId} créée (Coût : {$cost} USD, Service : {$service['name']}, Quantité : {$quantity})");

            // Ajouter les points de cashback/fidélité
            try {
                (new \App\Models\Loyalty())->addPointsForOrder((int)$user['id'], $orderId, $cost);
            } catch (\Throwable $e) {
                // Silencieusement ignorer en cas de table non migrée
            }

        } catch (\Throwable $e) {
            $db->rollBack();
            $this->flash('error', 'Erreur lors de la création de la commande. Veuillez réessayer.');
            $this->redirect('/dashboard');
        }

        // --- Envoi vers l'API du fournisseur spécifique (Routage dynamique) ---
        $apiUrl = $service['api_url'] ?? '';
        $apiKey = $service['api_key'] ?? '';

        if (!empty($apiUrl) && !empty($apiKey) && $apiKey !== SMM_PLACEHOLDER_KEY) {
            try {
                // Instanciation de SmmApi avec les coordonnées récupérées dynamiquement depuis la jointure
                $api = new SmmApi($apiKey, $apiUrl);
                
                // Routage automatique de la commande chez le fournisseur spécifique
                $response = $api->addOrder(
                    (int)$service['external_service_id'],
                    $link,
                    $quantity
                );

                if (isset($response['order'])) {
                    $orderModel->updateExternalId($orderId, (string)$response['order'], 'Processing');
                } else {
                    // L'envoi a échoué chez le fournisseur mais la commande est stockée — marked Pending pour ré-essai admin
                    $orderModel->updateStatus($orderId, 'Pending');
                }
            } catch (\Throwable $e) {
                // Erreur de communication — reste en Pending pour contrôle manuel
            }
        }

        // Rafraîchir le cache utilisateur
        Auth::refreshUser();

        // Envoi de l'email de confirmation
        $userData = Auth::user();
        if (!empty($userData['email'])) {
            $emailData = [
                'username'    => $userData['username'],
                'orderId'     => str_pad((string)$orderId, 5, '0', STR_PAD_LEFT),
                'serviceName' => $service['name'],
                'quantity'    => number_format($quantity, 0, ',', ' '),
                'cost'        => number_format($cost, 4),
                'link'        => $link,
                'dashboardUrl'=> APP_URL . '/history'
            ];
            sendKivuBoostMail(
                $userData['email'], 
                "Confirmation de commande #" . $emailData['orderId'], 
                'order_confirmation', 
                $emailData
            );
        }

        $this->flash('success', sprintf(
            'Commande #%d passée avec succès ! Coût débité : $%.4f',
            $orderId,
            $cost
        ));
        $this->redirect('/dashboard');
    }

    // -------------------------------------------------------
    // POST /orders/mass-place (Traitement commande de masse)
    // -------------------------------------------------------
    public function massPlace(): void
    {
        Auth::requireLogin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/dashboard');
        }

        $massOrderText = trim($_POST['mass_order'] ?? '');
        if (empty($massOrderText)) {
            $this->flash('error', 'Le contenu de la commande de masse est vide.');
            $this->redirect('/dashboard');
        }

        $lines = explode("\n", str_replace("\r", "", $massOrderText));
        $successCount = 0;
        $errorCount = 0;
        $reportMessages = [];

        $userModel = new User();
        $serviceModel = new Service();
        $orderModel = new Order();
        $db = Database::getInstance();

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = explode('|', $line);
            if (count($parts) < 3) {
                $errorCount++;
                $reportMessages[] = "Ligne " . ($index + 1) . " : Format invalide. Format requis : service_id | quantité | lien";
                continue;
            }

            $serviceId = (int)trim($parts[0]);
            $quantity  = (int)trim($parts[1]);
            $link      = trim($parts[2]);

            // Validation de base
            if ($serviceId <= 0 || empty($link) || $quantity <= 0) {
                $errorCount++;
                $reportMessages[] = "Ligne " . ($index + 1) . " : Valeurs de commande invalides.";
                continue;
            }

            if (!filter_var($link, FILTER_VALIDATE_URL)) {
                $errorCount++;
                $reportMessages[] = "Ligne " . ($index + 1) . " : URL du lien invalide.";
                continue;
            }

            // Charger service
            $service = $serviceModel->findById($serviceId);
            if (!$service || !$service['is_active']) {
                $errorCount++;
                $reportMessages[] = "Ligne " . ($index + 1) . " : Service ID {$serviceId} introuvable ou inactif.";
                continue;
            }

            if (isset($service['provider_status']) && (int)$service['provider_status'] !== 1) {
                $errorCount++;
                $reportMessages[] = "Ligne " . ($index + 1) . " : Le serveur du fournisseur pour ce service est en maintenance.";
                continue;
            }

            if ($quantity < $service['min_quantity'] || $quantity > $service['max_quantity']) {
                $errorCount++;
                $reportMessages[] = "Ligne " . ($index + 1) . " : La quantité doit être entre {$service['min_quantity']} et {$service['max_quantity']}.";
                continue;
            }

            // Calcul du coût
            $cost = round(($service['calculated_rate'] * $quantity) / 1000, 4);

            // Charger le solde mis à jour à chaque itération pour éviter les doubles dépenses
            $user = $userModel->findById((int)Auth::user()['id']);
            if (!$user || (float)$user['balance'] < $cost) {
                $errorCount++;
                $reportMessages[] = "Ligne " . ($index + 1) . " : Solde insuffisant (Requis : $" . number_format($cost, 4) . ").";
                continue;
            }

            // Processus de commande
            try {
                $db->beginTransaction();

                // 1. Débiter
                $debited = $userModel->debitBalance((int)$user['id'], $cost);
                if (!$debited) {
                    throw new \RuntimeException('Échec du débit.');
                }

                // 2. Créer la commande
                $orderId = $orderModel->create(
                    (int)$user['id'],
                    $serviceId,
                    $link,
                    $quantity,
                    $cost
                );

                $db->commit();
                Audit::log('place_mass_order', "Commande en masse #{$orderId} créée (Coût : {$cost} USD, Service : {$service['name']}, Quantité : {$quantity})");

                // Ajouter les points de cashback/fidélité
                try {
                    (new \App\Models\Loyalty())->addPointsForOrder((int)$user['id'], $orderId, $cost);
                } catch (\Throwable $e) {
                    // Silencieusement ignorer en cas de table non migrée
                }

                // Routage API grossiste
                $apiUrl = $service['api_url'] ?? '';
                $apiKey = $service['api_key'] ?? '';
                if (!empty($apiUrl) && !empty($apiKey) && $apiKey !== SMM_PLACEHOLDER_KEY) {
                    try {
                        $api = new SmmApi($apiKey, $apiUrl);
                        $response = $api->addOrder(
                            (int)$service['external_service_id'],
                            $link,
                            $quantity
                        );

                        if (isset($response['order'])) {
                            $orderModel->updateExternalId($orderId, (string)$response['order'], 'Processing');
                        } else {
                            $orderModel->updateStatus($orderId, 'Pending');
                        }
                    } catch (\Throwable $e) {
                        // Reste en Pending
                    }
                }

                $successCount++;
                $reportMessages[] = "Ligne " . ($index + 1) . " : Commande #{$orderId} créée avec succès !";

            } catch (\Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $errorCount++;
                $reportMessages[] = "Ligne " . ($index + 1) . " : Erreur interne de traitement.";
            }
        }

        Auth::refreshUser();

        $flashType = $successCount > 0 ? 'success' : 'error';
        $reportStr = "<strong>Traitement de masse terminé.</strong><br>Succès : {$successCount} | Échecs : {$errorCount}<br><ul class='mt-2 list-disc pl-4 text-xs space-y-1'>" . implode("", array_map(fn($m) => "<li>$m</li>", $reportMessages)) . "</ul>";
        $this->flash($flashType, $reportStr);
        $this->redirect('/dashboard');
    }

    // -------------------------------------------------------
    // POST /subscriptions/create (Création d'abonnement auto)
    // -------------------------------------------------------
    public function createSubscription(): void
    {
        Auth::requireLogin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/dashboard');
        }

        $serviceId   = (int)($_POST['service_id'] ?? 0);
        $username    = trim($_POST['username'] ?? '');
        $minQuantity = (int)($_POST['min_quantity'] ?? 0);
        $maxQuantity = (int)($_POST['max_quantity'] ?? 0);
        $posts       = (int)($_POST['posts'] ?? 0);
        $delay       = (int)($_POST['delay'] ?? 0);

        if ($serviceId <= 0 || empty($username) || $minQuantity <= 0 || $maxQuantity <= 0 || $posts <= 0) {
            $this->flash('error', 'Tous les champs de l\'abonnement sont obligatoires.');
            $this->redirect('/dashboard');
        }

        if ($minQuantity > $maxQuantity) {
            $this->flash('error', 'La quantité minimale ne peut pas être supérieure à la quantité maximale.');
            $this->redirect('/dashboard');
        }

        $serviceModel = new Service();
        $service = $serviceModel->findById($serviceId);

        if (!$service || !$service['is_active']) {
            $this->flash('error', 'Service introuvable ou désactivé.');
            $this->redirect('/dashboard');
        }

        // Calcul du coût maximum possible pour cet abonnement
        $maxCost = round((($service['calculated_rate'] * $maxQuantity) / 1000) * $posts, 4);

        $userModel = new User();
        $user = $userModel->findById((int)Auth::user()['id']);

        if (!$user || (float)$user['balance'] < $maxCost) {
            $this->flash('error', sprintf('Solde insuffisant. Coût estimé : $%.4f', $maxCost));
            $this->redirect('/dashboard');
        }

        $db = Database::getInstance();
        try {
            $db->beginTransaction();

            // 1. Débiter l'utilisateur
            $debited = $userModel->debitBalance((int)$user['id'], $maxCost);
            if (!$debited) {
                throw new \RuntimeException('Échec du débit.');
            }

            // 2. Enregistrer en base localement
            $stmt = $db->prepare("
                INSERT INTO subscriptions (user_id, service_id, username, min_quantity, max_quantity, posts, delay, status, cost)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', ?)
            ");
            $stmt->execute([
                $user['id'],
                $serviceId,
                $username,
                $minQuantity,
                $maxQuantity,
                $posts,
                $delay,
                $maxCost
            ]);
            $subId = $db->lastInsertId();

            // 3. Routage API grossiste
            $apiUrl = $service['api_url'] ?? '';
            $apiKey = $service['api_key'] ?? '';
            
            if (!empty($apiUrl) && !empty($apiKey) && $apiKey !== SMM_PLACEHOLDER_KEY) {
                $api = new SmmApi($apiKey, $apiUrl);
                
                // Pour les abonnements, on passe des paramètres spécifiques
                $response = $api->order([
                    'service'  => (int)$service['external_service_id'],
                    'username' => $username,
                    'min'      => $minQuantity,
                    'max'      => $maxQuantity,
                    'posts'    => $posts,
                    'delay'    => $delay
                ]);

                if (isset($response['order']) || isset($response['subscription'])) {
                    $extId = $response['subscription'] ?? $response['order'];
                    $upStmt = $db->prepare('UPDATE subscriptions SET external_subscription_id = ? WHERE id = ?');
                    $upStmt->execute([(string)$extId, $subId]);
                } else {
                    throw new \RuntimeException('Erreur API Grossiste: ' . ($response['error'] ?? 'Inconnue'));
                }
            }

            $db->commit();
            Audit::log('create_subscription', "Abonnement #{$subId} créé pour @{$username} (Coût : {$maxCost} USD, Posts : {$posts})");

            // Ajouter les points de cashback/fidélité pour l'abonnement
            try {
                (new \App\Models\Loyalty())->addPointsForOrder((int)$user['id'], (int)$subId, $maxCost);
            } catch (\Throwable $e) {
                // Silencieusement ignorer en cas de table non migrée
            }

            Auth::refreshUser();
            $this->flash('success', "Abonnement créé avec succès pour @{$username} ! Le système suivra automatiquement vos $posts prochaines publications.");

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->flash('error', 'Erreur lors de la création de l\'abonnement : ' . $e->getMessage());
        }

        $this->redirect('/dashboard');
    }
}
