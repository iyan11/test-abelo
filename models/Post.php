<?php
namespace models;

use system\ORM;
use system\DatabaseSystem;

class Post extends ORM
{
    private int $id;
    private string $title;
    private string $description;
    private string $slug;
    private string $photo;
    private string $content;
    private int $views;
    private string $created_at;
    private string $updated_at;

    protected ?string $table = 'posts';
    protected string $primaryKey = 'id';
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

    // Конструктор с дополнительной инициализацией
    public function __construct()
    {
        parent::__construct();

        if (empty($this->attributes['views'])) {
            $this->attributes['views'] = 0;
        }
    }

    // ========== Отношения ==========

    /**
     * Отношение к категориям (многие ко многим)
     */
    public function categories(): array
    {
        // Для many-to-many нужна промежуточная таблица
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

    // ========== Скоупы (условия) ==========


    /**
     * Популярные посты (по просмотрам)
     */
    public static function popular(int $limit = 10): array
    {
        return self::orderBy('views', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Похожие посты
     */
    public function similar(int $limit = 5): array
    {
        return self::where('id', '!=', $this->id)
            ->where('category_id', '=', $this->category_id)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    // ========== Методы ==========

    /**
     * Увеличить счетчик просмотров
     */
    public function incrementViews(): bool
    {
        $this->views++;
        return $this->save();
    }

    /**
     * Сгенерировать slug из заголовка
     */
    public function generateSlug(): string
    {
        $slug = strtolower(trim($this->title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);

        // Проверяем уникальность
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Проверить существование slug
     */
    private function slugExists(string $slug): bool
    {
        $existing = self::where('slug', '=', $slug)->first();
        return $existing && $existing->id !== ($this->id ?? null);
    }

    /**
     * Получить URL поста
     */
    public function getUrl(): string
    {
        return '/posts/' . ($this->slug ?? $this->id);
    }


    /**
     * Форматированная дата публикации
     */
    public function getFormattedDate(string $format = 'd.m.Y H:i'): string
    {
        return date($format, strtotime($this->created_at));
    }

    /**
     * Получить время чтения в минутах
     */
    public function getReadingTime(): int
    {
        $words = str_word_count(strip_tags($this->content));
        return max(1, ceil($words / 200)); // 200 слов в минуту
    }

    // ========== Переопределение методов ORM ==========

    /**
     * Автоматическая генерация slug перед сохранением
     */
    public function save(): bool
    {
        if (empty($this->slug) && !empty($this->title)) {
            $this->slug = $this->generateSlug();
        }

        return parent::save();
    }
}