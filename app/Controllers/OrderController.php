<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Service;
use App\Models\Order;
use App\Models\User;
use App\Services\SmmApi;

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

        // --- Calcul du coût dynamique basé sur le selling_price local ---
        $cost = round(($service['selling_price'] * $quantity) / 1000, 4);

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

        } catch (\Throwable $e) {
            $db->rollBack();
            $this->flash('error', 'Erreur lors de la création de la commande. Veuillez réessayer.');
            $this->redirect('/dashboard');
        }

        // --- Envoi vers l'API du fournisseur spécifique (Routage dynamique) ---
        $apiUrl = $service['api_url'] ?? '';
        $apiKey = $service['api_key'] ?? '';

        if (!empty($apiUrl) && !empty($apiKey) && $apiKey !== 'CLE_SECRETE_SMM_FOLLOWS') {
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

        $this->flash('success', sprintf(
            'Commande #%d passée avec succès ! Coût débité : $%.4f',
            $orderId,
            $cost
        ));
        $this->redirect('/dashboard');
    }
}
