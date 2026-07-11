<?php
// ============================================================
// KivuBoost — Front Controller (Point d'entrée unique)
// ============================================================

declare(strict_types=1);

// Activer temporairement l'affichage des erreurs pour déboguer le site
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Chargement de la configuration (inclut l'autoloader)
require_once __DIR__ . '/config/config.php';

// Démarrage de la session sécurisée
session_set_cookie_params([
    'lifetime' => 7200,
    'path'     => APP_BASE !== '/' ? rtrim(APP_BASE, '/') . '/' : '/',
    'secure'   => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'), // Détection HTTPS (gère aussi reverse proxy)
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Envoi des en-têtes de sécurité (évite les erreurs 500 sur InfinityFree qui bloque mod_headers)
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Routeur
use App\Core\Router;

$router = new Router();

// -------------------------------------------------------
// Définition des routes
// -------------------------------------------------------

// Auth
$router->get('/login',    'AuthController@showLogin');
$router->post('/login',   'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register','AuthController@register');
$router->get('/logout',   'AuthController@logout');

// Google OAuth2
$router->get('/auth/google',          'GoogleAuthController@initiate');
$router->get('/auth/google/callback', 'GoogleAuthController@callback');

// Page d'accueil publique
$router->get('/',              'NewsController@index');

// Client (espace connecté)
$router->get('/dashboard',     'DashboardController@index');
$router->get('/history',       'DashboardController@history');
$router->get('/services',      'DashboardController@services');
$router->post('/orders/place', 'OrderController@place');
$router->post('/orders/mass-place', 'OrderController@massPlace');
$router->post('/subscriptions/create', 'OrderController@createSubscription');
$router->get('/currency/switch','DashboardController@switchCurrency');
$router->get('/profile',       'AuthController@profile');
$router->post('/profile/update','AuthController@updateProfile');
$router->get('/rewards',        'DashboardController@rewards');
$router->post('/rewards/redeem', 'DashboardController@redeemRewards');
$router->get('/api-docs',      'ApiController@docs');
$router->post('/api-docs/generate-key', 'ApiController@generateKey');

// Portefeuille
$router->get('/recharge',          'RechargeController@index');
$router->post('/recharge/submit',  'RechargeController@submit');
$router->post('/recharge/online/initiate',  'RechargeController@initiateOnline');
$router->get('/recharge/online/success',   'RechargeController@onlineSuccess');
$router->get('/recharge/online/cancel',    'RechargeController@onlineCancel');
$router->post('/api/v1/payments/webhook/:gateway', 'RechargeController@webhook');
$router->post('/api/webhook/bkapay', 'RechargeController@webhook');

// Administration
$router->get('/admin',                      'AdminController@index');
$router->get('/admin/configuration',        'AdminController@configuration');
$router->get('/admin/provider-balance',     'AdminController@getProviderBalance');
$router->get('/admin/providers/balances',    'AdminController@providerBalances');
$router->post('/admin/recharge/approve',    'AdminController@approveRecharge');
$router->post('/admin/recharge/reject',     'AdminController@rejectRecharge');
$router->get('/admin/settings',             'AdminController@settings');
$router->post('/admin/settings/update',     'AdminController@updateSettings');
$router->post('/admin/gateways/update',     'AdminController@updateGateways');
$router->post('/admin/settings/update-margins','AdminController@updateMargins');
$router->get('/admin/services',             'AdminController@services');
$router->post('/admin/services/save',          'AdminController@saveService');
$router->post('/admin/services/delete',        'AdminController@deleteService');
$router->post('/admin/services/bulk-delete',   'AdminController@bulkDeleteServices');
$router->post('/admin/services/toggle-status', 'AdminController@toggleServiceStatus');
$router->post('/admin/services/bulk-toggle',   'AdminController@bulkToggleServices');
$router->post('/admin/services/update-price',  'AdminController@updateServicePrice');
$router->post('/admin/services/sync',       'AdminController@syncServices');
$router->post('/admin/providers/save',      'AdminController@saveProvider');
$router->post('/admin/providers/delete',     'AdminController@deleteProvider');
$router->post('/admin/users/balance',       'AdminController@adjustBalance');
$router->post('/admin/orders/sync-statuses','AdminController@syncOrderStatuses');
$router->post('/admin/orders/retry',        'AdminController@retryOrder');
$router->get('/admin/audit',                'AdminController@audit');
$router->get('/admin/campaign',             'AdminController@campaignForm');
$router->post('/admin/campaign/send',       'AdminController@sendCampaign');
$router->get('/admin/pricing-rules',        'AdminController@pricingRules');
$router->post('/admin/pricing-rules/save',  'AdminController@savePricingRule');
$router->post('/admin/pricing-rules/delete','AdminController@deletePricingRule');
$router->post('/admin/pricing-rules/apply', 'AdminController@applyPricingRules');
$router->get('/admin/financial-report',     'AdminController@financialReport');

// Actualités publiques
$router->get('/actualites',                 'NewsController@index');
$router->get('/actualites/:slug',           'NewsController@show');

// Support public
$router->get('/support',                            'SupportController@index');

// Support Tickets — Client
$router->get('/tickets',                            'SupportController@ticketsIndex');
$router->post('/tickets/create',                    'SupportController@createTicket');
$router->get('/tickets/:id',                        'SupportController@viewTicket');
$router->post('/tickets/:id/reply',                 'SupportController@replyTicket');
$router->post('/tickets/:id/close',                 'SupportController@closeTicket');

// Support — Admin (WhatsApp Agents & Tickets)
$router->get('/admin/support',                      'SupportController@adminIndex');
$router->post('/admin/support/settings',            'SupportController@updateSettings');
$router->post('/admin/support/agents/add',          'SupportController@addAgent');
$router->post('/admin/support/agents/toggle',       'SupportController@toggleAgent');
$router->post('/admin/support/agents/delete',       'SupportController@deleteAgent');

$router->get('/admin/tickets',                      'SupportController@adminTicketsIndex');
$router->get('/admin/tickets/:id',                  'SupportController@adminViewTicket');
$router->post('/admin/tickets/:id/reply',            'SupportController@adminReplyTicket');
$router->post('/admin/tickets/:id/close',            'SupportController@adminCloseTicket');

// Actualités — Admin
$router->get('/admin/actualites',           'NewsController@adminIndex');
$router->get('/admin/actualites/creer',     'NewsController@adminForm');
$router->get('/admin/actualites/edit/:id',  'NewsController@adminForm');
$router->post('/admin/actualites/publier',  'NewsController@store');
$router->post('/admin/actualites/delete/:id', 'NewsController@delete');

// -------------------------------------------------------
// Dispatch
// -------------------------------------------------------
$router->dispatch();
