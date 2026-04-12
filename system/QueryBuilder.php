<?php
namespace system;

class QueryBuilder
{
    private ORM $model;
    private ?string $table;
    private array $wheres = [];
    private array $orderBy = [];
    private int|null $limit = null;
    private int|null $offset = null;
    private array $params = [];

    public function __construct(ORM $model)
    {
        $this->model = $model;
        $this->table = $model->table;
    }

    public function where(string $column, string $operator, $value): self
    {
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function first(): ?ORM
    {
        $this->limit = 1;
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function get(): array
    {
        $sql = "SELECT * FROM $this->table";

        if (!empty($this->wheres)) {
            $conditions = [];
            foreach ($this->wheres as $index => $where) {
                $paramKey = ":where_$index";
                $conditions[] = "{$where['column']} {$where['operator']} $paramKey";
                $this->params[$paramKey] = $where['value'];
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT $this->limit";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET $this->offset";
        }

        $db = DatabaseSystem::getInstance();
        $results = $db->query($sql, $this->params)->fetchAll();

        $items = [];
        $className = get_class($this->model);

        foreach ($results as $result) {
            $item = new $className();
            $item->hydrate($result);
            $item->exists = true;
            $items[] = $item;
        }

        return $items;
    }

    public function update(array $data): bool
    {
        $model = $this->first();
        if (!$model) {
            return false;
        }

        $model->fill($data);
        return $model->save();
    }

    public function delete(): bool
    {
        $models = $this->get();
        $success = true;

        foreach ($models as $model) {
            if (!$model->delete()) {
                $success = false;
            }
        }

        return $success;
    }
}