<?php

use system\Migration;
use system\Blueprint;

class PostsTable extends Migration
{
    public function up(): void
    {
        $this->create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->string('slug');
            $table->string('photo');
            $table->longText('content');
            $table->integer('views');
            $table->timestamps();

            $table->index('slug');
            $table->index('created_at');
        });
    }
    
    public function down(): void
    {
        $this->dropIfExists('posts');
    }
}