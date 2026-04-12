<?php
session_start();
use system\EnvLoader;
use system\Router;

require_once __DIR__ . '/../system/EnvLoader.php';
EnvLoader::load(__DIR__ . '/../');


// Подключаем Smarty
require_once __DIR__ . '/../system/Smarty/libs/Smarty.class.php';

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
    Router::dispatch($method, $uri);
} catch (Exception $e) {
    echo $e->getMessage();
}