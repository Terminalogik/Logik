<?php
namespace Logik;

/**
 * Fission - Command line tool for generating Code (controllers) and Views
 * Usage: php core/Fission.php make:view <name>
 */
class Fission
{
    private string $projectRoot;
    private string $codeDir;
    private string $viewsDir;
    private string $vectorsFile;

    public function __construct()
    {
        $this->projectRoot = dirname(__DIR__);
        $this->codeDir = $this->projectRoot . '/code';
        $this->viewsDir = $this->projectRoot . '/views';
        $this->vectorsFile = $this->projectRoot . '/vectors/web.php';
    }

    /**
     * Main entry point for the fission command
     */
    public function run(array $argv): void
    {
        if (count($argv) < 2) {
            $this->showHelp();
            return;
        }

        $command = $argv[1];

        switch ($command) {
            case 'make:view':
                if (count($argv) < 3) {
                    echo "Error: View name is required.\n";
                    echo "Usage: php core/Fission.php make:view <name>\n";
                    return;
                }
                $this->makeView($argv[2]);
                break;
            case 'help':
            case '--help':
            case '-h':
                $this->showHelp();
                break;
            default:
                echo "Unknown command: $command\n";
                $this->showHelp();
        }
    }

    /**
     * Generate a new view with its corresponding Code (controller)
     */
    private function makeView(string $name): void
    {
        $name = $this->sanitizeName($name);
        $codeName = $this->toPascalCase($name) . 'Code';
        $viewName = strtolower($name);

        echo "Generating view: $viewName\n";
        echo "Generating code: $codeName\n";

        // Create Code file
        $this->createCodeFile($codeName, $viewName);
        
        // Create View file
        $this->createViewFile($viewName);
        
        // Add route to web.php
        $this->addRoute($viewName, $codeName);

        echo "✅ View '$viewName' created successfully!\n";
        echo "📁 Code: code/{$codeName}.code.php\n";
        echo "📁 View: views/{$viewName}.php\n";
        echo "🔗 Route: /{$viewName} -> {$codeName}@index\n";
    }

    /**
     * Create the Code (controller) file
     */
    private function createCodeFile(string $codeName, string $viewName): void
    {
        $codeContent = $this->getCodeTemplate($codeName, $viewName);
        $codePath = $this->codeDir . '/' . $codeName . '.code.php';
        
        if (file_exists($codePath)) {
            echo "⚠️  Code file already exists: $codePath\n";
            return;
        }

        file_put_contents($codePath, $codeContent);
        echo "✅ Created Code file: $codePath\n";
    }

    /**
     * Create the View file
     */
    private function createViewFile(string $viewName): void
    {
        $viewContent = $this->getViewTemplate($viewName);
        $viewPath = $this->viewsDir . '/' . $viewName . '.php';
        
        if (file_exists($viewPath)) {
            echo "⚠️  View file already exists: $viewPath\n";
            return;
        }

        file_put_contents($viewPath, $viewContent);
        echo "✅ Created View file: $viewPath\n";
    }

    /**
     * Add route to web.php
     */
    private function addRoute(string $viewName, string $codeName): void
    {
        if (!file_exists($this->vectorsFile)) {
            echo "⚠️  Vectors file not found: {$this->vectorsFile}\n";
            return;
        }

        $routeLine = "\$vector->get('/{$viewName}', '{$codeName}@index', '{$viewName}');\n";
        
        // Read current content
        $content = file_get_contents($this->vectorsFile);
        
        // Check if route already exists
        if (strpos($content, "'/{$viewName}'") !== false) {
            echo "⚠️  Route already exists in web.php\n";
            return;
        }

        // Add route before the debug route (if it exists) or at the end
        if (strpos($content, '_debug') !== false) {
            $content = str_replace(
                "\$vector->get('/_debug', function () {",
                $routeLine . "\n\$vector->get('/_debug', function () {",
                $content
            );
        } else {
            $content = rtrim($content) . "\n\n" . $routeLine;
        }

        file_put_contents($this->vectorsFile, $content);
        echo "✅ Added route to web.php\n";
    }

    /**
     * Get Code template
     */
    private function getCodeTemplate(string $codeName, string $viewName): string
    {
        $title = $this->formatTitle($viewName);
        return "<?php
namespace App\\Code;

use Logik\\Code;

class {$codeName} extends Code
{
    public function index(): void
    {
        \$this->view('{$viewName}', ['title' => '{$title}'], 'axiom_dash');
    }
}";
    }

    /**
     * Get View template
     */
    private function getViewTemplate(string $viewName): string
    {
        $title = $this->formatTitle($viewName);
        return "<div class=\"container\">
    <h1>{$title}</h1>
    <p>This is the {$viewName} view generated by Fission.</p>
    <p>You can customize this view in <code>views/{$viewName}.php</code></p>
</div>";
    }

    /**
     * Sanitize name to be safe for file names and class names
     */
    private function sanitizeName(string $name): string
    {
        // Remove any non-alphanumeric characters except underscores and hyphens
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
        
        // Convert hyphens to underscores
        $name = str_replace('-', '_', $name);
        
        // Ensure it starts with a letter
        if (!preg_match('/^[a-zA-Z]/', $name)) {
            $name = 'view_' . $name;
        }
        
        return $name;
    }

    /**
     * Convert string to PascalCase
     */
    private function toPascalCase(string $string): string
    {
        // Convert snake_case and kebab-case to PascalCase
        $string = str_replace(['-', '_'], ' ', $string);
        $string = ucwords($string);
        return str_replace(' ', '', $string);
    }

    /**
     * Format title for display (convert underscores to spaces and title case)
     */
    private function formatTitle(string $string): string
    {
        return ucwords(str_replace('_', ' ', $string));
    }

    /**
     * Show help information
     */
    private function showHelp(): void
    {
        echo "Logik Fission - View Generator\n";
        echo "==============================\n\n";
        echo "Usage:\n";
        echo "  php core/Fission.php make:view <name>    Generate a new view with controller\n";
        echo "  php core/Fission.php help               Show this help message\n\n";
        echo "Examples:\n";
        echo "  php core/Fission.php make:view dashboard\n";
        echo "  php core/Fission.php make:view user-profile\n";
        echo "  php core/Fission.php make:view admin_panel\n\n";
        echo "This will create:\n";
        echo "  - code/{Name}Code.code.php (Controller)\n";
        echo "  - views/{name}.php (View)\n";
        echo "  - Add route to vectors/web.php\n";
    }
}

// Run the command if this file is executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $fission = new Fission();
    $fission->run($argv);
}
