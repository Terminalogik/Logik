<?php
// Usage: php scripts/Vision.php login auth
// 
if ($argc < 3) {
    echo "Usage: php Vision.php <viewname> <subdir>\n";
    exit(1);
}

$view = $argv[1];
$subdir = $argv[2];

// View file
$viewDir = __DIR__ . "/../views/$subdir";
$viewFile = "$viewDir/$view.php";
if (!is_dir($viewDir)) mkdir($viewDir, 0777, true);
if (!file_exists($viewFile)) {
    file_put_contents($viewFile, "<h2>..title..</h2>\n<p>This is the $view view.</p>\n");
    echo "Created view: $viewFile\n";
} else {
    echo "View already exists: $viewFile\n";
}

// Controller file
$className = ucfirst($view) . "Code";
$controllerDir = __DIR__ . "/../code/$subdir";
$controllerFile = "$controllerDir/$className.php";
if (!is_dir($controllerDir)) mkdir($controllerDir, 0777, true);
if (!file_exists($controllerFile)) {
    $controller = "<?php\nnamespace App\\Code;\n\nclass $className extends Code\n{\n    public function index(): void\n    {\n        echo view_template('$subdir.$view', ['title' => ucfirst('$view')]);\n    }\n}\n";
    file_put_contents($controllerFile, $controller);
    echo "Created controller: $controllerFile\n";
} else {
    echo "Controller already exists: $controllerFile\n";
}
