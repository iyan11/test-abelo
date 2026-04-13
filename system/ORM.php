<?php
namespace system;

abstract class ORM
{
    protected $table = null;
    protected $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;
    protected DatabaseSystem $db;

    public function __construct()
    {
        $this->db = DatabaseSystem::getInstance();

        if ($this->table === null) {
            $className = (new \ReflectionClass($this))->getShortName();
            $this->table = strtolower($className) . 's';
        }
    }

    public static function where(string $column, string $operator, $value): QueryBuilder
    {
        $instance = new static();
        return (new QueryBuilder($instance))->where($column, $operator, $value);
    }

    public static function first(): ?self
    {
        return static::where('id', '>', 0)->first();
    }

    public static function find($id): ?self
    {
        $instance = new static();
        $result = $instance->db->query(
            "SELECT * FROM `{$instance->table}` WHERE `{$instance->primaryKey}` = :id LIMIT 1",
            [':id' => $id]
        )->fetch();

        if (!$result) {
            return null;
        }

        $instance->hydrate($result);
        $instance->exists = true;

        return $instance;
    }

    public static function all(): array
    {
        $instance = new static();
        $results = $instance->db->query("SELECT * FROM `{$instance->table}`")->fetchAll();

        $items = [];
        foreach ($results as $result) {
            $item = new static();
            $item->hydrate($result);
            $item->exists = true;
            $items[] = $item;
        }

        return $items;
    }

    public static function create(array $data): self
    {
        $instance = new static();
        $instance->fill($data);
        $instance->save();

        return $instance;
    }

    public function fill(array $data): self
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable, true)) {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    public function hydrate(array $data): void
    {
        $this->attributes = $data;
        $this->syncOriginal();
    }

    public function save(): bool
    {
        if ($this->exists) {
            return $this->update();
        }

        return $this->insert();
    }

    private function insert(): bool
    {
        $data = $this->getFillableAttributes();
        if (empty($data)) {
            return false;
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn ($column) => ':' . $column, $columns);

        $sql = "INSERT INTO `{$this->table}` (`" . implode('`, `', $columns) . "`)"
            . ' VALUES (' . implode(', ', $placeholders) . ')';

        $this->db->query($sql, $data);

        if ($this->primaryKey === 'id') {
            $this->attributes[$this->primaryKey] = $this->db->lastInsertId();
        }

        $this->exists = true;
        $this->syncOriginal();

        return true;
    }

    private function update(): bool
    {
        $data = $this->getFillableAttributes();
        if (empty($data)) {
            return false;
        }

        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "`{$column}` = :{$column}";
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets)
            . " WHERE `{$this->primaryKey}` = :_primaryKey";

        $data['_primaryKey'] = $this->attributes[$this->primaryKey];
        $this->db->query($sql, $data);
        $this->syncOriginal();

        return true;
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $this->db->query($sql, [':id' => $this->attributes[$this->primaryKey]]);
        $this->exists = false;

        return true;
    }

    protected function getFillableAttributes(): array
    {
        $data = [];
        foreach ($this->fillable as $field) {
            if (array_key_exists($field, $this->attributes)) {
                $data[$field] = $this->attributes[$field];
            }
        }

        return $data;
    }

    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }

    public function toArray(): array
    {
        $data = $this->attributes;

        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    public function __get($name)
    {
        if ($name === 'exists') {
            return $this->exists;
        }

        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value): void
    {
        if ($name === 'exists') {
            $this->exists = (bool) $value;
            return;
        }

        $this->attributes[$name] = $value;
    }

    public function __isset($name): bool
    {
        if ($name === 'exists') {
            return true;
        }

        return isset($this->attributes[$name]);
    }

    public function getTableName(): string
    {
        return $this->table;
    }
}
