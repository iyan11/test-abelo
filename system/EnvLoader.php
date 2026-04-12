<?php
namespace system;

class EnvLoader
{
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = rtrim($path, '/') . '/.env';

        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Пропускаем комментарии
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // Разбираем переменную
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0]);
            $value = trim($parts[1]);

            // Убираем кавычки если есть
            if (strlen($value) > 0 && ($value[0] === '"' || $value[0] === "'")) {
                $value = substr($value, 1, -1);
            }

            // Устанавливаем переменную окружения
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        self::$loaded = true;
    }
}