<?php
session_start();

use system\EnvLoader;
use system\Router;

require_once __DIR__ . '/../system/EnvLoader.php';
EnvLoader::load(__DIR__ . '/../');
require_once __DIR__ . '/../system/smarty/libs/Smarty.class.php';

spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

require_once __DIR__ . '/../routes/web.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

try {
    $response = Router::dispatch($method, $uri);

    if (is_string($response)) {
        echo $response;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo $e->getMessage();
}
