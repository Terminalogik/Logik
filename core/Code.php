<?php
namespace Logik;

/**
 * Base Code: renders a view and optionally wraps it with an axiom (layout).
 * $axiom resolves to: /views/axioms/<axiom>.php
 */
class Code
{
    public function view(string $view, array $data = [], ?string $axiom = 'axiom_dash'): void
    {
        // Allow $data['axiom'] to override
        if (isset($data['axiom']) && is_string($data['axiom'])) {
            $axiom = $data['axiom'];
        }

        // Extract variables for view + axiom
        extract($data, EXTR_SKIP);

        $viewsDir = \realpath(__DIR__ . '/../views') ?: (__DIR__ . '/../views');
        $viewPath = $viewsDir . '/' . ltrim($view, '/') . '.php';

        if (!\file_exists($viewPath)) {
            http_response_code(404);
            echo "View '{$view}' not found.";
            return;
        }

        // Render view into $content
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Wrap with axiom if provided & exists; else echo content
        if ($axiom) {
            $axiomPath = $viewsDir . '/axioms/' . $axiom . '.php';

            // Fallback: if caller passed "axioms/axiom_name" as axiom
            if (!\file_exists($axiomPath)) {
                $axiomPath = $viewsDir . '/' . $axiom . '.php';
            }

            if (\file_exists($axiomPath)) {
                require $axiomPath; // expects $content, $title, asset(), vector_url(), etc.
                return;
            }
        }

        echo $content;
    }
}
