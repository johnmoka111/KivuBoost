<?php

namespace App\Core;

/**
 * Router — Routeur HTTP léger avec support GET / POST et paramètres dynamiques
 */
class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    public function post(string $path, string $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Retirer la base (ex: /KivuBoost) de l'URI
        $base = rtrim(APP_BASE, '/');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $uri = '/' . trim($uri, '/');
        if ($uri === '') $uri = '/';

        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($routeMethod !== $method) continue;

            $pattern = $this->pathToRegex($routePath);
            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$controllerName, $action] = explode('@', $handler);
                $class = "App\\Controllers\\{$controllerName}";

                if (!class_exists($class)) {
                    $this->abort(500, "Contrôleur introuvable : {$class}");
                    return;
                }

                $controller = new $class();
                if (!method_exists($controller, $action)) {
                    $this->abort(500, "Méthode introuvable : {$action}");
                    return;
                }

                $controller->$action($params);
                return;
            }
        }

        $this->abort(404, 'Page introuvable');
    }

    private function pathToRegex(string $path): string
    {
        // Convertit /admin/:id en regex avec groupe nommé
        $pattern = preg_replace('/\/:([a-zA-Z_]+)/', '/(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function abort(int $code, string $message): void
    {
        http_response_code($code);
        if ($code === 404) {
            include VIEW_PATH . '/errors/404.php';
        } else {
            echo "<h1>Erreur {$code}</h1><p>{$message}</p>";
        }
    }
}
