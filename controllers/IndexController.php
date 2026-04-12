<?php
namespace controllers;

use JetBrains\PhpStorm\NoReturn;
use Smarty\Exception;
use system\Controller;

class IndexController extends Controller
{
    /**
     * @throws Exception
     */
    #[NoReturn]
    public function index(): void
    {
        $this->render('index', [
            'title' => 'Главная страница',
            'user' => [
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ]
        ]);
    }
}