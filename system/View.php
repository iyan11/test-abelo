<?php
namespace system;

use Smarty\Exception;
use Smarty\Smarty;

class View
{
    private static ?View $instance = null;
    private Smarty $smarty {
        get {
            return $this->smarty;
        }
    }

    private function __construct()
    {
        $this->smarty = new Smarty();

        // Настройка путей
        $this->smarty->setTemplateDir(__DIR__ . '/../views/templates/');
        $this->smarty->setCompileDir(__DIR__ . '/../views/templates_c/');
        $this->smarty->setCacheDir(__DIR__ . '/../system/cache/');
        $this->smarty->setConfigDir(__DIR__ . '/../system/configs/');

        // Настройки для разработки
        $this->smarty->setCompileCheck(true);
        $this->smarty->setCaching(false);
        $this->smarty->debugging = false;

        // Убираем устаревший assignGlobal, используем обычный assign
        $this->assign('app_name', getenv('APP_NAME') ?: 'My App');
        $this->assign('app_url', getenv('APP_URL') ?: 'http://localhost');
        $this->assign('current_year', date('Y'));
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Назначить переменную шаблону (работает как глобальная)
    public function assign(string $key, $value): self
    {
        $this->smarty->assign($key, $value);
        return $this;
    }

    // Отобразить шаблон

    /**
     * @throws Exception
     */
    public function render(string $template, array $data = []): string
    {
        foreach ($data as $key => $value) {
            $this->assign($key, $value);
        }

        return $this->smarty->fetch($template . '.tpl');
    }

    // Вывести шаблон

    /**
     * @throws Exception
     */
    public function display(string $template, array $data = []): void
    {
        foreach ($data as $key => $value) {
            $this->assign($key, $value);
        }

        $this->smarty->display($template . '.tpl');
    }

    // Получить объект Smarty для прямого доступа

    // Добавить плагин или модификатор
    /**
     * @throws Exception
     */
    public function registerPlugin(string $type, string $name, callable $callback): void
    {
        $this->smarty->registerPlugin($type, $name, $callback);
    }

    // Очистить кэш

    /**
     * @throws Exception
     */
    public function clearCache(?string $template = null): void
    {
        if ($template) {
            $this->smarty->clearCache($template . '.tpl');
        } else {
            $this->smarty->clearAllCache();
        }
    }

    // Регистрация пользовательских модификаторов

    /**
     * @throws Exception
     */
    public function registerCustomModifiers(): void
    {
        // Модификатор для форматирования даты
        $this->smarty->registerPlugin('modifier', 'date_format_custom', function($timestamp, $format = 'd.m.Y') {
            return date($format, strtotime($timestamp));
        });

        // Модификатор для обрезания текста
        $this->smarty->registerPlugin('modifier', 'truncate', function($string, $length = 100) {
            if (strlen($string) <= $length) {
                return $string;
            }
            return substr($string, 0, $length) . '...';
        });

        // Модификатор для преобразования в валюту
        $this->smarty->registerPlugin('modifier', 'currency', function($amount, $symbol = '$') {
            return $symbol . number_format($amount, 2);
        });
    }
}