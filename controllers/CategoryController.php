<?php
namespace controllers;

use models\Category;
use models\Post;
use Smarty\Exception;
use system\Controller;
use system\DatabaseSystem;

class CategoryController extends Controller
{
    private const int PER_PAGE = 5;

    /**
     * @throws Exception
     */
    public function show(string $slug): string
    {
        $db = DatabaseSystem::getInstance();
        $stmt = $db->query('SELECT * FROM categories WHERE slug = :slug LIMIT 1', [':slug' => $slug]);
        $categoryData = $stmt->fetch();

        if (!$categoryData) {
            http_response_code(404);
            return $this->render('errors/404', ['title' => 'Категория не найдена']);
        }

        $category = new Category();
        $category->hydrate($categoryData);
        $category->exists = true;

        $sort = $_GET['sort'] ?? 'date_desc';
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $orderBy = match ($sort) {
            'views' => 'p.views DESC',
            'date_asc' => 'p.created_at ASC',
            default => 'p.created_at DESC',
        };

        $countSql = 'SELECT COUNT(*) as total FROM category_post WHERE category_id = :category_id';
        $countResult = $db->query($countSql, [':category_id' => (int) $category->id])->fetch();
        $total = (int) ($countResult['total'] ?? 0);

        $sql = "SELECT p.* FROM posts p
                INNER JOIN category_post cp ON cp.post_id = p.id
                WHERE cp.category_id = :category_id
                ORDER BY {$orderBy}
                LIMIT :limit OFFSET :offset";

        $result = $db->query($sql, [
            ':category_id' => (int) $category->id,
            ':limit' => self::PER_PAGE,
            ':offset' => $offset,
        ])->fetchAll();

        $posts = [];
        foreach ($result as $data) {
            $post = new Post();
            $post->hydrate($data);
            $post->exists = true;
            $posts[] = $post;
        }

        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        return $this->render('category/show', [
            'title' => $category->name,
            'category' => $category,
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'sort' => $sort,
        ]);
    }
}
