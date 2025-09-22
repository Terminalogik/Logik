
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../core/Helper.php';

use Logik\Router;

$vector = new Router();

require __DIR__ . '/../vectors/web.php';

$vector->warp($_SERVER['REQUEST_URI']);
