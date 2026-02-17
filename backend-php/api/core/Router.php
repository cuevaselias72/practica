<?php
class Router
{
    private $routes = [];
    private $protected_routes = [];
    private $version;
    private $basePath;

    public function __construct($version = 'v1', $basePath = '')
    {
        $this->version = $version;
        $this->basePath = rtrim($basePath, '/');
    }

    public function addRoute($method, $path, $handler)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => "/{$this->version}" . $path,
            'handler' => $handler,
            'protected' => false
        ];
    }

    /**
     * Agregar una ruta protegida que requiere validación de token
     */
    public function addProtectedRoute($method, $path, $handler)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => "/{$this->version}" . $path,
            'handler' => $handler,
            'protected' => true
        ];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (!empty($this->basePath) && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }

        // Asegurar que la URI comience con /
        $uri = '/' . ltrim($uri, '/');
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_-]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                // Si es una ruta protegida, validar el token
                if ($route['protected']) {
                    $this->validateToken();
                }

                array_shift($matches);
                return call_user_func_array($route['handler'], $matches);
            }
        }

        http_response_code(404);
        echo json_encode(['message' => 'Ruta no encontrada', 'uri' => $uri, 'method' => $method, 'basePath' => $this->basePath, 'routes' => array_map(function($r) { return ['method' => $r['method'], 'path' => $r['path']]; }, $this->routes)]);
    }

    /**
     * Validar el token del usuario
     */
    private function validateToken()
    {
        require_once __DIR__ . '/AuthMiddleware.php';
        require_once __DIR__ . '/../config/database.php';

        $database = new Database();
        $db = $database->getConnection();
        $auth = new AuthMiddleware($db);

        // Esta función termina la ejecución si el token no es válido
        $auth->validateRequest();
    }
}
?>