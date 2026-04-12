<?php
namespace system;

use JetBrains\PhpStorm\NoReturn;
use Smarty\Exception;

class Controller
{
    protected View $view;
    protected Request $request;

    public function __construct()
    {
        $this->view = View::getInstance();
        $this->request = new Request();
    }

    // Отображение Smarty шаблона

    /**
     * @throws Exception
     */
    protected function render(string $view, array $data = []): void
    {
        $this->view->display($view, $data);
    }

    // JSON ответ
    #[NoReturn]
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Редирект
    #[NoReturn]
    protected function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: $url");
        exit;
    }

    // Назад
    #[NoReturn]
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
}