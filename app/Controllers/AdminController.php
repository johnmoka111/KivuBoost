<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Models\Order;
use App\Models\Recharge;
use App\Models\Service;
use App\Models\Provider;
use App\Models\Setting;
use App\Services\SmmApi;
use App\Core\Audit;

class AdminController extends Controller
{
    // -------------------------------------------------------
    // GET /admin — Tableau de bord admin principal
    // -------------------------------------------------------
    public function index(): void
    {
        Auth::requireAdmin();

        $rechargeModel = new Recharge();
        $orderModel    = new Order();
        $userModel     = new User();
        $settingModel  = new Setting();
        $providerModel = new Provider();
        $serviceModel  = new Service();

        // Statistiques globales
        $stats = [
            'pending_recharges' => $rechargeModel->countPending(),
            'total_deposited'  => $rechargeModel->totalApproved(),
            'total_orders'     => $orderModel->countByStatus('Processing')
                                 + $orderModel->countByStatus('Completed'),
            'total_revenue'    => $orderModel->totalRevenue(),
        ];

        // Les providers actifs
        $providers = $providerModel->all();

        $pendingRecharges = $rechargeModel->getPending();
        $recentOrders     = $orderModel->getAll(200);
        $allUsers         = $userModel->all();
        $allSettings      = $settingModel->toArray();
        $allServices      = $serviceModel->allForAdmin();

        $this->render('admin/index', [
            'user'            => Auth::user(),
            'stats'           => $stats,
            'pendingRecharges' => $pendingRecharges,
            'recentOrders'    => $recentOrders,
            'allUsers'        => $allUsers,
            'allSettings'     => $allSettings,
            'allServices'     => $allServices,
            'allProviders'    => $providers,
        ]);
    }

    // -------------------------------------------------------
    // GET /admin/configuration (Configuration Globale)
    // -------------------------------------------------------
    public function configuration(): void
    {
        Auth::requireAdmin();

        $settingModel  = new Setting();
        $allSettings   = $settingModel->toArray();

        $this->render('admin/configuration', [
            'user'        => Auth::user(),
            'allSettings' => $allSettings,
        ]);
    }

    // -------------------------------------------------------
    // GET /admin/provider-balance (AJAX Asynchrone)
    // -------------------------------------------------------
    public function getProviderBalance(): void
    {
        Auth::requireAdmin();

        $providerModel = new Provider();
        $providers = $providerModel->all();
        $activeProvider = null;
        $providerId = isset($_GET['provider_id']) ? (int)$_GET['provider_id'] : 0;

        if ($providerId > 0) {
            foreach ($providers as $prov) {
                if ($prov['id'] == $providerId) {
                    $activeProvider = $prov;
                    break;
                }
            }
        } else {
            foreach ($providers as $prov) {
                if ($prov['status'] == 1 && $prov['api_key'] !== SMM_PLACEHOLDER_KEY) {
                    $activeProvider = $prov;
                    break;
                }
            }
        }

        $balance = null;
        $name = 'N/A';
        $returnedProviderId = null;

        if ($activeProvider) {
            $name = $activeProvider['name'];
            $returnedProviderId = $activeProvider['id'];
            $fetch = $this->fetchProviderBalance(
                $activeProvider['api_key'],
                $activeProvider['api_url']
            );
            $balance = $fetch['balance'] ?? null;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $balance !== null,
            'balance' => $balance,
            'provider_name' => $name,
            'provider_id' => $returnedProviderId
        ]);
        exit;
    }

    // -------------------------------------------------------
    // POST /admin/recharge/approve
    // -------------------------------------------------------
    public function approveRecharge(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin');
        }

        $rechargeId = (int)($_POST['recharge_id'] ?? 0);

        $rechargeModel = new Recharge();
        $recharge = $rechargeModel->findById($rechargeId);

        if (!$recharge || $recharge['status'] !== 'Pending') {
            $this->flash('error', 'Recharge introuvable ou déjà traitée.');
            $this->redirect('/admin');
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            $userModel = new User();

            // Créditer le solde unique (en USD) avec conversion si nécessaire
            $rechargeCurrency = strtoupper($recharge['currency'] ?? 'USD');
            $creditAmountUsd  = (float)$recharge['amount'];
            if ($rechargeCurrency === 'CDF') {
                $rate = (float)Setting::get('usd_rate_cdf', '2850');
                $creditAmountUsd = round($creditAmountUsd / $rate, 2);
                $currencyLabel = number_format((float)$recharge['amount'], 0, ',', ' ') . ' CDF';
            } else {
                $currencyLabel = '$' . number_format((float)$recharge['amount'], 2);
            }

            $userModel->creditBalance((int)$recharge['user_id'], $creditAmountUsd);

            $rechargeModel->updateStatus($rechargeId, 'Approved', 'Approuvé par ' . Auth::user()['username']);
            Audit::log('approve_recharge', "Recharge #{$rechargeId} approuvée (Montant : {$currencyLabel}, Utilisateur : {$recharge['username']})");

            $updatedUser = $userModel->findById((int)$recharge['user_id']);
            $newBalance = (float)($updatedUser['balance'] ?? 0.00);
            
            if ($rechargeCurrency === 'CDF') {
                $rate = (float)Setting::get('usd_rate_cdf', '2850');
                $newBalanceFormatted = $newBalance * $rate;
            } else {
                $newBalanceFormatted = $newBalance;
            }

            // Déclencheur SMTP : Validation de Portefeuille
            $historyUrl = APP_URL . '/history';
            sendKivuBoostMail($recharge['email'], "Fonds crédités avec succès sur KivuBoost !", "admin_recharges", [
                'username'   => $recharge['username'],
                'amount'     => (float)$recharge['amount'],
                'currency'   => $rechargeCurrency,
                'smsToken'   => $recharge['transaction_id'],
                'newBalance' => $newBalanceFormatted,
                'historyUrl' => $historyUrl
            ]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->flash('error', 'Erreur lors de l\'approbation. Veuillez réessayer.');
            $this->redirect('/admin');
        }

        Auth::refreshUser();

        $this->flash('success', sprintf(
            'Recharge #%d de %s approuvée — Compte de %s crédité de $%s USD.',
            $rechargeId,
            $currencyLabel,
            $recharge['username'],
            number_format($creditAmountUsd, 2)
        ));
        $this->redirect('/admin');
    }

    // -------------------------------------------------------
    // POST /admin/recharge/reject
    // -------------------------------------------------------
    public function rejectRecharge(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin');
        }

        $rechargeId = (int)($_POST['recharge_id'] ?? 0);
        $reason     = trim($_POST['reason'] ?? 'Transaction non vérifiée.');

        $rechargeModel = new Recharge();
        $recharge = $rechargeModel->findById($rechargeId);

        if (!$recharge || $recharge['status'] !== 'Pending') {
            $this->flash('error', 'Recharge introuvable ou déjà traitée.');
            $this->redirect('/admin');
        }

        $rechargeModel->updateStatus($rechargeId, 'Rejected', $reason);
        Audit::log('reject_recharge', "Recharge #{$rechargeId} rejetée (Raison : {$reason}, Utilisateur : {$recharge['username']})");

        $this->flash('success', 'Recharge #' . $rechargeId . ' rejetée.');
        $this->redirect('/admin');
    }

    // -------------------------------------------------------
    // POST /admin/settings/update — Paramètres globaux
    // -------------------------------------------------------
    public function updateSettings(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin');
        }

        $settingModel = new Setting();

        $allowed = [
            'site_name', 'markup_percentage', 'usd_rate_cdf',
            'mpesa_number', 'airtel_number', 'orange_number', 'vodacom_number',
            'pawapay_enabled', 'pawapay_api_key', 'pawapay_secret',
            'visapay_enabled', 'visapay_api_key', 'visapay_secret',
        ];

        $data = [];
        foreach ($allowed as $key) {
            if (isset($_POST[$key])) {
                $data[$key] = trim($_POST[$key]);
            }
        }

        foreach (['pawapay_enabled', 'visapay_enabled'] as $toggle) {
            $data[$toggle] = isset($_POST[$toggle]) ? '1' : '0';
        }

        $settingModel->setMany($data);
        Audit::log('update_settings', "Paramètres globaux mis à jour : " . json_encode($data));

        $this->flash('success', 'Paramètres mis à jour avec succès.');
        $this->redirect('/admin/configuration');
    }

    // -------------------------------------------------------
    // POST /admin/providers/save — Ajouter ou modifier un grossiste SMM
    // -------------------------------------------------------
    public function saveProvider(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin');
        }

        $id      = (int)($_POST['id'] ?? 0);
        $name    = trim($_POST['name'] ?? '');
        $apiUrl  = trim($_POST['api_url'] ?? '');
        $apiKey  = trim($_POST['api_key'] ?? '');
        $status  = isset($_POST['status']) ? 1 : 0;

        if (empty($name) || empty($apiUrl) || empty($apiKey)) {
            $this->flash('error', 'Tous les champs du fournisseur sont obligatoires.');
            $this->redirect('/admin');
        }

        $providerModel = new Provider();

        if ($id > 0) {
            $providerModel->update($id, $name, $apiUrl, $apiKey, $status);
            $this->flash('success', 'Fournisseur SMM "' . htmlspecialchars($name) . '" mis à jour.');
        } else {
            $providerModel->create($name, $apiUrl, $apiKey, $status);
            $this->flash('success', 'Fournisseur SMM "' . htmlspecialchars($name) . '" ajouté avec succès.');
        }

        $this->redirect('/admin');
    }

    // -------------------------------------------------------
    // POST /admin/providers/delete
    // -------------------------------------------------------
    public function deleteProvider(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin');
        }

        $id = (int)($_POST['id'] ?? 0);
        (new Provider())->delete($id);

        $this->flash('success', 'Fournisseur supprimé.');
        $this->redirect('/admin');
    }

    // -------------------------------------------------------
    // POST /admin/services/save
    // -------------------------------------------------------
    public function saveService(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin');
        }

        $serviceModel = new Service();
        $id = (int)($_POST['id'] ?? 0);

        $data = [
            'provider_id'        => (int)($_POST['provider_id'] ?? 0),
            'external_service_id' => (int)($_POST['external_service_id'] ?? 0),
            'category'           => trim($_POST['category'] ?? ''),
            'name'               => trim($_POST['name'] ?? ''),
            'min_quantity'       => (int)($_POST['min_quantity'] ?? 10),
            'max_quantity'       => (int)($_POST['max_quantity'] ?? 10000),
            'original_rate'       => (float)($_POST['original_rate'] ?? 0.0),
            'calculated_rate'      => (float)($_POST['calculated_rate'] ?? 0.0),
            'is_active'          => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($data['provider_id'] <= 0 || $data['external_service_id'] <= 0) {
            $this->flash('error', 'Veuillez lier un grossiste et un ID de service valide.');
            $this->redirect('/admin');
        }

        if ($id > 0) {
            $serviceModel->update($id, $data);
            $this->flash('success', 'Service mis à jour.');
        } else {
            $serviceModel->create($data);
            $this->flash('success', 'Service créé avec succès.');
        }

        $this->redirect('/admin');
    }

    // -------------------------------------------------------
    // POST /admin/services/update-price (Gestion rapide des tarifs locaux)
    // -------------------------------------------------------
    public function updateServicePrice(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin');
        }

        $id           = (int)($_POST['id'] ?? 0);
        $sellingPrice = (float)($_POST['calculated_rate'] ?? 0.00);

        if ($id <= 0 || $sellingPrice <= 0) {
            $this->flash('error', 'Valeurs tarifaires invalides.');
            $this->redirect('/admin');
        }

        $serviceModel = new Service();
        $serviceModel->updatePrice($id, $sellingPrice);

        $this->flash('success', 'Tarif mis à jour avec succès.');
        $this->redirect('/admin');
    }

    // -------------------------------------------------------
    // GET /admin/settings (Gestion des marges par fournisseur)
    // -------------------------------------------------------
    public function settings(): void
    {
        Auth::requireAdmin();
        $providerModel = new Provider();
        $providers = $providerModel->all();

        $this->render('admin/settings', [
            'user' => Auth::user(),
            'providers' => $providers
        ]);
    }

    // -------------------------------------------------------
    // POST /admin/settings/update-margins
    // -------------------------------------------------------
    public function updateMargins(): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf()) {
            $this->abort(403, 'Token CSRF invalide.');
        }

        $margins = $_POST['margins'] ?? [];
        $db = Database::getInstance();

        try {
            $db->beginTransaction();
            $stmtProvider = $db->prepare('UPDATE providers SET markup_percentage = ? WHERE id = ?');
            $stmtServices = $db->prepare('UPDATE services SET calculated_rate = ROUND(original_rate * (1 + (? / 100)), 4) WHERE provider_id = ?');
            
            foreach ($margins as $providerId => $percentage) {
                $markup = (int)$percentage;
                $pId = (int)$providerId;
                
                // 1. Mettre à jour la marge du grossiste
                $stmtProvider->execute([$markup, $pId]);
                
                // 2. Mettre à jour instantanément les tarifs calculés de tous les services de ce grossiste
                $stmtServices->execute([$markup, $pId]);
            }
            $db->commit();
            
            $this->flash('success', 'Les marges et les tarifs des services associés ont été mis à jour instantanément.');
        } catch (\Exception $e) {
            $db->rollBack();
            $this->flash('error', 'Erreur lors de la mise à jour des marges.');
        }

        $this->redirect('/admin/settings');
    }

    // -------------------------------------------------------
    // POST /admin/services/sync (Synchronisation automatique SMM API)
    // -------------------------------------------------------
    public function syncServices(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin');
        }

        $providerId = (int)($_POST['provider_id'] ?? 0);
        if ($providerId <= 0) {
            $this->flash('error', 'Veuillez sélectionner un fournisseur.');
            $this->redirect('/admin');
        }

        $providerModel = new Provider();
        $provider = $providerModel->findById($providerId);

        if (!$provider || $provider['api_key'] === SMM_PLACEHOLDER_KEY) {
            $this->flash('error', 'Fournisseur SMM introuvable ou clé API non configurée.');
            $this->redirect('/admin');
        }

        try {
            $api = new SmmApi($provider['api_key'], $provider['api_url']);
            $externalServices = $api->getServices();

            if (isset($externalServices['error'])) {
                throw new \RuntimeException($externalServices['error']);
            }

            if (!is_array($externalServices)) {
                throw new \RuntimeException('Le format de la réponse API fournisseur est invalide.');
            }

            $serviceModel = new Service();
            $markup = (float)$provider['markup_percentage'];

            // Récupérer les services existants pour ce fournisseur pour mise à jour/évitement doublons
            $db = Database::getInstance();
            $stmt = $db->prepare('SELECT id, external_service_id, is_active FROM services WHERE provider_id = ?');
            $stmt->execute([$providerId]);
            $existing = [];
            while ($row = $stmt->fetch()) {
                $existing[$row['external_service_id']] = [
                    'id'        => $row['id'],
                    'is_active' => $row['is_active']
                ];
            }

            // Début de la transaction unique pour des performances 100x plus rapides
            $db->beginTransaction();
            $countSynced = 0;
            
            foreach ($externalServices as $svc) {
                // SMM Panel Standard Keys: service, name, category, rate, min, max
                $extId    = (int)($svc['service'] ?? 0);
                $name     = trim($svc['name'] ?? '');
                $category = trim($svc['category'] ?? 'Général');
                $rate     = (float)($svc['rate'] ?? 0.0);
                $min      = (int)($svc['min'] ?? 10);
                $max      = (int)($svc['max'] ?? 10000);

                if ($extId <= 0 || empty($name)) continue;

                // Tarification dynamique : prix de vente = prix d'achat * (1 + markup %)
                $calculatedRate = round($rate * (1 + ($markup / 100)), 4);

                // --- AJUSTEMENT INTELLIGENT DU TITRE ---
                // Si le fournisseur inclut le prix d'achat dans le titre (ex: $0.30), cela crée la confusion.
                // On remplace dynamiquement l'ancien prix par le nouveau prix de vente ($calculatedRate) dans le titre.
                if ($rate > 0) {
                    $possibleFormats = [
                        number_format($rate, 4, '.', ''),
                        number_format($rate, 3, '.', ''),
                        number_format($rate, 2, '.', ''),
                        (string)(float)$rate
                    ];
                    $possibleFormats = array_unique($possibleFormats);
                    usort($possibleFormats, function($a, $b) { return strlen($b) - strlen($a); });
                    
                    $calcStr = (float)$calculatedRate == floor($calculatedRate) ? (string)(int)$calculatedRate : rtrim(rtrim(number_format($calculatedRate, 4, '.', ''), '0'), '.');
                    
                    foreach ($possibleFormats as $rf) {
                        $name = preg_replace_callback('/\$' . preg_quote($rf, '/') . '(?!\d)/', function() use ($calcStr) { return '$' . $calcStr; }, $name);
                        $name = preg_replace_callback('/(?<!\d)' . preg_quote($rf, '/') . '\$/', function() use ($calcStr) { return $calcStr . '$'; }, $name);
                        $name = preg_replace_callback('/([\s\-\|]+)' . preg_quote($rf, '/') . '(?!\d)$/', function($m) use ($calcStr) { return $m[1] . $calcStr; }, $name);
                    }
                }

                $data = [
                    'provider_id'        => $providerId,
                    'external_service_id' => $extId,
                    'category'           => $category,
                    'name'               => $name,
                    'original_rate'       => $rate,
                    'calculated_rate'      => $calculatedRate,
                    'min_quantity'       => $min,
                    'max_quantity'       => $max,
                    'is_active'          => 1
                ];

                if (isset($existing[$extId])) {
                    // Préserver l'état (actif ou désactivé) existant lors de la mise à jour
                    $data['is_active'] = $existing[$extId]['is_active'];
                    $serviceModel->update($existing[$extId]['id'], $data);
                } else {
                    $serviceModel->create($data);
                }
                $countSynced++;
            }

            // Validation de la transaction
            $db->commit();

            $this->flash('success', sprintf(
                'Synchronisation réussie ! %d services mis à jour ou créés depuis le catalogue de %s (Marge de +%g%% appliquée).',
                $countSynced,
                htmlspecialchars($provider['name']),
                $markup
            ));

        } catch (\Throwable $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $this->flash('error', 'Erreur de synchronisation : ' . $e->getMessage());
        }

        $this->redirect('/admin');
    }

    // -------------------------------------------------------
    // POST /admin/services/delete
    // -------------------------------------------------------
    public function deleteService(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin');
        }

        $id = (int)($_POST['id'] ?? 0);
        (new Service())->delete($id);

        $this->flash('success', 'Service supprimé.');
        $this->redirect('/admin');
    }

    // -------------------------------------------------------
    // POST /admin/services/toggle-status  (AJAX)
    // Corps attendu : { id, is_active, _token }
    // Retourne JSON { success, id, is_active }
    // -------------------------------------------------------
    public function toggleServiceStatus(): void
    {
        Auth::requireAdmin();

        // Accepter le token CSRF aussi bien en POST qu'en header X-CSRF-TOKEN
        $token = $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!Auth::verifyCsrfToken($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide.']);
            exit;
        }

        $id       = (int)($_POST['id'] ?? 0);
        $isActive = (int)($_POST['is_active'] ?? 0); // 0 ou 1

        if ($id <= 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'ID invalide.']);
            exit;
        }

        $db   = Database::getInstance();
        $stmt = $db->prepare('UPDATE services SET is_active = ? WHERE id = ?');
        $stmt->execute([$isActive ? 1 : 0, $id]);

        $label = $isActive ? 'visible' : 'invisible';
        Audit::log('toggle_service', "Service #{$id} marqué {$label} par " . Auth::user()['username']);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $id, 'is_active' => $isActive]);
        exit;
    }

    // -------------------------------------------------------
    // POST /admin/services/bulk-toggle  (AJAX)
    // Corps attendu : { ids[] (array d'entiers), is_active, _token }
    // Retourne JSON { success, affected }
    // -------------------------------------------------------
    public function bulkToggleServices(): void
    {
        Auth::requireAdmin();

        $token = $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!Auth::verifyCsrfToken($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide.']);
            exit;
        }

        $rawIds = $_POST['ids'] ?? [];
        if (is_string($rawIds) && str_starts_with($rawIds, '[')) {
            $rawIds = json_decode($rawIds, true) ?? [];
        }
        $isActive = (int)($_POST['is_active'] ?? 0);

        // Sanitiser tous les IDs
        $ids = array_filter(array_map('intval', (array)$rawIds), fn($v) => $v > 0);

        if (empty($ids)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Aucun service sélectionné.']);
            exit;
        }

        $db          = Database::getInstance();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params       = array_merge([$isActive ? 1 : 0], array_values($ids));
        $stmt         = $db->prepare("UPDATE services SET is_active = ? WHERE id IN ({$placeholders})");
        $stmt->execute($params);

        $label = $isActive ? 'visible' : 'invisible';
        Audit::log('bulk_toggle_services', count($ids) . " services marqués {$label} en lot par " . Auth::user()['username']);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'affected' => $stmt->rowCount(), 'is_active' => $isActive]);
        exit;
    }

    // -------------------------------------------------------
    // POST /admin/services/bulk-delete  (AJAX)
    // Corps attendu : { ids[] (array d'entiers), _token }
    // Retourne JSON { success, affected }
    // -------------------------------------------------------
    public function bulkDeleteServices(): void
    {
        Auth::requireAdmin();

        $token = $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!Auth::verifyCsrfToken($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide.']);
            exit;
        }

        $rawIds = $_POST['ids'] ?? [];
        if (is_string($rawIds) && str_starts_with($rawIds, '[')) {
            $rawIds = json_decode($rawIds, true) ?? [];
        }
        $ids    = array_filter(array_map('intval', (array)$rawIds), fn($v) => $v > 0);

        if (empty($ids)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Aucun service sélectionné.']);
            exit;
        }

        $db           = Database::getInstance();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt         = $db->prepare("DELETE FROM services WHERE id IN ({$placeholders})");
        $stmt->execute(array_values($ids));

        Audit::log('bulk_delete_services', count($ids) . " services supprimés en lot par " . Auth::user()['username']);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'affected' => $stmt->rowCount()]);
        exit;
    }

    // -------------------------------------------------------
    // POST /admin/users/balance
    // -------------------------------------------------------
    public function adjustBalance(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin');
        }

        $userId   = (int)($_POST['user_id'] ?? 0);
        $amount   = (float)($_POST['amount'] ?? 0);
        $currency = strtoupper(trim($_POST['wallet_currency'] ?? 'USD'));

        if ($userId <= 0) {
            $this->flash('error', 'Utilisateur invalide.');
            $this->redirect('/admin');
        }

        $userModel = new User();
        $adjustAmountUsd = $amount;
        if ($currency === 'CDF') {
            $rate = (float)Setting::get('usd_rate_cdf', '2850');
            $adjustAmountUsd = round($amount / $rate, 4);
            $label = number_format($amount, 0, ',', ' ') . ' CDF';
        } else {
            $label = '$' . number_format($amount, 2) . ' USD';
        }

        $userModel->adjustBalance($userId, $adjustAmountUsd);

        Audit::log('adjust_balance', "Ajustement de solde pour Utilisateur #{$userId} (Montant : {$label}, Équivalent USD : {$adjustAmountUsd})");

        Auth::refreshUser();

        $this->flash('success', sprintf('Solde ajusté de %s (équivalent à %+f USD) pour l\'utilisateur #%d.', $label, $adjustAmountUsd, $userId));
        $this->redirect('/admin');
    }

    // -------------------------------------------------------
    // GET /admin/audit — Afficher le journal d'audit
    // -------------------------------------------------------
    public function audit(): void
    {
        Auth::requireAdmin();

        $logs = Audit::getAll();

        $this->render('admin/audit', [
            'user' => Auth::user(),
            'logs' => $logs
        ]);
    }

    // -------------------------------------------------------
    // GET /admin/campaign — Interface de campagne d'emails
    // -------------------------------------------------------
    public function campaignForm(): void
    {
        Auth::requireAdmin();
        $this->render('admin/campaign', [
            'user' => Auth::user()
        ]);
    }

    // -------------------------------------------------------
    // POST /admin/campaign/send — Envoi de la campagne en masse
    // -------------------------------------------------------
    public function sendCampaign(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/admin/campaign');
        }

        $subject    = trim($_POST['subject'] ?? '');
        $title      = trim($_POST['title'] ?? '');
        $content    = trim($_POST['content'] ?? '');
        $actionText = trim($_POST['action_text'] ?? '🚀 Propulser mon audience');
        $actionUrl  = trim($_POST['action_url'] ?? '');

        if (empty($subject) || empty($title) || empty($content)) {
            $this->flash('error', 'Veuillez remplir le sujet, le titre et le contenu de l\'annonce.');
            $this->redirect('/admin/campaign');
        }

        if (empty($actionUrl)) {
            $actionUrl = APP_URL . '/dashboard';
        }

        // Pour éviter l'interruption du script en cas d'envois massifs
        set_time_limit(0);
        ignore_user_abort(true);

        $db = Database::getInstance();
        $stmt = $db->query("SELECT email, username FROM users WHERE email IS NOT NULL AND email != ''");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = count($users);
        $successCount = 0;
        $failCount = 0;

        foreach ($users as $recipient) {
            $data = [
                'username'   => $recipient['username'],
                'title'      => $title,
                'content'    => $content,
                'actionText' => $actionText,
                'actionUrl'  => $actionUrl
            ];

            $result = sendKivuBoostMail($recipient['email'], $subject, 'admin_news', $data);

            if ($result) {
                $successCount++;
            } else {
                $failCount++;
            }

            // Temporisation légère (50ms) pour ménager les serveurs SMTP et les cœurs CPU
            usleep(50000);
        }

        Audit::log('campaign_sent', "Campagne d'emails envoyée : [{$subject}] - Réussis : {$successCount}/{$total}, Échecs : {$failCount}/{$total}");
        
        $this->flash('success', "Campagne envoyée avec succès ! Destinataires touchés : {$successCount} reçus, {$failCount} échecs.");
        $this->redirect('/admin/campaign');
    }

    // -------------------------------------------------------
    // POST /admin/orders/sync-statuses  (AJAX)
    // Interroge les API fournisseurs pour toutes les commandes
    // en statut "Processing" et met à jour les statuts + remboursements.
    // -------------------------------------------------------
    public function syncOrderStatuses(): void
    {
        Auth::requireAdmin();

        $token = $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!Auth::verifyCsrfToken($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide.']);
            exit;
        }

        $db = Database::getInstance();

        // 1. Récupérer toutes les commandes "Processing" avec un external_order_id et les infos du fournisseur
        $stmt = $db->query("
            SELECT o.id, o.external_order_id, o.cost, o.quantity, o.user_id, o.status,
                   p.api_url, p.api_key, p.name AS provider_name
            FROM orders o
            JOIN services s ON s.id = o.service_id
            JOIN providers p ON p.id = s.provider_id
            WHERE o.status = 'Processing'
              AND o.external_order_id IS NOT NULL
              AND o.external_order_id != ''
              AND p.status = 1
              AND p.api_key != '" . SMM_PLACEHOLDER_KEY . "'
            LIMIT 200
        ");
        $processingOrders = $stmt->fetchAll();

        if (empty($processingOrders)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Aucune commande en cours à vérifier.',
                'stats'   => ['checked' => 0, 'completed' => 0, 'canceled' => 0, 'partial' => 0, 'refunded_total' => 0]
            ]);
            exit;
        }

        // 2. Regrouper les commandes par (api_url + api_key) pour minimiser les appels API
        $grouped = [];
        foreach ($processingOrders as $order) {
            $key = $order['api_url'] . '|' . $order['api_key'];
            $grouped[$key][] = $order;
        }

        $stats = ['checked' => 0, 'completed' => 0, 'canceled' => 0, 'partial' => 0, 'refunded_total' => 0.0, 'errors' => []];

        $userModel = new User();

        foreach ($grouped as $providerKey => $orders) {
            [$apiUrl, $apiKey] = explode('|', $providerKey, 2);

            try {
                $api = new SmmApi($apiKey, $apiUrl);

                // Appel multi-statuts (1 seul appel API pour toutes les commandes du fournisseur)
                $externalIds = array_column($orders, 'external_order_id');
                $statusResponse = $api->multiStatus($externalIds);

                if (!is_array($statusResponse)) {
                    $stats['errors'][] = "Réponse invalide pour le fournisseur : " . ($orders[0]['provider_name'] ?? $apiUrl);
                    continue;
                }

                foreach ($orders as $order) {
                    $stats['checked']++;
                    $extId = $order['external_order_id'];

                    // La réponse peut être indexée par l'ID externe
                    $orderData = $statusResponse[$extId] ?? null;

                    if (!$orderData || !isset($orderData['status'])) {
                        continue; // Pas de réponse pour cette commande, on skip
                    }

                    $apiStatus = strtolower(trim($orderData['status']));

                    // Mapper les statuts de l'API vers nos statuts internes
                    $newStatus = null;
                    $refundAmount = 0.0;

                    if (in_array($apiStatus, ['completed', 'complete'])) {
                        $newStatus = 'Completed';
                        $stats['completed']++;

                    } elseif (in_array($apiStatus, ['canceled', 'cancelled'])) {
                        $newStatus = 'Canceled';
                        $refundAmount = (float)$order['cost']; // Remboursement intégral
                        $stats['canceled']++;

                    } elseif ($apiStatus === 'partial') {
                        $newStatus = 'Partial';
                        // Remboursement proportionnel : (quantité non livrée / quantité totale) * coût
                        $remains   = (int)($orderData['remains'] ?? 0);
                        $totalQty  = (int)$order['quantity'];
                        if ($totalQty > 0 && $remains > 0) {
                            $refundAmount = round(((float)$order['cost'] * $remains) / $totalQty, 4);
                        }
                        $stats['partial']++;
                    }
                    // 'processing', 'in progress', 'pending' → on ne fait rien

                    if ($newStatus !== null) {
                        // Mettre à jour le statut en DB
                        $upStmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
                        $upStmt->execute([$newStatus, $order['id']]);

                        // Remboursement si nécessaire
                        if ($refundAmount > 0) {
                            $userModel->adjustBalance((int)$order['user_id'], $refundAmount);
                            $stats['refunded_total'] += $refundAmount;

                            Audit::log('auto_refund', sprintf(
                                'Remboursement automatique de $%.4f pour commande #%d (Statut: %s)',
                                $refundAmount,
                                $order['id'],
                                $newStatus
                            ));
                        }
                    }
                }
            } catch (\Throwable $e) {
                $stats['errors'][] = "Erreur fournisseur (" . ($orders[0]['provider_name'] ?? $apiUrl) . ") : " . $e->getMessage();
            }
        }

        Audit::log('sync_order_statuses', sprintf(
            'Sync statuts : %d vérifiées, %d complétées, %d annulées, %d partielles, $%.4f remboursés.',
            $stats['checked'], $stats['completed'], $stats['canceled'], $stats['partial'], $stats['refunded_total']
        ));

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => sprintf(
                '%d commandes vérifiées → %d complétées, %d annulées, %d partielles. $%.4f remboursés.',
                $stats['checked'], $stats['completed'], $stats['canceled'], $stats['partial'], $stats['refunded_total']
            ),
            'stats' => $stats
        ]);
        exit;
    }

    // -------------------------------------------------------
    // Privé : récupérer le solde fournisseur
    // -------------------------------------------------------
    private function fetchProviderBalance(string $apiKey, string $apiUrl): ?array
    {
        try {
            $api = new SmmApi($apiKey, $apiUrl);
            return $api->getBalance();
        } catch (\Throwable $e) {
            return ['balance' => '0.00', 'currency' => 'USD', 'error' => $e->getMessage()];
        }
    }
}
