<?php
namespace models;

use system\DatabaseSystem;
use system\ORM;

class Category extends ORM
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected array $fillable = ['name', 'slug', 'description'];
    protected array $hidden = [];

    public function posts(): array
    {
        $db = DatabaseSystem::getInstance();
        $sql = "SELECT p.* FROM posts p 
                INNER JOIN category_post cp ON cp.post_id = p.id 
                WHERE cp.category_id = :category_id 
                ORDER BY p.created_at DESC";

        $result = $db->query($sql, [':category_id' => $this->id])->fetchAll();

        $posts = [];
        foreach ($result as $data) {
            $post = new Post();
            $post->hydrate($data);
            $post->exists = true;
            $posts[] = $post;
        }

        return $posts;
    }

    public function getRecentPosts(int $limit = 3): array
    {
        $db = DatabaseSystem::getInstance();
        $sql = "SELECT p.* FROM posts p 
                INNER JOIN category_post cp ON cp.post_id = p.id 
                WHERE cp.category_id = :category_id 
                ORDER BY p.created_at DESC 
                LIMIT :limit";

        $stmt = $db->query($sql, [':category_id' => $this->id, ':limit' => $limit]);
        $result = $stmt->fetchAll();

        $posts = [];
        foreach ($result as $data) {
            $post = new Post();
            $post->hydrate($data);
            $post->exists = true;
            $posts[] = $post;
        }

        return $posts;
    }

    public function getPostsCount(): int
    {
        $db = DatabaseSystem::getInstance();
        $sql = "SELECT COUNT(*) as count FROM category_post WHERE category_id = :category_id";
        $result = $db->query($sql, [':category_id' => $this->id])->fetch();
        return (int)$result['count'];
    }

    public function getUrl(): string
    {
        return '/category/' . ($this->slug ?? $this->id);
    }
}
