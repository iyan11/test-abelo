<?php
namespace seeders;

use system\DatabaseSystem;
use PDO;

class DatabaseSeeder
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DatabaseSystem::getInstance()->getConnection();
    }

    public function run(): void
    {
        echo "Запуск сидов...\n";

        $this->clearTables();
        $this->seedCategories();
        $this->seedPosts();
        $this->seedCategoryPostRelations();

        echo "Сиды прошли!\n";
    }

    private function clearTables(): void
    {
        $this->db->exec("SET FOREIGN_KEY_CHECKS=0");
        $this->db->exec("TRUNCATE TABLE category_post");
        $this->db->exec("TRUNCATE TABLE posts");
        $this->db->exec("TRUNCATE TABLE categories");
        $this->db->exec("SET FOREIGN_KEY_CHECKS=1");
        echo "✓ Cleared existing data\n";
    }

    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'Технологии', 'slug' => 'technology', 'description' => 'Новости и статьи о технологиях'],
            ['name' => 'Программирование', 'slug' => 'programming', 'description' => 'Всё о разработке ПО'],
            ['name' => 'Дизайн', 'slug' => 'design', 'description' => 'UI/UX, графика, творчество'],
            ['name' => 'Бизнес', 'slug' => 'business', 'description' => 'Стартапы, маркетинг, управление'],
            ['name' => 'Наука', 'slug' => 'science', 'description' => 'Научные открытия и исследования'],
        ];

        $stmt = $this->db->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");

        foreach ($categories as $cat) {
            $stmt->execute([$cat['name'], $cat['slug'], $cat['description']]);
            echo "✓ Created category: {$cat['name']}\n";
        }
    }

    private function seedPosts(): void
    {
        $posts = [
            [
                'title' => 'Введение в PHP 8',
                'description' => 'Новые возможности и улучшения в PHP 8',
                'slug' => 'intro-to-php-8',
                'photo' => '/uploads/php8.jpg',
                'content' => '<h2>Что нового в PHP 8?</h2><p>PHP 8 принес множество улучшений: JIT компилятор, атрибуты, match выражение, конструктор property promotion и многое другое...</p><p>Это действительно мощный апдейт, который делает PHP еще более производительным и удобным.</p>',
                'views' => 150,
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
            ],
            [
                'title' => 'Современный CSS: Grid и Flexbox',
                'description' => 'Полное руководство по использованию Grid и Flexbox',
                'slug' => 'modern-css-grid-flexbox',
                'photo' => '/uploads/css.jpg',
                'content' => '<h2>CSS Grid vs Flexbox</h2><p>Обе технологии мощные, но для разных задач. Grid для 2D, Flexbox для 1D раскладок...</p><p>В этой статье мы рассмотрим практические примеры использования обеих технологий.</p>',
                'views' => 98,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ],
            [
                'title' => '10 советов по оптимизации MySQL',
                'description' => 'Как ускорить работу вашей базы данных',
                'slug' => 'mysql-optimization-tips',
                'photo' => '/uploads/mysql.jpg',
                'content' => '<h2>Индексы, кэширование и структура таблиц</h2><p>Правильное использование индексов может ускорить запросы в 100 раз...</p><p>Рассмотрим основные принципы оптимизации MySQL запросов.</p>',
                'views' => 210,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
            [
                'title' => 'Основы UX дизайна',
                'description' => 'Как создать удобный интерфейс для пользователей',
                'slug' => 'ux-design-basics',
                'photo' => '/uploads/ux.jpg',
                'content' => '<h2>Принципы пользовательского опыта</h2><p>Хороший UX начинается с исследования аудитории...</p><p>В статье разберем основные принципы и лучшие практики.</p>',
                'views' => 75,
                'created_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-7 days'))
            ],
            [
                'title' => 'Как запустить стартап с нуля',
                'description' => 'Пошаговое руководство для начинающих предпринимателей',
                'slug' => 'startup-from-scratch',
                'photo' => '/uploads/startup.jpg',
                'content' => '<h2>От идеи до первой прибыли</h2><p>Запуск стартапа требует правильного подхода...</p><p>Рассказываем о главных этапах и типичных ошибках.</p>',
                'views' => 45,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'title' => 'Искусственный интеллект в 2024',
                'description' => 'Тренды и перспективы развития ИИ',
                'slug' => 'ai-trends-2024',
                'photo' => '/uploads/ai.jpg',
                'content' => '<h2>Будущее уже здесь</h2><p>Искусственный интеллект меняет мир вокруг нас...</p><p>Обзор самых интересных разработок в области ИИ.</p>',
                'views' => 320,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 days'))
            ],
        ];

        $stmt = $this->db->prepare("
            INSERT INTO posts (title, description, slug, photo, content, views, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($posts as $post) {
            $stmt->execute([
                $post['title'],
                $post['description'],
                $post['slug'],
                $post['photo'],
                $post['content'],
                $post['views'],
                $post['created_at'],
                $post['updated_at']
            ]);
            echo "Пост создан: {$post['title']}\n";
        }
    }

    private function seedCategoryPostRelations(): void
    {
        $posts = $this->db->query("SELECT id, slug FROM posts")->fetchAll(PDO::FETCH_ASSOC);
        $categories = $this->db->query("SELECT id, slug FROM categories")->fetchAll(PDO::FETCH_ASSOC);

        $postMap = [];
        foreach ($posts as $post) {
            $postMap[$post['slug']] = $post['id'];
        }

        $categoryMap = [];
        foreach ($categories as $category) {
            $categoryMap[$category['slug']] = $category['id'];
        }

        $relations = [
            'intro-to-php-8' => ['technology', 'programming'],
            'modern-css-grid-flexbox' => ['design', 'technology'],
            'mysql-optimization-tips' => ['programming', 'technology'],
            'ux-design-basics' => ['design'],
            'startup-from-scratch' => ['business'],
            'ai-trends-2024' => ['technology', 'science'],
        ];

        $stmt = $this->db->prepare("INSERT INTO category_post (category_id, post_id) VALUES (?, ?)");

        foreach ($relations as $postSlug => $categorySlugs) {
            if (!isset($postMap[$postSlug])) {
                echo "  Warning: Post '{$postSlug}' not found\n";
                continue;
            }

            $postId = $postMap[$postSlug];

            foreach ($categorySlugs as $catSlug) {
                if (!isset($categoryMap[$catSlug])) {
                    echo "  Warning: Category '{$catSlug}' not found\n";
                    continue;
                }

                $categoryId = $categoryMap[$catSlug];

                $check = $this->db->prepare("SELECT 1 FROM category_post WHERE category_id = ? AND post_id = ?");
                $check->execute([$categoryId, $postId]);

                if (!$check->fetch()) {
                    $stmt->execute([$categoryId, $postId]);
                    echo "Посты связаны: {$postSlug} -> {$catSlug}\n";
                }
            }
        }
    }
}