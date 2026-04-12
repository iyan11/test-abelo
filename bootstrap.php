<?php
// Общий загрузчик для CLI

// Загрузка .env
require_once __DIR__ . '/system/EnvLoader.php';
\system\EnvLoader::load(__DIR__ . '/');

// Автозагрузка классов
spl_autoload_register(function ($class) {
    $path = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});