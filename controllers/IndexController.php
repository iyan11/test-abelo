<?php
namespace controllers;

use models\Category;
use models\Post;
use Smarty\Exception;
use system\Controller;
use system\DatabaseSystem;

class IndexController extends Controller
{
    /**
     * @throws Exception
     */
    public function index(): string
    {
        $db = DatabaseSystem::getInstance();
        $postsCount = $db->query('SELECT COUNT(*) as count FROM posts')->fetch();

        if ((int) ($postsCount['count'] ?? 0) === 0) {
            return $this->render('home/index', [
                'title' => 'Главная страница',
                'categories' => [],
                'message' => 'Нет статей. Запустите команду php commands/seed.php внутри контейнера приложения, чтобы заполнить базу.',
            ]);
        }

        $sql = "SELECT c.* FROM categories c
                INNER JOIN category_post cp ON cp.category_id = c.id
                GROUP BY c.id
                ORDER BY c.name";
        $result = $db->query($sql)->fetchAll();

        $categories = [];
        foreach ($result as $data) {
            $category = new Category();
            $category->hydrate($data);
            $category->exists = true;

            $recentSql = "SELECT p.* FROM posts p
                          INNER JOIN category_post cp ON cp.post_id = p.id
                          WHERE cp.category_id = :category_id
                          ORDER BY p.created_at DESC
                          LIMIT 3";
            $recentResult = $db->query($recentSql, [':category_id' => (int) $category->id])->fetchAll();

            $recentPosts = [];
            foreach ($recentResult as $postData) {
                $post = new Post();
                $post->hydrate($postData);
                $post->exists = true;
                $recentPosts[] = $post;
            }

            $category->recentPosts = $recentPosts;
            $categories[] = $category;
        }

        return $this->render('home/index', [
            'title' => 'Главная страница',
            'categories' => $categories,
        ]);
    }
}
