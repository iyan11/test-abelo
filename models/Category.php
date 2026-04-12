<?php
namespace models;

use system\ORM;

class Category extends ORM
{
    protected int $id;
    protected string $name;
    protected string $slug;
    protected string $description;

    protected ?string $table = 'categories';
    protected string $primaryKey = 'id';
    protected array $fillable = ['name', 'slug', 'description'];

    public function posts(): array
    {
        return $this->hasMany(Post::class, 'category_id');
    }

    public function getUrl(): string
    {
        return '/category/' . ($this->slug ?? $this->id);
    }
}