<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\News;

class NewsController extends Controller
{
    // -------------------------------------------------------
    // GET /actualites  — Page publique
    // -------------------------------------------------------
    public function index(): void
    {
        $newsModel = new News();
        $articles  = $newsModel->getPublished(50, 0);

        $featured  = !empty($articles) ? array_shift($articles) : null;
        $rest      = $articles;

        $this->render('news/index', [
            'featured' => $featured,
            'rest'     => $rest,
        ], 'none');
    }

    // -------------------------------------------------------
    // GET /actualites/:slug  — Article individuel
    // -------------------------------------------------------
    public function show(array $params): void
    {
        $newsModel = new News();
        $article   = $newsModel->getBySlug($params['slug'] ?? '');

        if (!$article) {
            http_response_code(404);
            include VIEW_PATH . '/errors/404.php';
            return;
        }

        $this->render('news/show', ['article' => $article], 'none');
    }

    // -------------------------------------------------------
    // GET /admin/actualites  — Formulaire de rédaction
    // -------------------------------------------------------
    public function adminForm(): void
    {
        Auth::requireAdmin();
        $this->render('admin/news_form', [], 'none');
    }

    // -------------------------------------------------------
    // POST /admin/actualites/publier  — Traitement du formulaire
    // -------------------------------------------------------
    public function store(): void
    {
        Auth::requireAdmin();

        if (!Auth::verifyCsrf()) {
            $this->flash('error', 'Token CSRF invalide. Réessayez.');
            $this->redirect('/admin/actualites');
        }

        // --- Validation des champs texte ---
        $title   = trim(strip_tags($_POST['title']   ?? ''));
        $summary = trim(strip_tags($_POST['summary'] ?? ''));
        $content = $_POST['content'] ?? '';          // HTML de Quill – NE PAS strip_tags
        $status  = in_array($_POST['status'] ?? '', ['publie','brouillon'], true)
                   ? $_POST['status']
                   : 'publie';

        if ($title === '' || $summary === '' || $content === '') {
            $this->flash('error', 'Le titre, le résumé et le contenu sont obligatoires.');
            $this->redirect('/admin/actualites');
        }

        // --- Génération du slug ---
        $slug = $this->makeSlug($title);
        $newsModel = new News();

        if ($newsModel->slugExists($slug)) {
            $slug .= '-' . time();
        }

        // --- Gestion de l'upload d'image ---
        $imagePath = null;

        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $file      = $_FILES['cover_image'];
            $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed   = ['jpg','jpeg','png','webp'];
            $maxBytes  = 2 * 1024 * 1024; // 2 Mo

            if (!in_array($ext, $allowed, true)) {
                $this->flash('error', 'Format image invalide. Autorisés : JPG, PNG, WEBP.');
                $this->redirect('/admin/actualites');
            }

            if ($file['size'] > $maxBytes) {
                $this->flash('error', 'L\'image ne doit pas dépasser 2 Mo.');
                $this->redirect('/admin/actualites');
            }

            $uploadDir = ROOT_PATH . '/public/uploads/news/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newName  = bin2hex(random_bytes(12)) . '.' . $ext;
            $destPath = $uploadDir . $newName;

            $moved = is_uploaded_file($file['tmp_name'])
                ? move_uploaded_file($file['tmp_name'], $destPath)
                : copy($file['tmp_name'], $destPath);

            if (!$moved) {
                $this->flash('error', 'Échec de l\'enregistrement de l\'image sur le serveur.');
                $this->redirect('/admin/actualites');
            }

            $imagePath = 'uploads/news/' . $newName;
        }

        // --- Insertion PDO ---
        $newsModel->insert($title, $slug, $summary, $content, $imagePath, $status);

        $this->flash('success', 'Article « ' . htmlspecialchars($title) . ' » publié avec succès !');
        $this->redirect('/actualites');
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------
    private function makeSlug(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        $accents = [
            'à'=>'a','â'=>'a','ä'=>'a','á'=>'a','ã'=>'a',
            'è'=>'e','ê'=>'e','ë'=>'e','é'=>'e',
            'î'=>'i','ï'=>'i','í'=>'i','ì'=>'i',
            'ô'=>'o','ö'=>'o','ó'=>'o','ò'=>'o','õ'=>'o',
            'ù'=>'u','û'=>'u','ü'=>'u','ú'=>'u',
            'ç'=>'c','ñ'=>'n',
        ];
        $text = strtr($text, $accents);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return trim($text, '-');
    }
}
