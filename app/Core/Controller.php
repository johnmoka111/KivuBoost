<?php

namespace App\Core;

/**
 * Controller — Classe de base pour tous les contrôleurs
 */
abstract class Controller
{
    /**
     * Rend une vue en lui injectant des données
     *
     * @param string $view   Chemin relatif ex: 'auth/login'
     * @param array  $data   Variables à extraire dans la vue
     * @param string $layout Layout à utiliser ('main' | 'auth' | 'none')
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        // Rendre les variables disponibles dans la vue
        extract($data, EXTR_SKIP);

        // Capturer le contenu de la vue
        ob_start();
        $viewFile = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            ob_end_clean();
            http_response_code(500);
            die("Vue introuvable : {$viewFile}");
        }
        include $viewFile;
        $content = ob_get_clean();

        // Rendre le layout
        if ($layout === 'none') {
            echo $content;
            return;
        }

        $layoutFile = VIEW_PATH . '/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            return;
        }

        include $layoutFile;
    }

    /**
     * Redirection HTTP propre
     */
    protected function redirect(string $path): void
    {
        $url = rtrim(APP_BASE, '/') . '/' . ltrim($path, '/');
        header('Location: ' . $url);
        exit;
    }

    /**
     * Retourner une réponse JSON
     */
    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Flash message — stocker en session
     */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Lire et effacer le flash message
     */
    public static function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
