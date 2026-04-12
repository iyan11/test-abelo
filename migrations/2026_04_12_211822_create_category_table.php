<?php

use system\Migration;
use system\Blueprint;

class CategoryTable extends Migration
{
    public function up(): void
    {
        $this->create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->index('slug');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('categories');
    }
}