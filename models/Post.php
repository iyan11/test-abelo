<?php
namespace models;

use system\ORM;
use system\DatabaseSystem;

class Post extends ORM
{
    protected $table = 'posts';
    protected $primaryKey = 'id';
    protected array $fillable = [
        'title',
        'description',
        'slug',
        'photo',
        'content',
        'views',
        'created_at',
        'updated_at'
    ];
    protected array $hidden = [];

    public function __construct()
    {
        parent::__construct();

        if (empty($this->attributes['views'])) {
            $this->attributes['views'] = 0;
        }
    }

    public function categories(): array
    {
        $db = DatabaseSystem::getInstance();
        $sql = "SELECT c.* FROM categories c 
                INNER JOIN category_post cp ON cp.category_id = c.id 
                WHERE cp.post_id = :post_id";

        $result = $db->query($sql, [':post_id' => $this->id])->fetchAll();

        $categories = [];
        foreach ($result as $data) {
            $category = new Category();
            $category->hydrate($data);
            $category->exists = true;
            $categories[] = $category;
        }

        return $categories;
    }

    public function getCategory(): ?Category
    {
        $categories = $this->categories();
        return !empty($categories) ? $categories[0] : null;
    }

    public function getSimilarPosts(int $limit = 3): array
    {
        $categories = $this->categories();
        if (empty($categories)) {
            return [];
        }

        $categoryIds = array_map(function($cat) {
            return $cat->id;
        }, $categories);

        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

        $db = DatabaseSystem::getInstance();
        $sql = "SELECT DISTINCT p.* FROM posts p 
                INNER JOIN category_post cp ON cp.post_id = p.id 
                WHERE cp.category_id IN ({$placeholders}) 
                AND p.id != ? 
                ORDER BY p.created_at DESC 
                LIMIT ?";

        $params = array_merge($categoryIds, [$this->id, $limit]);
        $stmt = $db->query($sql, $params);
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

    public static function popular(int $limit = 10): array
    {
        $instance = new static();
        $db = DatabaseSystem::getInstance();
        $sql = "SELECT * FROM {$instance->table} ORDER BY views DESC LIMIT :limit";
        $stmt = $db->query($sql, [':limit' => $limit]);
        $results = $stmt->fetchAll();

        $posts = [];
        foreach ($results as $data) {
            $post = new static();
            $post->hydrate($data);
            $post->exists = true;
            $posts[] = $post;
        }

        return $posts;
    }

    public function incrementViews(): bool
    {
        $this->attributes['views']++;
        return $this->save();
    }

    public function generateSlug(): string
    {
        $slug = strtolower(trim($this->title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);

        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        $db = DatabaseSystem::getInstance();
        $stmt = $db->query("SELECT id FROM {$this->table} WHERE slug = :slug", [':slug' => $slug]);
        $existing = $stmt->fetch();
        return $existing && $existing['id'] !== ($this->id ?? null);
    }

    public function getUrl(): string
    {
        return '/posts/' . ($this->slug ?? $this->id);
    }

    public function getFormattedDate(string $format = 'd.m.Y H:i'): string
    {
        return date($format, strtotime($this->created_at));
    }

    public function getReadingTime(): int
    {
        preg_match_all('/[\p{L}\p{N}\']+/u', strip_tags((string) $this->content), $matches);
        $words = count($matches[0]);

        return max(1, (int) ceil($words / 200));
    }

    public function save(): bool
    {
        if (empty($this->slug) && !empty($this->title)) {
            $this->slug = $this->generateSlug();
        }

        if (empty($this->created_at)) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        $this->updated_at = date('Y-m-d H:i:s');

        return parent::save();
    }
}
