<?php
// ============================================================
// KivuBoost — Script Cron : Synchronisation des statuts de commandes
// Appelé toutes les 15 minutes par un service externe (cron-job.org)
//
// Accès : /cron/sync_orders.php?secret=KivuBoost_Cron_2024!
// PROTÉGER ce fichier — ne pas exposer publiquement sans le token.
// ============================================================

declare(strict_types=1);

// --- Sécurité : token secret obligatoire ---
define('CRON_SECRET', 'KivuBoost_Cron_2024!');

if (($_GET['secret'] ?? '') !== CRON_SECRET) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Accès refusé. Token invalide.']);
    exit;
}

// --- Limite de temps généreuse pour les hébergements mutualisés ---
set_time_limit(120);
ignore_user_abort(true);

// --- Chargement de la configuration ---
require_once __DIR__ . '/../config/config.php';

// --- Réponse JSON ---
header('Content-Type: application/json; charset=UTF-8');

// Charger les classes nécessaires manuellement (pas de session requise)
use App\Core\Database;
use App\Models\User;
use App\Models\Order;
use App\Models\Loyalty;
use App\Core\Audit;
use App\Services\SmmApi;

try {
    $db        = Database::getInstance();
    $userModel = new User();

    // 1. Récupérer toutes les commandes "Processing"
    $stmt = $db->query("
        SELECT o.id, o.external_order_id, o.cost, o.quantity, o.user_id, o.status,
               s.name AS service_name, p.api_url, p.api_key, p.name AS provider_name
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
    $processingOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($processingOrders)) {
        echo json_encode([
            'success'    => true,
            'timestamp'  => date('Y-m-d H:i:s'),
            'message'    => 'Aucune commande en cours à vérifier.',
            'stats'      => ['checked' => 0, 'completed' => 0, 'canceled' => 0, 'partial' => 0, 'refunded_total' => 0]
        ]);
        exit;
    }

    // 2. Regrouper par fournisseur pour minimiser les appels API
    $grouped = [];
    foreach ($processingOrders as $order) {
        $key = $order['api_url'] . '|' . $order['api_key'];
        $grouped[$key][] = $order;
    }

    $stats = [
        'checked'        => 0,
        'completed'      => 0,
        'canceled'       => 0,
        'partial'        => 0,
        'refunded_total' => 0.0,
        'errors'         => []
    ];

    foreach ($grouped as $providerKey => $orders) {
        [$apiUrl, $apiKey] = explode('|', $providerKey, 2);

        try {
            $api        = new SmmApi($apiKey, $apiUrl);
            $externalIds = array_column($orders, 'external_order_id');
            $statusResponse = $api->multiStatus($externalIds);

            if (!is_array($statusResponse)) {
                $stats['errors'][] = "Réponse invalide : " . ($orders[0]['provider_name'] ?? $apiUrl);
                continue;
            }

            foreach ($orders as $order) {
                $stats['checked']++;
                $extId     = $order['external_order_id'];
                $orderData = $statusResponse[$extId] ?? null;

                if (!$orderData || !isset($orderData['status'])) {
                    continue;
                }

                $apiStatus    = strtolower(trim($orderData['status']));
                $newStatus    = null;
                $refundAmount = 0.0;

                if (in_array($apiStatus, ['completed', 'complete'])) {
                    $newStatus = 'Completed';
                    $stats['completed']++;

                } elseif (in_array($apiStatus, ['canceled', 'cancelled'])) {
                    $newStatus    = 'Canceled';
                    $refundAmount = (float)$order['cost'];
                    $stats['canceled']++;

                } elseif ($apiStatus === 'partial') {
                    $newStatus = 'Partial';
                    $remains   = (int)($orderData['remains'] ?? 0);
                    $totalQty  = (int)$order['quantity'];
                    if ($totalQty > 0 && $remains > 0) {
                        $refundAmount = round(((float)$order['cost'] * $remains) / $totalQty, 4);
                    }
                    $stats['partial']++;
                }

                if ($newStatus !== null) {
                    // Mettre à jour le statut
                    $upStmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
                    $upStmt->execute([$newStatus, $order['id']]);

                    if ($newStatus === 'Completed') {
                        // Recalcul palier fidélité
                        try {
                            (new Loyalty())->recalculateTierIfNeeded((int)$order['user_id']);
                        } catch (\Throwable $e) {}

                        // Email notification client : commande terminée
                        try {
                            $clientUser = $userModel->findById((int)$order['user_id']);
                            if ($clientUser && !empty($clientUser['email'])) {
                                @sendKivuBoostMail(
                                    $clientUser['email'],
                                    "✅ Commande #{$order['id']} terminée — KivuBoost",
                                    'order_completed',
                                    [
                                        'username'    => $clientUser['username'] ?? 'Client',
                                        'orderId'     => str_pad((string)$order['id'], 5, '0', STR_PAD_LEFT),
                                        'serviceName' => $order['service_name'] ?? 'Votre service commandé',
                                        'quantity'    => number_format((int)$order['quantity'], 0, ',', ' '),
                                        'cost'        => number_format((float)$order['cost'], 4),
                                        'dashboardUrl'=> APP_URL . '/history',
                                    ]
                                );
                            }
                        } catch (\Throwable $e) {}
                    }

                    // Remboursement si nécessaire
                    if ($refundAmount > 0) {
                        $userModel->adjustBalance((int)$order['user_id'], $refundAmount);
                        $stats['refunded_total'] += $refundAmount;

                        Audit::log('cron_auto_refund', sprintf(
                            '[CRON] Remboursement $%.4f pour commande #%d (%s)',
                            $refundAmount, $order['id'], $newStatus
                        ));
                    }

                } elseif (!empty($orderData['error'])) {
                    $errorPayload = json_encode([
                        'timestamp' => date('Y-m-d H:i:s'),
                        'provider'  => $orders[0]['provider_name'] ?? 'inconnu',
                        'error'     => $orderData['error'],
                    ], JSON_UNESCAPED_UNICODE);
                    (new Order())->logApiError((int)$order['id'], $errorPayload);
                }
            }

        } catch (\Throwable $e) {
            $stats['errors'][] = "Erreur fournisseur (" . ($orders[0]['provider_name'] ?? $apiUrl) . ") : " . $e->getMessage();
        }
    }

    // --- DB MAINTENANCE AUTOMATIQUE ---
    // 1. Purge des logs d'audit datant de plus de 90 jours
    try {
        Audit::purgeOld(90);
    } catch (\Throwable $e) {}

    // 2. Purge des tentatives de connexion de plus de 24h
    try {
        (new \App\Core\RateLimiter())->purgeOldAttempts();
    } catch (\Throwable $e) {}

    Audit::log('cron_sync', sprintf(
        '[CRON] Sync statuts : %d vérifiées, %d complétées, %d annulées, %d partielles, $%.4f remboursés.',
        $stats['checked'], $stats['completed'], $stats['canceled'], $stats['partial'], $stats['refunded_total']
    ));

    echo json_encode([
        'success'   => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'message'   => sprintf(
            '%d vérifiées → %d complétées, %d annulées, %d partielles. $%.4f remboursés.',
            $stats['checked'], $stats['completed'], $stats['canceled'], $stats['partial'], $stats['refunded_total']
        ),
        'stats'     => $stats
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success'   => false,
        'timestamp' => date('Y-m-d H:i:s'),
        'error'     => $e->getMessage()
    ]);
}
