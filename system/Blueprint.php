<?php
namespace system;

use Exception;

class Blueprint
{
    private string $table;
    private array $columns = [];
    private array $indexes = [];
    private array $addedColumns = [];
    private array $modifiedColumns = [];
    private array $droppedColumns = [];
    private ?array $lastColumn = null;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(string $name = 'id'): self
    {
        return $this->bigIncrements($name);
    }

    public function increments(string $name): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => 'INT',
            'auto_increment' => true,
            'nullable' => false
        ]);
        $this->primary($name);
        return $this;
    }

    public function bigIncrements(string $name): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => 'BIGINT',
            'auto_increment' => true,
            'nullable' => false
        ]);
        $this->primary($name);
        return $this;
    }

    public function string(string $name, int $length = 255): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => "VARCHAR({$length})",
            'nullable' => false
        ]);
        return $this;
    }

    public function text(string $name): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => 'TEXT',
            'nullable' => false
        ]);
        return $this;
    }

    public function longText(string $name): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => 'LONGTEXT',
            'nullable' => false
        ]);
        return $this;
    }

    public function integer(string $name): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => 'INT',
            'nullable' => false,
            'unsigned' => false
        ]);
        return $this;
    }

    public function bigInteger(string $name): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => 'BIGINT',
            'nullable' => false,
            'unsigned' => false
        ]);
        return $this;
    }

    public function boolean(string $name): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => 'TINYINT(1)',
            'nullable' => false,
            'default' => 0
        ]);
        return $this;
    }

    public function timestamp(string $name): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => 'TIMESTAMP',
            'nullable' => true,
            'default' => null
        ]);
        return $this;
    }

    public function timestamps(): self
    {
        $this->timestamp('created_at');
        $this->timestamp('updated_at');
        return $this;
    }

    public function date(string $name): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => 'DATE',
            'nullable' => true
        ]);
        return $this;
    }

    public function datetime(string $name): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => 'DATETIME',
            'nullable' => true
        ]);
        return $this;
    }

    public function decimal(string $name, int $total = 10, int $places = 2): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => "DECIMAL({$total},{$places})",
            'nullable' => false
        ]);
        return $this;
    }

    public function float(string $name): self
    {
        $this->addColumn([
            'name' => $name,
            'type' => 'FLOAT',
            'nullable' => false
        ]);
        return $this;
    }

    public function enum(string $name, array $values): self
    {
        $valuesStr = implode("', '", $values);
        $this->addColumn([
            'name' => $name,
            'type' => "ENUM('{$valuesStr}')",
            'nullable' => false
        ]);
        return $this;
    }

    public function foreignId(string $name): self
    {
        // foreignId создает обычный INT, а не UNSIGNED
        $this->addColumn([
            'name' => $name,
            'type' => 'INT',
            'nullable' => false
        ]);
        return $this;
    }

    // Модификаторы
    public function nullable(): self
    {
        if ($this->lastColumn) {
            $this->lastColumn['nullable'] = true;
            $this->updateLastColumn($this->lastColumn);
        }
        return $this;
    }

    public function default($value): self
    {
        if ($this->lastColumn) {
            $this->lastColumn['default'] = $value;
            $this->updateLastColumn($this->lastColumn);
        }
        return $this;
    }

    public function unsigned(): self
    {
        if ($this->lastColumn) {
            $this->lastColumn['unsigned'] = true;
            $this->updateLastColumn($this->lastColumn);
        }
        return $this;
    }

    public function comment(string $comment): self
    {
        if ($this->lastColumn) {
            $this->lastColumn['comment'] = $comment;
            $this->updateLastColumn($this->lastColumn);
        }
        return $this;
    }

    public function after(string $column): self
    {
        if ($this->lastColumn) {
            $this->lastColumn['after'] = $column;
            $this->updateLastColumn($this->lastColumn);
        }
        return $this;
    }

    // Индексы
    public function primary(string|array $columns): self
    {
        $columnsStr = is_array($columns) ? implode(', ', $columns) : $columns;
        $this->indexes[] = [
            'type' => 'primary',
            'columns' => $columnsStr,
            'name' => 'primary'
        ];
        return $this;
    }

    /**
     * @throws Exception
     */
    public function unique(string|array|null $columns = null, ?string $name = null): self
    {
        if ($columns === null) {
            if ($this->lastColumn && isset($this->lastColumn['name'])) {
                $columns = $this->lastColumn['name'];
            } else {
                throw new Exception("No column specified for unique index");
            }
        }

        $columnsStr = is_array($columns) ? implode(', ', $columns) : $columns;
        $name = $name ?? "unique_" . str_replace(', ', '_', $columnsStr);

        $this->indexes[] = [
            'type' => 'unique',
            'columns' => $columnsStr,
            'name' => $name
        ];
        return $this;
    }

    public function index(string|array $columns, ?string $name = null): self
    {
        $columnsStr = is_array($columns) ? implode(', ', $columns) : $columns;
        $name = $name ?? "idx_" . str_replace(', ', '_', $columnsStr);
        $this->indexes[] = [
            'type' => 'index',
            'columns' => $columnsStr,
            'name' => $name
        ];
        return $this;
    }

    // Для ALTER TABLE

    public function modifyColumn(string $name, callable $callback): self
    {
        $column = ['name' => $name];
        $callback(new class($column) {
            private array $column;
            public function __construct(&$column) { $this->column = &$column; }
            public function string(int $length = 255) { $this->column['type'] = "VARCHAR({$length})"; return $this; }
            public function text() { $this->column['type'] = 'TEXT'; return $this; }
            public function integer() { $this->column['type'] = 'INT'; return $this; }
            public function nullable() { $this->column['nullable'] = true; return $this; }
        });

        $this->modifiedColumns[] = $column;
        return $this;
    }

    public function dropColumn(string $name): self
    {
        $this->droppedColumns[] = $name;
        return $this;
    }

    // Вспомогательные методы
    private function addColumn(array $column): void
    {
        $this->addColumnToArray($column);
        $this->lastColumn = $column;
    }

    private function addColumnToArray(array $column): void
    {
        $this->columns[] = $column;
    }

    private function updateLastColumn(array $column): void
    {
        $index = count($this->columns) - 1;
        if ($index >= 0) {
            $this->columns[$index] = $column;
        }
    }

    // Getters
    public function getTable(): string { return $this->table; }
    public function getColumns(): array { return $this->columns; }
    public function getIndexes(): array { return $this->indexes; }
    public function getAddedColumns(): array { return $this->addedColumns; }
    public function getModifiedColumns(): array { return $this->modifiedColumns; }
    public function getDroppedColumns(): array { return $this->droppedColumns; }
}