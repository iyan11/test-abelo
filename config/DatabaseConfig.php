<?php
namespace config;

class DatabaseConfig {
    function getConfig(): array
    {
        return [
            'host'     => getenv("DATABASE_HOST") ?: "localhost",
            'port'     => getenv("DATABASE_PORT") ?: "3306",
            'user'     => getenv("MYSQL_USER") ?: "abelo",
            'password' => getenv("MYSQL_PASSWORD") ?: "abelo",
            'db'       => getenv("MYSQL_DATABASE") ?: "abelo",
            'charset'  => "utf8mb4"
        ];
    }
}
