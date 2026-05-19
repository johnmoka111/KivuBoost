<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // -------------------------------------------------------
    // GET /login
    // -------------------------------------------------------
    public function showLogin(): void
    {
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $this->render('auth/login', [], 'auth');
    }

    // -------------------------------------------------------
    // POST /login
    // -------------------------------------------------------
    public function login(): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide. Veuillez réessayer.');
            $this->redirect('/login');
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Veuillez remplir tous les champs.');
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->flash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('/login');
        }

        Auth::login($user);
        $this->flash('success', 'Bienvenue, ' . htmlspecialchars($user['username']) . ' !');

        if (Auth::isAdmin()) {
            $this->redirect('/admin');
        } else {
            $this->redirect('/dashboard');
        }
    }

    // -------------------------------------------------------
    // GET /register
    // -------------------------------------------------------
    public function showRegister(): void
    {
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $this->render('auth/register', [], 'auth');
    }

    // -------------------------------------------------------
    // POST /register
    // -------------------------------------------------------
    public function register(): void
    {
        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/register');
        }

        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password_confirm'] ?? '';

        // Validations
        if (empty($username) || empty($email) || empty($password)) {
            $this->flash('error', 'Tous les champs sont obligatoires.');
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Adresse email invalide.');
            $this->redirect('/register');
        }

        if (strlen($password) < 8) {
            $this->flash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
            $this->redirect('/register');
        }

        if ($password !== $password2) {
            $this->flash('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect('/register');
        }

        $userModel = new User();

        if ($userModel->findByEmail($email)) {
            $this->flash('error', 'Cet email est déjà utilisé.');
            $this->redirect('/register');
        }

        if ($userModel->findByUsername($username)) {
            $this->flash('error', 'Ce nom d\'utilisateur est déjà pris.');
            $this->redirect('/register');
        }

        // Hacher le mot de passe avant insertion
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $userId = $userModel->create($username, $email, $passwordHash);
        $user   = $userModel->findById($userId);

        Auth::login($user);
        $this->flash('success', 'Compte créé avec succès ! Bienvenue sur BukavuBoost.');
        $this->redirect('/dashboard');
    }

    // -------------------------------------------------------
    // GET /profile — Espace de modification de profil client
    // -------------------------------------------------------
    public function profile(): void
    {
        Auth::requireLogin();
        $this->render('auth/profile', [
            'user' => Auth::user()
        ]);
    }

    // -------------------------------------------------------
    // POST /profile/update
    // -------------------------------------------------------
    public function updateProfile(): void
    {
        Auth::requireLogin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/profile');
        }

        $currentUser = Auth::user();
        $userId      = (int)$currentUser['id'];

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email)) {
            $this->flash('error', 'Le pseudo et l\'email sont obligatoires.');
            $this->redirect('/profile');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Adresse email invalide.');
            $this->redirect('/profile');
        }

        $userModel = new User();

        // Vérifier si le pseudo est pris par un autre utilisateur
        $existUser = $userModel->findByUsername($username);
        if ($existUser && (int)$existUser['id'] !== $userId) {
            $this->flash('error', 'Ce pseudo est déjà utilisé.');
            $this->redirect('/profile');
        }

        // Vérifier si l'email est pris par un autre utilisateur
        $existEmail = $userModel->findByEmail($email);
        if ($existEmail && (int)$existEmail['id'] !== $userId) {
            $this->flash('error', 'Cet email est déjà associé à un autre compte.');
            $this->redirect('/profile');
        }

        // 1. Mettre à jour les infos textuelles
        $userModel->updateProfile($userId, $username, $email);

        // 2. Traitement du changement de mot de passe facultatif
        if (!empty($password)) {
            if (strlen($password) < 8) {
                $this->flash('error', 'Le nouveau mot de passe doit faire au moins 8 caractères.');
                $this->redirect('/profile');
            }
            $userModel->updatePassword($userId, password_hash($password, PASSWORD_BCRYPT));
        }

        // 3. Traitement de la photo d'avatar (Upload d'image)
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['avatar']['tmp_name'];
            $fileName    = $_FILES['avatar']['name'];
            $fileSize    = $_FILES['avatar']['size'];
            $fileType    = $_FILES['avatar']['type'];
            
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($fileExtension, $allowedExtensions)) {
                if ($fileSize <= 3 * 1024 * 1024) { // Limite de 3 Mo
                    $newFileName = md5(uniqid((string)$userId, true)) . '.' . $fileExtension;
                    
                    // S'assurer que le dossier public de stockage existe
                    $uploadDir = ROOT_PATH . '/public/uploads/avatars/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $destPath = $uploadDir . $newFileName;

                    if ($this->move_uploaded_path($fileTmpPath, $destPath)) {
                        // Optionnel : Supprimer l'ancien fichier d'avatar s'il existe
                        if (!empty($currentUser['avatar']) && file_exists($uploadDir . $currentUser['avatar'])) {
                            @unlink($uploadDir . $currentUser['avatar']);
                        }
                        $userModel->updateAvatar($userId, $newFileName);
                    } else {
                        // Fallback au cas où move_uploaded_file pose problème en environnement Windows restrictif
                        if (copy($fileTmpPath, $destPath)) {
                            if (!empty($currentUser['avatar']) && file_exists($uploadDir . $currentUser['avatar'])) {
                                @unlink($uploadDir . $currentUser['avatar']);
                            }
                            $userModel->updateAvatar($userId, $newFileName);
                        } else {
                            $this->flash('error', 'Erreur d\'écriture lors de l\'enregistrement de la photo.');
                        }
                    }
                } else {
                    $this->flash('error', 'La photo ne doit pas dépasser 3 Mo.');
                }
            } else {
                $this->flash('error', 'Extensions de photo autorisées : JPG, JPEG, PNG, GIF.');
            }
        }

        // Rafraîchir les infos de la session
        Auth::refreshUser();

        $this->flash('success', 'Votre compte a été mis à jour avec succès !');
        $this->redirect('/profile');
    }

    // Helper de secours si move_uploaded_file rencontre des soucis de droits Windows XAMPP
    private function move_uploaded_path(string $source, string $destination): bool
    {
        if (is_uploaded_file($source)) {
            return move_uploaded_file($source, $destination);
        }
        return false;
    }

    // -------------------------------------------------------
    // GET /logout
    // -------------------------------------------------------
    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
