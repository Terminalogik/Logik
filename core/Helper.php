
<?php
function _base_path(): string {
    $dir = str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($dir === '.' || $dir === '/' || $dir === '') return '/';
    return '/' . trim($dir, '/') . '/';
}

function stack(string $path): string {
    return rtrim(_base_path(), '/') . '/' . ltrim($path, '/');
}

// Build URL from a named route (you already had this)
function vector(string $name, array $params = []): string {
    return \Logik\Router::instance()->vector($name, $params);
}

/** Current request path (no query string), normalized */
function current_path(): string {
    $p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $p = rtrim($p, '/');
    return $p === '' ? '/' : $p;
}

// Safer link builder: returns "#" if the named route doesn't exist
function atlas(string $name, array $params = []): string {
    try {
        return \Logik\Router::instance()->vector($name, $params);
    } catch (\Throwable $e) {
        return '#';
    }
}


/** Is the current page this named route? 
 *  $loose=true also marks subpaths active (e.g., /myevents/123 matches 'myevents')
 */
function route_is(string $name, array $params = [], bool $loose = false): bool {
    $cur = current_path();
    $tgt = parse_url(atlas($name, $params), PHP_URL_PATH) ?? '/';
    $tgt = rtrim($tgt, '/'); $tgt = $tgt === '' ? '/' : $tgt;

    if ($cur === $tgt) return true;
    if ($loose && $tgt !== '/' && strpos($cur, $tgt . '/') === 0) return true;
    return false;
}

/** Convenience: render an <a> to a named route with active classes */
function map(string $name, string $label, array $params = [], array $attrs = [], bool $loose = false): string {
    $href   = atlas($name, $params);
    $active = route_is($name, $params, $loose);

    // Tailwind-ish defaults; tweak as you like
    $base   = 'px-3 py-2 rounded hover:underline';
    $activeCls = 'font-semibold underline';

    $cls = trim(($attrs['class'] ?? '') . ' ' . $base . ($active ? ' ' . $activeCls : ''));
    $attr = 'class="' . htmlspecialchars($cls, ENT_QUOTES) . '"';
    foreach ($attrs as $k => $v) { if ($k === 'class') continue;
        $attr .= ' ' . $k . '="' . htmlspecialchars((string)$v, ENT_QUOTES) . '"';
    }
    if ($active) $attr .= ' aria-current="page"';

    return '<a href="' . htmlspecialchars($href, ENT_QUOTES) . "\" $attr>" . htmlspecialchars($label) . '</a>';
}

/** Easy redirect to a named route */
function redirect_vector(string $name, array $params = [], int $code = 302): void {
    header('Location: ' . atlas($name, $params), true, $code);
    exit;
}

/** Build a named route + query string */
function vector_qs(string $name, array $params = [], array $qs = []): string {
    $url = atlas($name, $params);
    return $qs ? $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($qs) : $url;
}