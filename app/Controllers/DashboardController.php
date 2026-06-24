<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Currency;
use App\Models\Service;
use App\Models\Order;
use App\Models\Loyalty;

class DashboardController extends Controller
{
    /**
     * GET /dashboard — Espace Client Principal
     */
    public function index(): void
    {
        Auth::requireLogin();

        $serviceModel = new Service();
        $orderModel   = new Order();

        $user = Auth::user();

        // Récupérer les services groupés par catégorie
        $services = $serviceModel->groupedByCategory();

        // Récupérer l'historique des commandes de l'utilisateur connecté
        $orders = $orderModel->getByUser((int)$user['id']);

        // Récupérer les abonnements de l'utilisateur connecté
        $db = \App\Core\Database::getInstance();
        $stmtSub = $db->prepare("
            SELECT sub.*, s.name AS service_name, s.category
            FROM subscriptions sub
            LEFT JOIN services s ON s.id = sub.service_id
            WHERE sub.user_id = ?
            ORDER BY sub.created_at DESC
        ");
        $stmtSub->execute([$user['id']]);
        $subscriptions = $stmtSub->fetchAll();

        $this->render('dashboard/index', [
            'user'         => $user,
            'services'     => $services,
            'orders'       => $orders,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * GET /history — Historique des commandes
     */
    public function history(): void
    {
        Auth::requireLogin();
        $user = Auth::user();
        
        $orderModel = new Order();
        $rechargeModel = new \App\Models\Recharge();
        $db = \App\Core\Database::getInstance();

        // Paramètres de pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 25;
        $offset = ($page - 1) * $limit;

        if (Auth::isAdmin()) {
            $totalOrders = $orderModel->countAll();
            $orders = $orderModel->getAll($limit, $offset);
            $orderStats = $orderModel->getAdminStats();

            $stmtSub = $db->query("
                SELECT sub.*, s.name AS service_name, s.category, u.username AS client_username
                FROM subscriptions sub
                LEFT JOIN services s ON s.id = sub.service_id
                LEFT JOIN users u ON u.id = sub.user_id
                ORDER BY sub.created_at DESC
            ");
            $subscriptions = $stmtSub->fetchAll();
            $recharges = $rechargeModel->getAll(100);
        } else {
            $totalOrders = $orderModel->countByUser((int)$user['id']);
            $orders = $orderModel->getByUser((int)$user['id'], $limit, $offset);
            $orderStats = $orderModel->getUserStats((int)$user['id']);

            $stmtSub = $db->prepare("
                SELECT sub.*, s.name AS service_name, s.category
                FROM subscriptions sub
                LEFT JOIN services s ON s.id = sub.service_id
                WHERE sub.user_id = ?
                ORDER BY sub.created_at DESC
            ");
            $stmtSub->execute([$user['id']]);
            $subscriptions = $stmtSub->fetchAll();
            $recharges = $rechargeModel->getByUser((int)$user['id']);
        }

        $totalPages = (int)ceil($totalOrders / $limit);

        $this->render('client/history', [
            'user'          => $user,
            'orders'        => $orders,
            'subscriptions' => $subscriptions,
            'recharges'     => $recharges,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'totalOrders'   => $totalOrders,
            'orderStats'    => $orderStats,
        ]);
    }

    /**
     * GET /services — Grille des tarifs
     */
    public function services(): void
    {
        Auth::requireLogin();
        $user = Auth::user();

        $serviceModel = new Service();
        $services = $serviceModel->groupedByCategory();

        $this->render('client/services', [
            'user'         => $user,
            'services'     => $services,
        ]);
    }

    /**
     * GET /currency/switch — Basculer la devise d'affichage USD <-> CDF
     */
    public function switchCurrency(): void
    {
        // Nouveau système : ?to=CDF, ?to=RWF, etc.
        $requested = strtoupper(trim($_GET['to'] ?? ''));

        if ($requested && array_key_exists($requested, Currency::all())) {
            Currency::setActive($requested);
        } else {
            // Ancien comportement fallback : bascule USD ↔ CDF
            $current = Currency::getActive();
            Currency::setActive($current === 'USD' ? 'CDF' : 'USD');
        }

        // Si c'est une requête AJAX, retourner JSON
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        if ($isAjax || isset($_GET['to'])) {
            $user = Auth::user();
            $balance = (float)($user['balance'] ?? 0);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success'   => true,
                'currency'  => Currency::getActive(),
                'formatted' => Currency::format($balance),
            ]);
            exit;
        }

        // Sinon : redirection classique
        $referrer = $_SERVER['HTTP_REFERER'] ?? (APP_BASE . '/dashboard');
        header('Location: ' . $referrer);
        exit;
    }

    /**
     * GET /rewards — Espace Programme de Fidélité
     */
    public function rewards(): void
    {
        Auth::requireLogin();
        $user = Auth::user();

        try {
            // Récupérer le solde mis à jour de l'utilisateur
            $userModel = new \App\Models\User();
            $currentUser = $userModel->findById((int)$user['id']);

            $loyaltyModel = new Loyalty();
            $totalSpent = $loyaltyModel->getUserTotalSpent((int)$user['id']);
            $currentTier = $loyaltyModel->getUserTier((int)$user['id']);
            $nextTier = Loyalty::getNextTierForSpent($totalSpent);
            $logs = $loyaltyModel->getLogsByUserId((int)$user['id'], 50);

            // Calculer le pourcentage de progression pour le niveau suivant
            $progressPercent = 0;
            $nextSpentDiff = 0.0;
            if ($nextTier) {
                $currentMin = $currentTier['min_spent'];
                $nextMin = $nextTier['min_spent'];
                $range = $nextMin - $currentMin;
                if ($range > 0) {
                    $progressPercent = min(100, max(0, (int)round((($totalSpent - $currentMin) / $range) * 100)));
                }
                $nextSpentDiff = $nextMin - $totalSpent;
            } else {
                $progressPercent = 100; // Niveau max atteint
            }

            $this->render('client/rewards', [
                'user'            => $currentUser,
                'totalSpent'      => $totalSpent,
                'currentTier'     => $currentTier,
                'nextTier'        => $nextTier,
                'progressPercent' => $progressPercent,
                'nextSpentDiff'   => $nextSpentDiff,
                'logs'            => $logs,
                'pageTitle'       => 'Récompenses de Remboursement',
            ]);
        } catch (\Throwable $e) {
            echo "<div style='padding:20px;background:#0f172a;color:#f1f5f9;font-family:sans-serif;border-radius:12px;border:1px solid #334155;max-width:600px;margin:50px auto;'>";
            echo "<h2 style='color:#ef4444;margin-top:0;'>Erreur Programme de Fidelite</h2>";
            echo "<p>Une erreur est survenue lors de l'affichage de vos recompenses :</p>";
            echo "<p style='background:#1e293b;padding:12px;border-radius:6px;border-left:4px solid #ef4444;font-family:monospace;font-size:13px;'>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p style='font-size:12px;color:#94a3b8;'>Astuce : Assurez-vous d'avoir bien uploade tous les nouveaux fichiers et d'avoir visite l'URL <strong>https://kivubooster.kesug.com/update_schema.php</strong> pour mettre a jour la base de donnees.</p>";
            echo "</div>";
            exit;
        }
    }

    /**
     * POST /rewards/redeem — Échanger les points accumulés contre du crédit réel
     */
    public function redeemRewards(): void
    {
        Auth::requireLogin();
        
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/rewards');
        }

        $user = Auth::user();
        $loyaltyModel = new Loyalty();

        $success = $loyaltyModel->redeemPoints((int)$user['id']);
        if ($success) {
            \App\Core\Audit::log('redeem_points', "Conversion de points de fidélité réussie pour l'utilisateur ID: " . $user['id']);
            $this->flash('success', 'Vos points ont été convertis avec succès en crédit de solde !');
        } else {
            $this->flash('error', 'Impossible de convertir vos points. Assurez-vous d\'avoir au moins 500 points échangeables.');
        }

        Auth::refreshUser();
        $this->redirect('/rewards');
    }
}
