<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Audit;
use App\Models\SupportAgent;
use App\Models\Setting;
use App\Models\SupportTicket;
use App\Models\SupportMessage;

class SupportController extends Controller
{
    // -------------------------------------------------------
    // GET /support — Page publique de l'équipe (WhatsApp)
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

    // =======================================================
    // --- TICKETS DE SUPPORT (ESPACE CLIENT CONNECÉ) ---
    // =======================================================

    // GET /tickets
    public function ticketsIndex(): void
    {
        Auth::requireLogin();
        $userId = (int)Auth::user()['id'];

        $ticketModel = new SupportTicket();
        $tickets = $ticketModel->allByUserId($userId);

        $this->render('support/client_list', [
            'tickets'   => $tickets,
            'pageTitle' => 'Support & Tickets'
        ]);
    }

    // POST /tickets/create
    public function createTicket(): void
    {
        Auth::requireLogin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/tickets');
        }

        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($subject) || empty($message)) {
            $this->flash('error', 'Le sujet et le message ne peuvent pas être vides.');
            $this->redirect('/tickets');
        }

        $userId = (int)Auth::user()['id'];

        $ticketModel  = new SupportTicket();
        $messageModel = new SupportMessage();

        $ticketId = $ticketModel->create($userId, $subject);
        $messageModel->create($ticketId, $userId, $message);

        Audit::log('create_ticket', "Nouveau ticket #{$ticketId} créé : {$subject}");
        $this->flash('success', 'Votre ticket a été créé avec succès.');
        $this->redirect('/tickets/' . $ticketId);
    }

    // GET /tickets/:id
    public function viewTicket(array $params = []): void
    {
        Auth::requireLogin();
        $ticketId = (int)($params['id'] ?? 0);
        $userId = (int)Auth::user()['id'];

        $ticketModel  = new SupportTicket();
        $ticket = $ticketModel->findById($ticketId);

        if (!$ticket || (int)$ticket['user_id'] !== $userId) {
            $this->flash('error', 'Ticket introuvable ou accès refusé.');
            $this->redirect('/tickets');
        }

        $messageModel = new SupportMessage();
        $messages = $messageModel->allByTicketId($ticketId);

        $this->render('support/client_view', [
            'ticket'    => $ticket,
            'messages'  => $messages,
            'pageTitle' => 'Ticket #' . $ticketId
        ]);
    }

    // POST /tickets/:id/reply
    public function replyTicket(array $params = []): void
    {
        Auth::requireLogin();
        $ticketId = (int)($params['id'] ?? 0);
        $userId = (int)Auth::user()['id'];

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/tickets/' . $ticketId);
        }

        $ticketModel = new SupportTicket();
        $ticket = $ticketModel->findById($ticketId);

        if (!$ticket || (int)$ticket['user_id'] !== $userId) {
            $this->flash('error', 'Ticket introuvable.');
            $this->redirect('/tickets');
        }

        if ($ticket['status'] === 'closed') {
            $this->flash('error', 'Ce ticket est fermé. Vous ne pouvez plus y répondre.');
            $this->redirect('/tickets/' . $ticketId);
        }

        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            $this->flash('error', 'Le message ne peut pas être vide.');
            $this->redirect('/tickets/' . $ticketId);
        }

        $messageModel = new SupportMessage();
        $messageModel->create($ticketId, $userId, $message);
        
        // Mettre à jour le statut du ticket à 'open'
        $ticketModel->updateStatus($ticketId, 'open');

        $this->flash('success', 'Votre réponse a été envoyée.');
        $this->redirect('/tickets/' . $ticketId);
    }

    // POST /tickets/:id/close
    public function closeTicket(array $params = []): void
    {
        Auth::requireLogin();
        $ticketId = (int)($params['id'] ?? 0);
        $userId = (int)Auth::user()['id'];

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/tickets/' . $ticketId);
        }

        $ticketModel = new SupportTicket();
        $ticket = $ticketModel->findById($ticketId);

        if (!$ticket || (int)$ticket['user_id'] !== $userId) {
            $this->flash('error', 'Ticket introuvable.');
            $this->redirect('/tickets');
        }

        $ticketModel->updateStatus($ticketId, 'closed');
        Audit::log('close_ticket', "Ticket #{$ticketId} fermé par le client.");

        $this->flash('success', 'Le ticket a été fermé.');
        $this->redirect('/tickets/' . $ticketId);
    }


    // =======================================================
    // --- TICKETS DE SUPPORT (ESPACE ADMINISTRATION) ---
    // =======================================================

    // GET /admin/tickets
    public function adminTicketsIndex(): void
    {
        Auth::requireAdmin();
        @set_time_limit(30);

        try {
            $ticketModel = new SupportTicket();
            $tickets = $ticketModel->all();
        } catch (\Throwable $e) {
            die("Erreur de base de donnees dans l'affichage des tickets : " . $e->getMessage() . "<br>Avez-vous execute update_schema.php sur le serveur ?");
        }

        $this->render('support/admin_list', [
            'tickets'   => $tickets,
            'pageTitle' => 'Gestion des Tickets Support'
        ]);
    }

    // GET /admin/tickets/:id
    public function adminViewTicket(array $params = []): void
    {
        Auth::requireAdmin();
        $ticketId = (int)($params['id'] ?? 0);

        $ticketModel = new SupportTicket();
        $ticket = $ticketModel->findById($ticketId);

        if (!$ticket) {
            $this->flash('error', 'Ticket introuvable.');
            $this->redirect('/admin/tickets');
        }

        $messageModel = new SupportMessage();
        $messages = $messageModel->allByTicketId($ticketId);

        $this->render('support/admin_view', [
            'ticket'    => $ticket,
            'messages'  => $messages,
            'pageTitle' => 'Répondre au Ticket #' . $ticketId
        ]);
    }

    // POST /admin/tickets/:id/reply
    public function adminReplyTicket(array $params = []): void
    {
        Auth::requireAdmin();
        $ticketId = (int)($params['id'] ?? 0);
        $adminId = (int)Auth::user()['id'];

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/admin/tickets/' . $ticketId);
        }

        $ticketModel = new SupportTicket();
        $ticket = $ticketModel->findById($ticketId);

        if (!$ticket) {
            $this->flash('error', 'Ticket introuvable.');
            $this->redirect('/admin/tickets');
        }

        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            $this->flash('error', 'Le message ne peut pas être vide.');
            $this->redirect('/admin/tickets/' . $ticketId);
        }

        $messageModel = new SupportMessage();
        $messageModel->create($ticketId, $adminId, $message);
        
        // Mettre à jour le statut du ticket à 'answered'
        $ticketModel->updateStatus($ticketId, 'answered');

        Audit::log('reply_ticket_admin', "Réponse apportée au ticket #{$ticketId} par l'admin.");

        $this->flash('success', 'Réponse envoyée avec succès.');
        $this->redirect('/admin/tickets/' . $ticketId);
    }

    // POST /admin/tickets/:id/close
    public function adminCloseTicket(array $params = []): void
    {
        Auth::requireAdmin();
        $ticketId = (int)($params['id'] ?? 0);

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token de sécurité invalide.');
            $this->redirect('/admin/tickets/' . $ticketId);
        }

        $ticketModel = new SupportTicket();
        $ticket = $ticketModel->findById($ticketId);

        if (!$ticket) {
            $this->flash('error', 'Ticket introuvable.');
            $this->redirect('/admin/tickets');
        }

        $ticketModel->updateStatus($ticketId, 'closed');
        Audit::log('close_ticket_admin', "Ticket #{$ticketId} fermé par l'administration.");

        $this->flash('success', 'Le ticket a été fermé.');
        $this->redirect('/admin/tickets/' . $ticketId);
    }


    // -------------------------------------------------------
    // GET /admin/support — Interface admin de gestion (WhatsApp Agents)
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
