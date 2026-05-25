<?php
// ============================================================
// KivuBoost — Front Controller (Point d'entrée unique)
// ============================================================

declare(strict_types=1);

// Démarrage de la session sécurisée
session_set_cookie_params([
    'lifetime' => 7200,
    'path'    => '/KivuBoost/',
    'secure'  => false,   // passer à true en production HTTPS
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Chargement de la configuration (inclut l'autoloader)
require_once __DIR__ . '/config/config.php';

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
$router->get('/api-docs',      'ApiController@docs');
$router->post('/api-docs/generate-key', 'ApiController@generateKey');

// Portefeuille
$router->get('/recharge',          'RechargeController@index');
$router->post('/recharge/submit',  'RechargeController@submit');

// Administration
$router->get('/admin',                      'AdminController@index');
$router->get('/admin/configuration',        'AdminController@configuration');
$router->get('/admin/provider-balance',     'AdminController@getProviderBalance');
$router->post('/admin/recharge/approve',    'AdminController@approveRecharge');
$router->post('/admin/recharge/reject',     'AdminController@rejectRecharge');
$router->get('/admin/settings',             'AdminController@settings');
$router->post('/admin/settings/update',     'AdminController@updateSettings');
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
$router->get('/admin/audit',                'AdminController@audit');
$router->get('/admin/campaign',             'AdminController@campaignForm');
$router->post('/admin/campaign/send',       'AdminController@sendCampaign');

// Actualités publiques
$router->get('/actualites',                 'NewsController@index');
$router->get('/actualites/:slug',           'NewsController@show');

// Support public
$router->get('/support',                            'SupportController@index');

// Support — Admin
$router->get('/admin/support',                      'SupportController@adminIndex');
$router->post('/admin/support/settings',            'SupportController@updateSettings');
$router->post('/admin/support/agents/add',          'SupportController@addAgent');
$router->post('/admin/support/agents/toggle',       'SupportController@toggleAgent');
$router->post('/admin/support/agents/delete',       'SupportController@deleteAgent');

// Actualités — Admin
$router->get('/admin/actualites',           'NewsController@adminForm');
$router->post('/admin/actualites/publier',  'NewsController@store');

// -------------------------------------------------------
// Dispatch
// -------------------------------------------------------
$router->dispatch();
