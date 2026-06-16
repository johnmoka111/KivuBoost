<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Recharge;
use App\Models\Setting;
use App\Core\Audit;

class RechargeController extends Controller
{
    // -------------------------------------------------------
    // GET /recharge
    // -------------------------------------------------------
    public function index(): void
    {
        Auth::requireLogin();

        if (Auth::isAdmin()) {
            $this->flash('error', 'Accès interdit aux administrateurs sur cette page.');
            $this->redirect('/admin');
        }

        $user          = Auth::user();
        $rechargeModel = new Recharge();

        $recharges = $rechargeModel->getByUser((int)$user['id']);

        // Récupération des numéros depuis les settings
        $settings = [
            'mpesa_number'  => Setting::get('mpesa_number',  '+243XXXXXXXXX'),
            'airtel_number' => Setting::get('airtel_number', '+243XXXXXXXXX'),
            'orange_number' => Setting::get('orange_number', '+243XXXXXXXXX'),
            'vodacom_number' => Setting::get('vodacom_number','+243XXXXXXXXX'),
        ];

        // Charger les passerelles actives
        $gatewayModel = new \App\Models\PaymentGateway();
        $activeGateways = $gatewayModel->allActive();

        $this->render('recharge/index', [
            'user'           => $user,
            'recharges'      => $recharges,
            'settings'       => $settings,
            'activeGateways' => $activeGateways,
        ]);
    }

    // -------------------------------------------------------
    // POST /recharge/submit
    // -------------------------------------------------------
    public function submit(): void
    {
        Auth::requireLogin();

        if (Auth::isAdmin()) {
            $this->flash('error', 'Action non autorisée pour les administrateurs.');
            $this->redirect('/admin');
        }

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/recharge');
        }

        $network       = trim($_POST['network'] ?? '');
        $amount        = (float)($_POST['amount'] ?? 0);
        $transactionId = trim($_POST['transaction_id'] ?? '');
        $currency      = strtoupper(trim($_POST['currency'] ?? 'USD'));

        if (!in_array($currency, ['USD', 'CDF'], true)) {
            $currency = 'USD';
        }

        $allowedNetworks = ['M-Pesa', 'Airtel Money', 'Orange Money', 'Vodacom'];

        if (!in_array($network, $allowedNetworks, true)) {
            $this->flash('error', 'Réseau de paiement invalide.');
            $this->redirect('/recharge');
        }

        if ($currency === 'USD' && $amount < 1) {
            $this->flash('error', 'Le montant minimum est de $1 USD.');
            $this->redirect('/recharge');
        }

        if ($currency === 'CDF') {
            $rate   = (float)Setting::get('usd_rate_cdf', '2850');
            $minCdf = round(1 * $rate, 2);
            if ($amount < $minCdf) {
                $this->flash('error', 'Le montant minimum est de ' . number_format($minCdf, 0, ',', ' ') . ' CDF.');
                $this->redirect('/recharge');
            }
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

        $rechargeModel->create((int)$user['id'], $amount, $network, $transactionId, $currency);
        Audit::log('submit_recharge', "Demande de recharge soumise (Montant : {$amount} {$currency}, Réseau : {$network}, Ref : {$transactionId})");

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

    // -------------------------------------------------------
    // POST /recharge/online/initiate — Initier paiement en ligne
    // -------------------------------------------------------
    public function initiateOnline(): void
    {
        Auth::requireLogin();

        if (Auth::isAdmin()) {
            $this->flash('error', 'Action non autorisée pour les administrateurs.');
            $this->redirect('/admin');
        }

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/recharge');
        }

        $gatewayId = trim($_POST['gateway'] ?? '');
        $amount    = (float)($_POST['amount'] ?? 0);
        $currency  = strtoupper(trim($_POST['currency'] ?? 'USD'));

        if (!in_array($currency, ['USD', 'CDF'], true)) {
            $currency = 'USD';
        }

        $gatewayModel = new \App\Models\PaymentGateway();
        $gateway = $gatewayModel->findByIdentifier($gatewayId);

        if (!$gateway || !$gateway['is_active']) {
            $this->flash('error', 'Passerelle de paiement inactive ou introuvable.');
            $this->redirect('/recharge');
        }

        // Vérifier le montant minimum (200 Fc ou équivalent USD)
        if ($currency === 'CDF' && $amount < 200) {
            $this->flash('error', 'Le montant minimum pour un paiement en CDF est de 200 Fc.');
            $this->redirect('/recharge');
        }

        if ($currency === 'USD') {
            $rate = (float)Setting::get('usd_rate_cdf', '2850');
            $equivalentCdf = $amount * $rate;
            if ($equivalentCdf < 200) {
                $minUsd = round(200 / $rate, 2);
                $this->flash('error', "Le montant minimum pour un paiement en USD est de \${$minUsd} USD.");
                $this->redirect('/recharge');
            }
        }

        $user = Auth::user();
        $rechargeModel = new Recharge();

        // 1. Créer la recharge en statut 'Pending'
        $tempRef = 'SESS_' . uniqid() . '_' . time();
        $rechargeId = $rechargeModel->create((int)$user['id'], $amount, $gateway['name'], $tempRef, $currency);

        if (!$rechargeId) {
            $this->flash('error', 'Erreur lors de la création de la transaction.');
            $this->redirect('/recharge');
        }

        $successUrl  = APP_URL . '/recharge/online/success';
        $cancelUrl   = APP_URL . '/recharge/online/cancel';
        $callbackUrl = APP_URL . '/api/v1/payments/webhook/' . $gateway['identifier'];

        $apiUrl = $gateway['api_url'];
        $privateKey = $gateway['private_key'];

        // Initialisation de la requête HTTP selon la passerelle
        $payload = [];
        if ($gateway['identifier'] === 'bkapay') {
            $payload = [
                'amount'       => (int)round($amount), // Entier requis
                'currency'     => $currency,
                'description'  => "Recharge KivuBoost #" . $rechargeId . " (" . $user['username'] . ")",
                'success_url'  => $successUrl,
                'cancel_url'   => $cancelUrl,
                'callback_url' => $callbackUrl,
                'order_id'     => (string)$rechargeId,
                'expires_in'   => 30
            ];
        } else {
            // Stub générique pour d'autres passerelles (ex: PawaPay / VisaPay)
            $payload = [
                'amount'      => $amount,
                'currency'    => $currency,
                'reference'   => (string)$rechargeId,
                'redirectUrl' => $successUrl,
                'callbackUrl' => $callbackUrl
            ];
        }

        // Appel cURL
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $privateKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || ($httpCode !== 200 && $httpCode !== 201)) {
            Audit::log('gateway_error', "Échec de création de session {$gateway['name']} (HTTP : {$httpCode}, Erreur cURL : {$curlError}, Réponse : {$response})");
            $this->flash('error', "Impossible d'initialiser la session de paiement avec {$gateway['name']}.");
            $this->redirect('/recharge');
        }

        $resData = json_decode($response, true);

        // Récupération de l'URL et du session_id
        $paymentUrl = '';
        $sessionId = '';

        if ($gateway['identifier'] === 'bkapay') {
            if (!empty($resData['success']) && !empty($resData['payment_url'])) {
                $paymentUrl = $resData['payment_url'];
                $sessionId  = $resData['session_id'] ?? $tempRef;
            }
        } else {
            // Parser générique
            $paymentUrl = $resData['payment_url'] ?? $resData['paymentUrl'] ?? '';
            $sessionId  = $resData['session_id'] ?? $resData['id'] ?? $tempRef;
        }

        if (empty($paymentUrl)) {
            Audit::log('gateway_error', "Réponse invalide de {$gateway['name']} (Réponse : {$response})");
            $this->flash('error', "La passerelle {$gateway['name']} a retourné une réponse invalide.");
            $this->redirect('/recharge');
        }

        // Mettre à jour la recharge avec le session_id réel de la passerelle
        $rechargeModel->updateStatus($rechargeId, 'Pending', "Session ID: {$sessionId}");
        
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("UPDATE recharges SET transaction_id = ? WHERE id = ?");
        $stmt->execute([$sessionId, $rechargeId]);

        header('Location: ' . $paymentUrl);
        exit;
    }

    // -------------------------------------------------------
    // GET /recharge/online/success
    // -------------------------------------------------------
    public function onlineSuccess(): void
    {
        Auth::requireLogin();
        $this->flash('success', 'Votre paiement en ligne a été initié et est en cours de traitement. Votre solde sera mis à jour automatiquement dès validation.');
        $this->redirect('/recharge');
    }

    // -------------------------------------------------------
    // GET /recharge/online/cancel
    // -------------------------------------------------------
    public function onlineCancel(): void
    {
        Auth::requireLogin();
        $this->flash('error', 'Le paiement en ligne a été annulé par l\'utilisateur.');
        $this->redirect('/recharge');
    }

    // -------------------------------------------------------
    // POST /api/v1/payments/webhook/:gateway
    // -------------------------------------------------------
    public function webhook(array $params = []): void
    {
        $gatewayId = $params['gateway'] ?? '';
        
        $gatewayModel = new \App\Models\PaymentGateway();
        $gateway = $gatewayModel->findByIdentifier($gatewayId);

        if (!$gateway) {
            http_response_code(404);
            echo json_encode(['error' => 'Gateway not found']);
            exit;
        }

        $payload = file_get_contents('php://input');
        $receivedSignature = $_SERVER['HTTP_X_BKAPAY_SIGNATURE'] ?? $_SERVER['HTTP_X_SIGNATURE'] ?? '';

        // Tenter de trouver dans les headers bruts
        if (empty($receivedSignature) && function_exists('getallheaders')) {
            $headers = getallheaders();
            foreach ($headers as $name => $value) {
                if (strtolower($name) === 'x-bkapay-signature') {
                    $receivedSignature = $value;
                    break;
                }
            }
        }

        // Vérification de la signature HMAC-SHA256
        $webhookSecret = $gateway['signature_secret'];
        $expectedHash = hash_hmac('sha256', $payload, $webhookSecret);

        if (!hash_equals($expectedHash, $receivedSignature)) {
            Audit::log('gateway_error', "Signature Webhook invalide pour {$gateway['name']} (Reçu : {$receivedSignature}, Attendu : {$expectedHash})");
            http_response_code(401);
            echo json_encode(['error' => 'Invalid signature']);
            exit;
        }

        $event = json_decode($payload, true);
        if (empty($event['type']) || $event['type'] !== 'payment.completed') {
            http_response_code(200);
            echo json_encode(['received' => true, 'message' => 'Ignored event type']);
            exit;
        }

        $orderId = (int)($event['data']['order_id'] ?? 0);
        $transactionId = trim($event['data']['transactionId'] ?? '');

        $rechargeModel = new Recharge();
        $recharge = $rechargeModel->findById($orderId);

        if (!$recharge) {
            Audit::log('gateway_error', "Recharge #{$orderId} non trouvée dans la base de données lors du webhook.");
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            exit;
        }

        // Idempotence : Si déjà approuvé
        if ($recharge['status'] !== 'Pending') {
            http_response_code(200);
            echo json_encode(['received' => true, 'message' => 'Already processed']);
            exit;
        }

        $db = \App\Core\Database::getInstance();
        try {
            $db->beginTransaction();

            $userModel = new \App\Models\User();

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

            // Mettre à jour le statut
            $rechargeModel->updateStatus($orderId, 'Approved', "Payé automatiquement via {$gateway['name']} Webhook. Réf: {$transactionId}");
            
            // Mettre à jour le transaction_id avec le transactionId réel de paiement
            $stmt = $db->prepare("UPDATE recharges SET transaction_id = ? WHERE id = ?");
            $stmt->execute([$transactionId, $orderId]);

            Audit::log('approve_recharge', "Recharge #{$orderId} approuvée automatiquement via Webhook (Montant : {$currencyLabel}, Utilisateur : {$recharge['username']})");

            // Récupérer les infos utilisateur pour le SMTP
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
            if (function_exists('sendKivuBoostMail')) {
                sendKivuBoostMail($recharge['email'], "Fonds crédités avec succès sur KivuBoost !", "admin_recharges", [
                    'username'   => $recharge['username'],
                    'amount'     => (float)$recharge['amount'],
                    'currency'   => $rechargeCurrency,
                    'smsToken'   => $transactionId,
                    'newBalance' => $newBalanceFormatted,
                    'historyUrl' => $historyUrl
                ]);
            }

            $db->commit();
            
            // Recharger la session utilisateur si connectée
            if (Auth::user() && (int)Auth::user()['id'] === (int)$recharge['user_id']) {
                Auth::refreshUser();
            }

        } catch (\Throwable $e) {
            $db->rollBack();
            Audit::log('gateway_error', "Erreur transactionnelle dans le webhook {$gateway['name']} : " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        http_response_code(200);
        echo json_encode(['received' => true]);
        exit;
    }
}
