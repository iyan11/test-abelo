<?php
namespace system;

use PDO;

class Schema
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);

        $sql = $this->buildCreateTableSQL($blueprint);
        $this->db->exec($sql);

        echo "✓ Created table: {$table}\n";
    }

    public function alter(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);

        $sql = $this->buildAlterTableSQL($blueprint);
        if ($sql) {
            $this->db->exec($sql);
            echo "✓ Altered table: {$table}\n";
        }
    }

    public function drop(string $table): void
    {
        $this->db->exec("DROP TABLE {$table}");
        echo "✓ Dropped table: {$table}\n";
    }

    public function dropIfExists(string $table): void
    {
        $this->db->exec("DROP TABLE IF EXISTS {$table}");
        echo "✓ Dropped table if exists: {$table}\n";
    }

    private function buildCreateTableSQL(Blueprint $blueprint): string
    {
        $columns = [];
        $indexes = [];

        foreach ($blueprint->getColumns() as $column) {
            $columns[] = $this->buildColumnSQL($column);
        }

        foreach ($blueprint->getIndexes() as $index) {
            $indexes[] = $this->buildIndexSQL($index);
        }

        $allDefinitions = array_merge($columns, $indexes);
        $sql = "CREATE TABLE {$blueprint->getTable()} (\n    " . implode(",\n    ", $allDefinitions) . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        return $sql;
    }

    private function buildColumnSQL(array $column): string
    {
        $sql = "`{$column['name']}` {$column['type']}";

        // Добавляем UNSIGNED если есть (только для числовых типов)
        if (isset($column['unsigned']) && $column['unsigned'] === true) {
            // Проверяем, что тип числовой
            $numericTypes = ['INT', 'BIGINT', 'TINYINT', 'SMALLINT', 'MEDIUMINT'];
            $typeUpper = strtoupper($column['type']);
            foreach ($numericTypes as $numericType) {
                if (strpos($typeUpper, $numericType) === 0) {
                    $sql .= " UNSIGNED";
                    break;
                }
            }
        }

        // NULL или NOT NULL
        if ($column['nullable'] ?? false) {
            $sql .= " NULL";
        } else {
            $sql .= " NOT NULL";
        }

        // DEFAULT
        if (isset($column['default'])) {
            $default = is_string($column['default']) ? "'{$column['default']}'" : $column['default'];
            $sql .= " DEFAULT {$default}";
        }

        // AUTO_INCREMENT
        if ($column['auto_increment'] ?? false) {
            $sql .= " AUTO_INCREMENT";
        }

        // COMMENT
        if (isset($column['comment'])) {
            $sql .= " COMMENT '{$column['comment']}'";
        }

        return $sql;
    }

    private function buildIndexSQL(array $index): string
    {
        if ($index['type'] === 'primary') {
            return "PRIMARY KEY ({$index['columns']})";
        } elseif ($index['type'] === 'unique') {
            return "UNIQUE KEY `{$index['name']}` ({$index['columns']})";
        } else {
            return "KEY `{$index['name']}` ({$index['columns']})";
        }
    }

    private function buildAlterTableSQL(Blueprint $blueprint): ?string
    {
        $alterations = [];

        foreach ($blueprint->getAddedColumns() as $column) {
            $alterations[] = "ADD COLUMN " . $this->buildColumnSQL($column);
        }

        foreach ($blueprint->getModifiedColumns() as $column) {
            $alterations[] = "MODIFY COLUMN " . $this->buildColumnSQL($column);
        }

        foreach ($blueprint->getDroppedColumns() as $columnName) {
            $alterations[] = "DROP COLUMN `{$columnName}`";
        }

        if (empty($alterations)) {
            return null;
        }

        return "ALTER TABLE {$blueprint->getTable()}\n    " . implode(",\n    ", $alterations);
    }

    public static function hasTable(string $table): bool
    {
        $db = DatabaseSystem::getInstance()->getConnection();
        $stmt = $db->query("SHOW TABLES LIKE '{$table}'");
        return $stmt->rowCount() > 0;
    }
}