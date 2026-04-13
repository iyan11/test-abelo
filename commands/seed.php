<?php

require_once __DIR__ . '/../bootstrap.php';

use seeders\DatabaseSeeder;

$seeder = new DatabaseSeeder();
$seeder->run();

echo "Готово!\n";