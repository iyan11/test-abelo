<?php

require_once __DIR__ . '/../bootstrap.php';

use system\MigrationManager;

$action = $argv[1] ?? 'run';
$migrationManager = new MigrationManager(__DIR__ . '/../migrations');

switch ($action) {
    case 'run':
    case 'migrate':
        $migrationManager->run();
        break;

    case 'rollback':
        $steps = $argv[2] ?? 1;
        $migrationManager->rollback((int)$steps);
        break;

    case 'refresh':
        $migrationManager->refresh();
        break;

    case 'make':
        if (empty($argv[2])) {
            echo "Укажите имя\n";
            echo "Например: php migrate.php make create_users_table\n";
            exit(1);
        }
        $migrationManager->make($argv[2]);
        break;

    default:
        echo "Доступные команды:\n";
        echo "  php migrate.php run           - Запустить все миграции\n";
        echo "  php migrate.php rollback [n]  - Откат последних n миграций\n";
        echo "  php migrate.php refresh       - Откатить все миграции\n";
        echo "  php migrate.php make <name>   - Создать новую миграцию\n";
        break;
}