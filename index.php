<?php
// ============================================================
// BukavuBoost — Front Controller (Point d'entrée unique)
// ============================================================

declare(strict_types=1);

// Démarrage de la session sécurisée
session_set_cookie_params([
    'lifetime' => 7200,
    'path'     => '/KivuBoost/',
    'secure'   => false,   // passer à true en production HTTPS
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

// Client
$router->get('/',              'DashboardController@index');
$router->get('/dashboard',     'DashboardController@index');
$router->post('/orders/place', 'OrderController@place');
$router->get('/currency/switch','DashboardController@switchCurrency');
$router->get('/profile',       'AuthController@profile');
$router->post('/profile/update','AuthController@updateProfile');

// Portefeuille
$router->get('/recharge',          'RechargeController@index');
$router->post('/recharge/submit',  'RechargeController@submit');

// Administration
$router->get('/admin',                      'AdminController@index');
$router->post('/admin/recharge/approve',    'AdminController@approveRecharge');
$router->post('/admin/recharge/reject',     'AdminController@rejectRecharge');
$router->post('/admin/settings/update',     'AdminController@updateSettings');
$router->get('/admin/services',             'AdminController@services');
$router->post('/admin/services/save',       'AdminController@saveService');
$router->post('/admin/services/delete',     'AdminController@deleteService');
$router->post('/admin/services/update-price','AdminController@updateServicePrice');
$router->post('/admin/services/sync',       'AdminController@syncServices');
$router->post('/admin/providers/save',      'AdminController@saveProvider');
$router->post('/admin/providers/delete',     'AdminController@deleteProvider');
$router->post('/admin/users/balance',       'AdminController@adjustBalance');

// -------------------------------------------------------
// Dispatch
// -------------------------------------------------------
$router->dispatch();
