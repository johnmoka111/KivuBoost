<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use PDO;

class ApiController extends Controller {

    public function docs() {
        Auth::requireLogin();

        if (Auth::isAdmin()) {
            $this->flash('error', 'Accès interdit aux administrateurs sur cette page.');
            $this->redirect('/admin');
        }

        $user = Auth::user();
        
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT api_key FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $apiKey = $stmt->fetchColumn();

        $this->render('client/api_docs', [
            'api_key' => $apiKey
        ]);
    }

    public function generateKey() {
        Auth::requireLogin();

        if (Auth::isAdmin()) {
            $this->flash('error', 'Action non autorisée pour les administrateurs.');
            $this->redirect('/admin');
        }

        $user = Auth::user();
        
        $newKey = bin2hex(random_bytes(32));
        
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET api_key = ? WHERE id = ?");
        $stmt->execute([$newKey, $user['id']]);
        
        // Déclencheur SMTP : Sécurité & Jeton API
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $dateTime  = date('d/m/Y H:i:s');
        sendKivuBoostMail($user['email'], "Alerte de Sécurité : Nouvelle clé API générée", "client_api", [
            'username'  => $user['username'],
            'apiKey'    => $newKey,
            'ipAddress' => $ipAddress,
            'dateTime'  => $dateTime
        ]);

        $this->flash("success", "Nouvelle clé API générée avec succès.");
        $this->redirect('/api-docs');
    }
}
