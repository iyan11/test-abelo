<?php
use system\Router;

Router::get('/', 'IndexController@index');

Router::get('/category/{slug}', 'CategoryController@show');

Router::get('/posts/{slug}', 'PostController@show');