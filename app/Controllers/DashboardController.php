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

        $this->render('dashboard/index', [
            'user'     => $user,
            'services' => $services,
            'orders'   => $orders,
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
