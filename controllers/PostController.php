<?php
namespace controllers;

use models\Category;
use models\Post;
use Smarty\Exception;
use system\Controller;
use system\DatabaseSystem;

class PostController extends Controller
{
    /**
     * @throws Exception
     */
    public function show(string $slug): string
    {
        $db = DatabaseSystem::getInstance();
        $stmt = $db->query('SELECT * FROM posts WHERE slug = :slug LIMIT 1', [':slug' => $slug]);
        $postData = $stmt->fetch();

        if (!$postData) {
            http_response_code(404);
            return $this->render('errors/404', ['title' => 'Статья не найдена']);
        }

        $post = new Post();
        $post->hydrate($postData);
        $post->exists = true;

        $db->query('UPDATE posts SET views = views + 1 WHERE id = :id', [':id' => (int) $post->id]);
        $post->views = ((int) $postData['views']) + 1;

        $categoriesData = $db->query(
            'SELECT c.* FROM categories c
             INNER JOIN category_post cp ON cp.category_id = c.id
             WHERE cp.post_id = :post_id',
            [':post_id' => (int) $post->id]
        )->fetchAll();

        $categories = [];
        foreach ($categoriesData as $catData) {
            $category = new Category();
            $category->hydrate($catData);
            $category->exists = true;
            $categories[] = $category;
        }

        $similarPosts = [];
        if (!empty($categories)) {
            $categoryIds = array_map(static fn ($cat) => (int) $cat->id, $categories);
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $params = array_merge($categoryIds, [(int) $post->id]);

            $similarSql = "SELECT DISTINCT p.* FROM posts p
                           INNER JOIN category_post cp ON cp.post_id = p.id
                           WHERE cp.category_id IN ({$placeholders})
                           AND p.id != ?
                           ORDER BY p.created_at DESC
                           LIMIT 3";

            $similarData = $db->query($similarSql, $params)->fetchAll();

            foreach ($similarData as $data) {
                $similarPost = new Post();
                $similarPost->hydrate($data);
                $similarPost->exists = true;
                $similarPosts[] = $similarPost;
            }
        }

        return $this->render('post/show', [
            'title' => $post->title,
            'post' => $post,
            'categories' => $categories,
            'similarPosts' => $similarPosts,
        ]);
    }
}
