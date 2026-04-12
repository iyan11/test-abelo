<?php
namespace system;

use PDO;

class MigrationManager
{
    private PDO $db;
    private string $migrationsPath;
    private string $migrationsTable = 'migrations';

    public function __construct(string $migrationsPath)
    {
        $this->db = DatabaseSystem::getInstance()->getConnection();
        $this->migrationsPath = $migrationsPath;
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    /**
     * Создание новой миграции
     */
    public function make(string $name): void
    {
        // Очищаем имя
        $name = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
        $name = strtolower($name);

        $timestamp = date('Y_m_d_His');
        $filename = $timestamp . "_" . $name . ".php";
        $path = $this->migrationsPath . '/' . $filename;

        // Генерируем имя класса
        $className = $this->generateClassName($name);
        $tableName = $this->extractTableName($name);

        $stub = $this->generateMigrationStub($className, $tableName);
        file_put_contents($path, $stub);

        echo "✓ Created migration: {$filename}\n";
        echo "  Class: {$className}\n";
        echo "  Table: {$tableName}\n";
    }

    /**
     * Генерация имени класса из имени миграции
     */
    private function generateClassName(string $name): string
    {
        // Убираем префиксы
        $cleanName = preg_replace('/^(create_|add_|drop_|alter_)/', '', $name);
        $cleanName = preg_replace('/_table$/', '', $cleanName);

        // Преобразуем snake_case в PascalCase
        $parts = explode('_', $cleanName);
        $className = '';
        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }

        // Добавляем суффикс
        if (strpos($name, 'create_') === 0) {
            $className .= 'Table';
        } else {
            $className .= 'Migration';
        }

        return $className;
    }

    /**
     * Извлечение имени таблицы
     */
    private function extractTableName(string $name): string
    {
        if (strpos($name, 'create_') === 0) {
            // create_users_table -> users
            $table = str_replace(['create_', '_table'], '', $name);
        } elseif (strpos($name, 'add_') === 0) {
            // add_age_to_users_table -> users
            if (preg_match('/to_(.+)_table/', $name, $matches)) {
                $table = $matches[1];
            } else {
                $table = str_replace(['add_', '_to_', '_table'], '', $name);
            }
        } else {
            $table = str_replace('_table', '', $name);
        }

        return $table;
    }

    /**
     * Генерация содержимого миграции
     */
    private function generateMigrationStub(string $className, string $tableName): string
    {
        return <<<PHP
<?php

use system\\Migration;
use system\\Blueprint;

class {$className} extends Migration
{
    public function up(): void
    {
        \$this->create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->timestamps();
        });
    }
    
    public function down(): void
    {
        \$this->dropIfExists('{$tableName}');
    }
}
PHP;
    }

    /**
     * Запуск всех миграций
     */
    public function run(): void
    {
        $executed = $this->getExecutedMigrations();
        $migrations = $this->getPendingMigrations($executed);

        if (empty($migrations)) {
            echo "Nothing to migrate.\n";
            return;
        }

        $batch = $this->getNextBatchNumber();

        echo "Running migrations...\n";

        foreach ($migrations as $migration) {
            $this->runMigration($migration, $batch);
        }

        echo "All migrations completed!\n";
    }

    /**
     * Выполнение одной миграции
     */
    private function runMigration(string $migrationFile, int $batch): void
    {
        $fullPath = $this->migrationsPath . '/' . $migrationFile;

        // Подключаем файл миграции
        require_once $fullPath;

        // Получаем имя класса из файла
        $className = $this->getClassNameFromFile($fullPath);

        if (!class_exists($className)) {
            echo "Error: Class {$className} not found in {$migrationFile}\n";
            echo "Available classes: " . implode(', ', get_declared_classes()) . "\n";
            return;
        }

        $migration = new $className();
        $migration->up();

        $this->recordMigration($migrationFile, $batch);

        echo "✓ Migrated: {$migrationFile}\n";
    }

    /**
     * Получение имени класса из файла миграции
     */
    private function getClassNameFromFile(string $filePath): string
    {
        // Читаем содержимое файла
        $content = file_get_contents($filePath);

        // Ищем класс через regex
        if (preg_match('/class\s+([a-zA-Z0-9_]+)\s+extends/', $content, $matches)) {
            return $matches[1];
        }

        // Альтернативный метод: из имени файла
        $filename = basename($filePath, '.php');
        // Убираем timestamp (первые 20 символов: 2024_01_13_120000_)
        $cleanName = substr($filename, 20);

        return $this->generateClassName($cleanName);
    }

    /**
     * Откат миграций
     */
    public function rollback(int $steps = 1): void
    {
        $migrations = $this->getLastBatchMigrations($steps);

        if (empty($migrations)) {
            echo "Nothing to rollback.\n";
            return;
        }

        echo "Rolling back migrations...\n";

        foreach (array_reverse($migrations) as $migration) {
            $this->rollbackMigration($migration);
        }

        echo "Rollback completed!\n";
    }

    /**
     * Откат одной миграции
     */
    private function rollbackMigration(string $migrationFile): void
    {
        $fullPath = $this->migrationsPath . '/' . $migrationFile;

        require_once $fullPath;

        $className = $this->getClassNameFromFile($fullPath);

        if (!class_exists($className)) {
            echo "Error: Class {$className} not found\n";
            return;
        }

        $migration = new $className();
        $migration->down();

        $this->removeMigration($migrationFile);

        echo "✓ Rolled back: {$migrationFile}\n";
    }

    /**
     * Полный рефреш
     */
    public function refresh(): void
    {
        echo "Refreshing migrations...\n";
        $this->rollback(1000);
        $this->run();
    }

    /**
     * Получение списка выполненных миграций
     */
    private function getExecutedMigrations(): array
    {
        $stmt = $this->db->query("SELECT migration FROM {$this->migrationsTable} ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Получение списка ожидающих миграций
     */
    private function getPendingMigrations(array $executed): array
    {
        $files = scandir($this->migrationsPath);
        $migrations = array_filter($files, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'php';
        });

        sort($migrations);

        return array_diff($migrations, $executed);
    }

    /**
     * Получение последних миграций
     */
    private function getLastBatchMigrations(int $steps): array
    {
        $stmt = $this->db->prepare("
            SELECT migration FROM {$this->migrationsTable} 
            WHERE batch = (SELECT MAX(batch) FROM {$this->migrationsTable})
            ORDER BY id DESC LIMIT :steps
        ");
        $stmt->bindValue(':steps', $steps, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Получение следующего номера батча
     */
    private function getNextBatchNumber(): int
    {
        $stmt = $this->db->query("SELECT MAX(batch) FROM {$this->migrationsTable}");
        return (int)$stmt->fetchColumn() + 1;
    }

    /**
     * Запись о выполненной миграции
     */
    private function recordMigration(string $migration, int $batch): void
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migration, $batch]);
    }

    /**
     * Удаление записи о миграции
     */
    private function removeMigration(string $migration): void
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->migrationsTable} WHERE migration = ?");
        $stmt->execute([$migration]);
    }
}