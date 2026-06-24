<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;
use App\Core\Audit;

/**
 * GoogleAuthController — Connexion OAuth2 avec Google (sans dépendance externe)
 * 
 * Flux :
 *  1. GET  /auth/google          → Redirige vers Google
 *  2. GET  /auth/google/callback → Reçoit le code, échange contre un token, crée/connecte l'utilisateur
 */
class GoogleAuthController extends Controller
{
    // -------------------------------------------------------
    // GET /auth/google — Initier la connexion avec Google
    // -------------------------------------------------------
    public function initiate(): void
    {
        if (Auth::isLoggedIn()) {
            parent::redirect('/dashboard');
        }

        // Générer un état aléatoire anti-CSRF
        $state = bin2hex(random_bytes(16));
        $_SESSION['google_oauth_state'] = $state;

        $params = http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'online',
            'state'         => $state,
            'prompt'        => 'select_account',
        ]);

        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
        exit;
    }

    // -------------------------------------------------------
    // GET /auth/google/callback — Traiter le retour de Google
    // -------------------------------------------------------
    public function callback(): void
    {
        // 1. Vérification de l'état anti-CSRF
        $state = $_GET['state'] ?? '';
        if (empty($state) || $state !== ($_SESSION['google_oauth_state'] ?? '')) {
            $this->flash('error', 'Erreur de sécurité OAuth. Veuillez réessayer.');
            parent::redirect('/login');
        }
        unset($_SESSION['google_oauth_state']);

        // 2. Vérifier qu'il n'y a pas d'erreur Google
        if (!empty($_GET['error'])) {
            $this->flash('error', 'Connexion Google annulée ou refusée.');
            parent::redirect('/login');
        }

        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            $this->flash('error', 'Code d\'autorisation Google manquant.');
            parent::redirect('/login');
        }

        // 3. Échanger le code contre un access_token
        $tokenData = $this->exchangeCodeForToken($code);
        if (!$tokenData || empty($tokenData['access_token'])) {
            $this->flash('error', 'Impossible d\'obtenir le token Google. Veuillez réessayer.');
            parent::redirect('/login');
        }

        // 4. Récupérer le profil Google de l'utilisateur
        $googleUser = $this->fetchGoogleProfile($tokenData['access_token']);
        if (!$googleUser || empty($googleUser['email'])) {
            $this->flash('error', 'Impossible de récupérer votre profil Google.');
            parent::redirect('/login');
        }

        // 5. Vérifier que l'email Google est vérifié
        if (!($googleUser['email_verified'] ?? false)) {
            $this->flash('error', 'Votre adresse email Google n\'est pas vérifiée.');
            parent::redirect('/login');
        }

        $email     = $googleUser['email'];
        $googleId  = $googleUser['sub'];      // ID unique Google
        $firstName = $googleUser['given_name'] ?? '';
        $lastName  = $googleUser['family_name'] ?? '';
        $name      = trim($firstName . ' ' . $lastName) ?: $email;
        $avatar    = $googleUser['picture'] ?? null;

        // 6. Trouver ou créer l'utilisateur
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user) {
            // — Utilisateur existant : mise à jour de l'ID Google si pas déjà enregistré
            $this->updateGoogleId($user['id'], $googleId);
            Audit::log('google_login', "Connexion Google pour : {$user['username']} ({$email})");
            $this->flash('success', 'Bienvenue, ' . htmlspecialchars($user['username']) . ' !');

        } else {
            // — Nouvel utilisateur : création automatique du compte
            $username = $this->generateUsername($name, $email, $userModel);

            // Mot de passe aléatoire (compte Google, jamais utilisé pour connexion classique)
            $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);

            $userId = $userModel->create($username, $email, $randomPassword);

            // Enregistrer l'ID Google et l'avatar
            $this->saveGoogleData($userId, $googleId, $avatar);

            // Bonus de bienvenue fidélité
            try {
                $db = \App\Core\Database::getInstance();
                $db->prepare("UPDATE users SET loyalty_points = 200, lifetime_points = 200 WHERE id = ?")
                   ->execute([$userId]);
                $db->prepare("INSERT INTO loyalty_logs (user_id, points, description) VALUES (?, 200, 'Bonus de bienvenue (inscription Google)')")
                   ->execute([$userId]);
            } catch (\Throwable $e) {
                // Silencieux si table non migrée
            }

            // Email de bienvenue
            @sendKivuBoostMail($email, "Bienvenue sur KivuBoost !", "register", [
                'username'  => $username,
                'userEmail' => $email,
                'loginUrl'  => APP_URL . '/login',
            ]);

            $user = $userModel->findById($userId);
            Audit::log('google_register', "Création de compte via Google pour : {$username} ({$email})");
            $this->flash('success', "Compte créé avec succès ! Bienvenue, {$username} 🎉");
        }

        // 7. Connecter l'utilisateur
        Auth::login($user);

        if (Auth::isAdmin()) {
            parent::redirect('/admin');
        } else {
            parent::redirect('/dashboard');
        }
    }

    // -------------------------------------------------------
    // Échangeant le code OAuth contre un access_token
    // -------------------------------------------------------
    private function exchangeCodeForToken(string $code): ?array
    {
        $postData = http_build_query([
            'code'          => $code,
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ]);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        return json_decode($response, true) ?: null;
    }

    // -------------------------------------------------------
    // Récupérer le profil Google avec l'access_token
    // -------------------------------------------------------
    private function fetchGoogleProfile(string $accessToken): ?array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        return json_decode($response, true) ?: null;
    }

    // -------------------------------------------------------
    // Générer un nom d'utilisateur unique à partir du nom Google
    // -------------------------------------------------------
    private function generateUsername(string $name, string $email, User $userModel): string
    {
        // Nettoyer le nom : enlever les accents, espaces, caractères spéciaux
        $base = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $name));
        $base = preg_replace('/_+/', '_', trim($base, '_'));
        $base = strtolower(substr($base ?: explode('@', $email)[0], 0, 20));

        if (empty($base)) {
            $base = 'user';
        }

        // Vérifier l'unicité et ajouter un suffixe si nécessaire
        $username = $base;
        $counter  = 1;
        while ($userModel->findByUsername($username)) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    // -------------------------------------------------------
    // Enregistrer l'ID Google (mise à jour d'un compte existant)
    // -------------------------------------------------------
    private function updateGoogleId(int $userId, string $googleId): void
    {
        try {
            $db = \App\Core\Database::getInstance();
            $db->prepare("UPDATE users SET google_id = ? WHERE id = ? AND (google_id IS NULL OR google_id = '')")
               ->execute([$googleId, $userId]);
        } catch (\Throwable $e) {
            // Colonne pas encore créée → silencieux
        }
    }

    // -------------------------------------------------------
    // Enregistrer l'ID Google + avatar (nouvel utilisateur)
    // -------------------------------------------------------
    private function saveGoogleData(int $userId, string $googleId, ?string $avatarUrl): void
    {
        try {
            $db = \App\Core\Database::getInstance();
            $db->prepare("UPDATE users SET google_id = ?, google_avatar = ? WHERE id = ?")
               ->execute([$googleId, $avatarUrl, $userId]);
        } catch (\Throwable $e) {
            // Colonnes pas encore créées → silencieux
        }
    }
}
