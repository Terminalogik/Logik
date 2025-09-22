<?php
namespace Logik;

class Router
{
    /** HTTP method => [uri => action] */
    protected array $vectors = ['GET'=>[], 'POST'=>[], 'PUT'=>[], 'DELETE'=>[]];
    protected array $namedVectors = [];
    private static ?self $singleton = null;

    public function __construct() { if (!self::$singleton) self::$singleton = $this; }
    public static function instance(): self { return self::$singleton ?? (self::$singleton = new self()); }

    /** Return app base path (folder that holds index.php), like "" or "/ticketathon.com/public" */
    private function basePath(): string {
        $dir = str_replace('\\','/', \dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($dir === '.' || $dir === '/' || $dir === '') return '';
        return '/' . trim($dir, '/');
    }

    /** Normalize any path to an absolute-ish form: "/" or "/foo/bar" (no trailing slash except "/") */
    private function norm(string $path): string {
        $p = '/' . ltrim($path, '/');
        return rtrim($p, '/') ?: '/';
    }

    // ---- Registration with optional name ----
    public function get(string $uri, $action, ?string $name = null): void {
        $uri = $this->norm($uri);
        $this->vectors['GET'][$uri] = $action;
        if ($name) $this->namedVectors[$name] = $uri;
    }
    public function post(string $uri, $action, ?string $name = null): void {
        $uri = $this->norm($uri);
        $this->vectors['POST'][$uri] = $action;
        if ($name) $this->namedVectors[$name] = $uri;
    }
    public function put(string $uri, $action, ?string $name = null): void {
        $uri = $this->norm($uri);
        $this->vectors['PUT'][$uri] = $action;
        if ($name) $this->namedVectors[$name] = $uri;
    }
    public function delete(string $uri, $action, ?string $name = null): void {
        $uri = $this->norm($uri);
        $this->vectors['DELETE'][$uri] = $action;
        if ($name) $this->namedVectors[$name] = $uri;
    }

    // ---- Named vector URL builder ----
    public function vector(string $name, array $params = []): string {
        if (!isset($this->namedVectors[$name])) {
            throw new \InvalidArgumentException("Vector '{$name}' not defined.");
        }
        $uri = $this->namedVectors[$name];

        foreach ($params as $k => $v) {
            $uri = str_replace('{'.$k.'}', rawurlencode((string)$v), $uri);
        }
        return $this->basePath() . $this->norm($uri);
    }

    // ---- Dispatcher (warp) ----
    public function warp(string $uri): void
    {

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri    = \parse_url($uri ?? '', PHP_URL_PATH) ?? '/';
        $uri    = $this->norm($uri);

        // Normalize /public/ prefix if present
        if (strpos($uri, '/public/') === 0) {
            $uri = substr($uri, 7); // Remove '/public/'
            if ($uri === '') $uri = '/';
            $uri = $this->norm($uri);
        }

        // Debug output for routing
        if (isset($_GET['_debug_router'])) {
            header('Content-Type: text/plain');
            echo "[Router Debug] Method: $method\n";
            echo "[Router Debug] URI: $uri\n";
        }

        // Strip base (e.g., "/ticketathon.com/public") if present
        $base = $this->basePath();
        if ($base !== '' && strpos($uri, $base) === 0) {
            $uri = substr($uri, \strlen($base)) ?: '/';
            $uri = $this->norm($uri);
        }

        $vectorsForMethod = $this->vectors[$method] ?? [];

        foreach ($vectorsForMethod as $vector => $action) {
            // Build a safe regex that treats {param} as ([^/]+) and escapes everything else
            $route = $this->norm($vector);
            $tmp   = preg_replace('#\{[^/]+\}#', '___SEG___', $route);
            $tmp   = preg_quote($tmp, '#');
            $pattern = '#^' . str_replace('___SEG___', '([^/]+)', $tmp) . '$#';

            if (isset($_GET['_debug_router'])) {
                echo "[Router Debug] Checking route: $route\n";
                echo "[Router Debug] Pattern: $pattern\n";
            }

            if (\preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                if (isset($_GET['_debug_router'])) {
                    echo "[Router Debug] Matched route: $route\n";
                    echo "[Router Debug] Action: " . (is_string($action) ? $action : 'callable') . "\n";
                }

                if (\is_callable($action)) {
                    \call_user_func_array($action, $matches);
                } elseif (\is_string($action) && strpos($action, '@') !== false) {
                    [$codeName, $methodName] = \explode('@', $action, 2);
                    $codeClass = "App\\Code\\$codeName";
                    if (\class_exists($codeClass) && \method_exists($codeClass, $methodName)) {
                        $code = new $codeClass();
                        \call_user_func_array([$code, $methodName], $matches);
                    } else {
                        \http_response_code(500);
                        (new \Logik\Code())->view('anomalies/500', ['title' => 'Error']);
                    }
                }
                exit;
            }
        }

        if (isset($_GET['_debug_router'])) {
            echo "[Router Debug] No route matched for $method $uri\n";
        }

        \http_response_code(404);
        (new \Logik\Code())->view('anomalies/404', ['title' => 'Not Found']);
    }
}
