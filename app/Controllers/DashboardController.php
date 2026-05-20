<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Currency;
use App\Models\Service;
use App\Models\Order;

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
            'user'          => $user,
            'services'      => $services,
            'orders'        => $orders,
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
        $db = \App\Core\Database::getInstance();

        if (Auth::isAdmin()) {
            $orders = $orderModel->getAll();
            $stmtSub = $db->query("
                SELECT sub.*, s.name AS service_name, s.category, u.username AS client_username
                FROM subscriptions sub
                LEFT JOIN services s ON s.id = sub.service_id
                LEFT JOIN users u ON u.id = sub.user_id
                ORDER BY sub.created_at DESC
            ");
            $subscriptions = $stmtSub->fetchAll();
        } else {
            $orders = $orderModel->getByUser((int)$user['id']);
            $stmtSub = $db->prepare("
                SELECT sub.*, s.name AS service_name, s.category
                FROM subscriptions sub
                LEFT JOIN services s ON s.id = sub.service_id
                WHERE sub.user_id = ?
                ORDER BY sub.created_at DESC
            ");
            $stmtSub->execute([$user['id']]);
            $subscriptions = $stmtSub->fetchAll();
        }

        $this->render('client/history', [
            'user'          => $user,
            'orders'        => $orders,
            'subscriptions' => $subscriptions,
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
            'user'          => $user,
            'services'      => $services,
        ]);
    }

    /**
     * GET /currency/switch — Basculer la devise d'affichage USD <-> CDF
     */
    public function switchCurrency(): void
    {
        $current = Currency::getActive();
        $next = ($current === 'USD') ? 'CDF' : 'USD';
        Currency::setActive($next);

        // Redirection vers la page précédente ou le dashboard par défaut
        $referrer = $_SERVER['HTTP_REFERER'] ?? (APP_BASE . '/dashboard');
        header('Location: ' . $referrer);
        exit;
    }
}
