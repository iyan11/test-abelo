<?php

use system\Migration;
use system\Blueprint;

class CategoryPostTable extends Migration
{
    public function up(): void
    {
        $this->create('category_post', function (Blueprint $table) {
            $table->id();
            // Используем BIGINT для совместимости с id в таблицах categories и posts
            $table->bigInteger('category_id');
            $table->bigInteger('post_id');
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index('category_id');
            $table->index('post_id');
            $table->unique(['category_id', 'post_id']);
        });

        // Добавляем внешние ключи после создания таблицы
        $this->addForeignKeys();
    }

    private function addForeignKeys(): void
    {
        try {
            // Проверяем существование таблиц перед добавлением FK
            $tables = $this->db->query("SHOW TABLES LIKE 'categories'")->fetchAll();
            if (count($tables) > 0) {
                $this->db->exec("
                    ALTER TABLE category_post 
                    ADD CONSTRAINT fk_category_post_category 
                    FOREIGN KEY (category_id) 
                    REFERENCES categories(id) 
                    ON DELETE CASCADE
                ");
            }

            $tables = $this->db->query("SHOW TABLES LIKE 'posts'")->fetchAll();
            if (count($tables) > 0) {
                $this->db->exec("
                    ALTER TABLE category_post 
                    ADD CONSTRAINT fk_category_post_post 
                    FOREIGN KEY (post_id) 
                    REFERENCES posts(id) 
                    ON DELETE CASCADE
                ");
            }
        } catch (PDOException $e) {
            echo "Note: Foreign keys not added: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        $this->dropForeignKeys();
        $this->dropIfExists('category_post');
    }

    private function dropForeignKeys(): void
    {
        try {
            $this->db->exec("ALTER TABLE category_post DROP FOREIGN KEY fk_category_post_category");
            $this->db->exec("ALTER TABLE category_post DROP FOREIGN KEY fk_category_post_post");
        } catch (PDOException $e) {
            // Игнорируем ошибки при удалении
        }
    }
}