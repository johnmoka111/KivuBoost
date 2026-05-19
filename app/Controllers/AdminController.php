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
            'total_deposited'   => $rechargeModel->totalApproved(),
            'total_orders'      => $orderModel->countByStatus('Processing')
                                 + $orderModel->countByStatus('Completed'),
            'total_revenue'     => $orderModel->totalRevenue(),
        ];

        // Solde fournisseur (Récupération dynamique du premier fournisseur actif)
        $providerBalance = ['balance' => '0.00', 'currency' => 'USD'];
        $providers = $providerModel->all();
        $activeProvider = null;
        foreach ($providers as $prov) {
            if ($prov['status'] == 1 && $prov['api_key'] !== 'CLE_SECRETE_SMM_FOLLOWS') {
                $activeProvider = $prov;
                break;
            }
        }

        if ($activeProvider) {
            $providerBalance = $this->fetchProviderBalance(
                $activeProvider['api_key'],
                $activeProvider['api_url']
            );
        }

        $pendingRecharges = $rechargeModel->getPending();
        $recentOrders     = $orderModel->getAll(20);
        $allUsers         = $userModel->all();
        $allSettings      = $settingModel->toArray();
        $allServices      = $serviceModel->allForAdmin();

        $this->render('admin/index', [
            'user'             => Auth::user(),
            'stats'            => $stats,
            'providerBalance'  => $providerBalance,
            'pendingRecharges' => $pendingRecharges,
            'recentOrders'     => $recentOrders,
            'allUsers'         => $allUsers,
            'allSettings'      => $allSettings,
            'allServices'      => $allServices,
            'allProviders'     => $providers,
        ]);
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
            $userModel->creditBalance((int)$recharge['user_id'], (float)$recharge['amount']);
            $rechargeModel->updateStatus($rechargeId, 'Approved', 'Approuvé par ' . Auth::user()['username']);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->flash('error', 'Erreur lors de l\'approbation. Veuillez réessayer.');
            $this->redirect('/admin');
        }

        Auth::refreshUser();

        $this->flash('success', sprintf(
            'Recharge #%d de $%.2f approuvée — Compte de %s crédité.',
            $rechargeId,
            $recharge['amount'],
            $recharge['username']
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

        $this->flash('success', 'Recharge #' . $rechargeId . ' rejetée.');
        $this->redirect('/admin');
    }

    // -------------------------------------------------------
    // POST /admin/settings/update — Paramètres globaux
    // -------------------------------------------------------
    public function updateSettings(): void
    {
        Auth::requireSuperAdmin();

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

        $this->flash('success', 'Paramètres mis à jour avec succès.');
        $this->redirect('/admin');
    }

    // -------------------------------------------------------
    // POST /admin/providers/save — Ajouter ou modifier un grossiste SMM
    // -------------------------------------------------------
    public function saveProvider(): void
    {
        Auth::requireSuperAdmin();

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
        Auth::requireSuperAdmin();

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
            'provider_id'         => (int)($_POST['provider_id'] ?? 0),
            'external_service_id' => (int)($_POST['external_service_id'] ?? 0),
            'category'            => trim($_POST['category'] ?? ''),
            'name'                => trim($_POST['name'] ?? ''),
            'min_quantity'        => (int)($_POST['min_quantity'] ?? 10),
            'max_quantity'        => (int)($_POST['max_quantity'] ?? 10000),
            'buying_price'        => (float)($_POST['buying_price'] ?? 0.0),
            'selling_price'       => (float)($_POST['selling_price'] ?? 0.0),
            'is_active'           => isset($_POST['is_active']) ? 1 : 0,
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
        $sellingPrice = (float)($_POST['selling_price'] ?? 0.00);

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

        if (!$provider || $provider['api_key'] === 'CLE_SECRETE_SMM_FOLLOWS') {
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
            $markup = (float)Setting::get('markup_percentage', '20');

            // Récupérer les services existants pour ce fournisseur pour mise à jour/évitement doublons
            $db = Database::getInstance();
            $stmt = $db->prepare('SELECT id, external_service_id FROM services WHERE provider_id = ?');
            $stmt->execute([$providerId]);
            $existing = [];
            while ($row = $stmt->fetch()) {
                $existing[$row['external_service_id']] = $row['id'];
            }

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

                // Tarification dynamique : prix de vente = prix d'achat + markup %
                $sellingPrice = round($rate * (1 + $markup / 100), 4);

                $data = [
                    'provider_id'         => $providerId,
                    'external_service_id' => $extId,
                    'category'            => $category,
                    'name'                => $name,
                    'buying_price'        => $rate,
                    'selling_price'       => $sellingPrice,
                    'min_quantity'        => $min,
                    'max_quantity'        => $max,
                    'is_active'           => 1
                ];

                if (isset($existing[$extId])) {
                    $serviceModel->update($existing[$extId], $data);
                } else {
                    $serviceModel->create($data);
                }
                $countSynced++;
            }

            $this->flash('success', sprintf(
                'Synchronisation réussie ! %d services mis à jour ou créés depuis le catalogue de %s (Marge de +%g%% appliquée).',
                $countSynced,
                htmlspecialchars($provider['name']),
                $markup
            ));

        } catch (\Throwable $e) {
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
    // POST /admin/users/balance
    // -------------------------------------------------------
    public function adjustBalance(): void
    {
        Auth::requireSuperAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin');
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);

        if ($userId <= 0) {
            $this->flash('error', 'Utilisateur invalide.');
            $this->redirect('/admin');
        }

        (new User())->adjustBalance($userId, $amount);
        Auth::refreshUser();

        $this->flash('success', sprintf('Solde ajusté de %+.2f$ pour l\'utilisateur #%d.', $amount, $userId));
        $this->redirect('/admin');
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
