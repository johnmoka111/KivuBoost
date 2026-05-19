<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Recharge;
use App\Models\Setting;

class RechargeController extends Controller
{
    // -------------------------------------------------------
    // GET /recharge
    // -------------------------------------------------------
    public function index(): void
    {
        Auth::requireLogin();

        $user          = Auth::user();
        $rechargeModel = new Recharge();

        $recharges = $rechargeModel->getByUser((int)$user['id']);

        // Récupération des numéros depuis les settings
        $settings = [
            'mpesa_number'   => Setting::get('mpesa_number',  '+243XXXXXXXXX'),
            'airtel_number'  => Setting::get('airtel_number', '+243XXXXXXXXX'),
            'orange_number'  => Setting::get('orange_number', '+243XXXXXXXXX'),
            'vodacom_number' => Setting::get('vodacom_number','+243XXXXXXXXX'),
            'pawapay_enabled'=> Setting::get('pawapay_enabled', '0'),
            'visapay_enabled'=> Setting::get('visapay_enabled', '0'),
        ];

        $this->render('recharge/index', [
            'user'      => $user,
            'recharges' => $recharges,
            'settings'  => $settings,
        ]);
    }

    // -------------------------------------------------------
    // POST /recharge/submit
    // -------------------------------------------------------
    public function submit(): void
    {
        Auth::requireLogin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/recharge');
        }

        $network       = trim($_POST['network'] ?? '');
        $amount        = (float)($_POST['amount'] ?? 0);
        $transactionId = trim($_POST['transaction_id'] ?? '');

        $allowedNetworks = ['M-Pesa', 'Airtel Money', 'Orange Money', 'Vodacom'];

        if (!in_array($network, $allowedNetworks, true)) {
            $this->flash('error', 'Réseau de paiement invalide.');
            $this->redirect('/recharge');
        }

        if ($amount < 1) {
            $this->flash('error', 'Le montant minimum est de $1 USD.');
            $this->redirect('/recharge');
        }

        if (empty($transactionId) || strlen($transactionId) < 4) {
            $this->flash('error', 'La référence de transaction est invalide.');
            $this->redirect('/recharge');
        }

        $user = Auth::user();
        $rechargeModel = new Recharge();

        // Vérifier que la référence n'est pas déjà soumise
        $existing = $this->findExistingTransaction($transactionId);
        if ($existing) {
            $this->flash('error', 'Cette référence de transaction a déjà été soumise.');
            $this->redirect('/recharge');
        }

        $rechargeModel->create((int)$user['id'], $amount, $network, $transactionId);

        $this->flash('success', 'Votre demande de recharge a été soumise avec succès. Un administrateur la validera sous peu.');
        $this->redirect('/recharge');
    }

    private function findExistingTransaction(string $txId): bool
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM recharges WHERE transaction_id = ? AND status != 'Rejected'"
        );
        $stmt->execute([$txId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
