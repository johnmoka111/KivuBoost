<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Audit;
use App\Models\SupportAgent;
use App\Models\Setting;

class SupportController extends Controller
{
    // -------------------------------------------------------
    // GET /support — Page publique de l'équipe
    // -------------------------------------------------------
    public function index(): void
    {
        $agentModel = new SupportAgent();
        $agents     = $agentModel->allActive();

        $mainWhatsapp  = Setting::get('main_whatsapp', '');
        $facebookUrl   = Setting::get('facebook_url', '#');
        $instagramUrl  = Setting::get('instagram_url', '#');

        $this->render('support/index', [
            'agents'        => $agents,
            'mainWhatsapp'  => $mainWhatsapp,
            'facebookUrl'   => $facebookUrl,
            'instagramUrl'  => $instagramUrl,
        ], 'none');
    }

    // -------------------------------------------------------
    // GET /admin/support — Interface admin de gestion
    // -------------------------------------------------------
    public function adminIndex(): void
    {
        Auth::requireAdmin();

        $agentModel  = new SupportAgent();
        $agents      = $agentModel->all();
        $allSettings = [
            'main_whatsapp' => Setting::get('main_whatsapp', ''),
            'facebook_url'  => Setting::get('facebook_url', ''),
            'instagram_url' => Setting::get('instagram_url', ''),
        ];

        $this->render('admin/support', [
            'user'        => Auth::user(),
            'agents'      => $agents,
            'allSettings' => $allSettings,
        ]);
    }

    // -------------------------------------------------------
    // POST /admin/support/settings — Mise à jour réseaux
    // -------------------------------------------------------
    public function updateSettings(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/admin/support');
        }

        $settingModel = new Setting();
        $data = [
            'main_whatsapp' => preg_replace('/[^0-9]/', '', trim($_POST['main_whatsapp'] ?? '')),
            'facebook_url'  => filter_var(trim($_POST['facebook_url'] ?? ''), FILTER_SANITIZE_URL),
            'instagram_url' => filter_var(trim($_POST['instagram_url'] ?? ''), FILTER_SANITIZE_URL),
        ];

        $settingModel->setMany($data);
        Audit::log('support_settings', 'Paramètres de support client mis à jour.');

        $this->flash('success', 'Paramètres de support enregistrés avec succès.');
        $this->redirect('/admin/support');
    }

    // -------------------------------------------------------
    // POST /admin/support/agents/add — Ajout d'un agent
    // -------------------------------------------------------
    public function addAgent(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/admin/support');
        }

        $name      = trim($_POST['name'] ?? '');
        $city      = trim($_POST['city'] ?? '');
        $whatsapp  = preg_replace('/[^0-9]/', '', trim($_POST['whatsapp_number'] ?? ''));

        if (empty($name) || empty($city) || empty($whatsapp)) {
            $this->flash('error', 'Tous les champs texte sont obligatoires.');
            $this->redirect('/admin/support');
        }

        $photoPath = null;

        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/agents/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file     = $_FILES['photo'];
            $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!in_array($mimeType, $allowed, true)) {
                $this->flash('error', 'Format de photo non accepté. Utilisez JPG, PNG, WEBP ou GIF.');
                $this->redirect('/admin/support');
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                $this->flash('error', 'La photo ne peut pas dépasser 2 Mo.');
                $this->redirect('/admin/support');
            }

            $ext      = match ($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                default      => 'gif',
            };
            $filename = 'agent_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                $this->flash('error', 'Erreur lors du téléversement de la photo.');
                $this->redirect('/admin/support');
            }

            $photoPath = 'uploads/agents/' . $filename;
        }

        $agentModel = new SupportAgent();
        $id = $agentModel->create($name, $city, $whatsapp, $photoPath);
        Audit::log('add_support_agent', "Agent de support #{$id} — {$name} ({$city}) ajouté.");

        $this->flash('success', "Agent \"{$name}\" ajouté avec succès.");
        $this->redirect('/admin/support');
    }

    // -------------------------------------------------------
    // POST /admin/support/agents/toggle — Activer / désactiver
    // -------------------------------------------------------
    public function toggleAgent(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin/support');
        }

        $id       = (int)($_POST['id'] ?? 0);
        $isActive = (int)($_POST['is_active'] ?? 0);

        if ($id <= 0) {
            $this->flash('error', 'Agent introuvable.');
            $this->redirect('/admin/support');
        }

        (new SupportAgent())->setActive($id, $isActive);
        $label = $isActive ? 'activé' : 'désactivé';
        Audit::log('toggle_support_agent', "Agent #{$id} {$label}.");

        $this->flash('success', "Agent {$label} avec succès.");
        $this->redirect('/admin/support');
    }

    // -------------------------------------------------------
    // POST /admin/support/agents/delete — Suppression
    // -------------------------------------------------------
    public function deleteAgent(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token invalide.');
            $this->redirect('/admin/support');
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->flash('error', 'Agent introuvable.');
            $this->redirect('/admin/support');
        }

        $agentModel = new SupportAgent();
        $agent = $agentModel->findById($id);

        if ($agent && $agent['photo_path']) {
            $fullPath = __DIR__ . '/../../public/' . $agent['photo_path'];
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        $agentModel->delete($id);
        Audit::log('delete_support_agent', "Agent de support #{$id} supprimé.");

        $this->flash('success', 'Agent supprimé avec succès.');
        $this->redirect('/admin/support');
    }
}
