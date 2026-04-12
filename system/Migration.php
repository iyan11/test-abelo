<?php
namespace system;

use PDO;

abstract class Migration
{
    protected PDO $db;
    protected string $table;
    protected Schema $schema;

    public function __construct()
    {
        $this->db = DatabaseSystem::getInstance()->getConnection();
        $this->schema = new Schema($this->db);
    }

    abstract public function up(): void;
    abstract public function down(): void;

    protected function create(string $table, callable $callback): void
    {
        $this->schema->create($table, $callback);
    }

    protected function alter(string $table, callable $callback): void
    {
        $this->schema->alter($table, $callback);
    }

    protected function drop(string $table): void
    {
        $this->schema->drop($table);
    }

    protected function dropIfExists(string $table): void
    {
        $this->schema->dropIfExists($table);
    }
}