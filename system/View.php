<?php
namespace system;

use Smarty\Exception;
use Smarty\Smarty;

class View
{
    private static ?View $instance = null;
    private Smarty $smarty;

    /**
     * @throws Exception
     */
    private function __construct()
    {
        $this->smarty = new Smarty();

        $templateDir = __DIR__ . '/../views/templates/';
        $compileDir = __DIR__ . '/../views/templates_c/';
        $cacheDir = __DIR__ . '/../system/cache/';
        $configDir = __DIR__ . '/../system/configs/';

        foreach ([$compileDir, $cacheDir, $configDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        $this->smarty->setTemplateDir($templateDir);
        $this->smarty->setCompileDir($compileDir);
        $this->smarty->setCacheDir($cacheDir);
        $this->smarty->setConfigDir($configDir);
        $this->smarty->setCompileCheck(true);
        $this->smarty->setCaching(false);
        $this->smarty->debugging = false;

        $this->assign('app_name', getenv('APP_NAME') ?: 'My App');
        $this->assign('app_url', getenv('APP_URL') ?: 'http://localhost');
        $this->assign('current_year', date('Y'));

        $this->registerCustomModifiers();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function assign(string $key, $value): self
    {
        $this->smarty->assign($key, $value);
        return $this;
    }

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

    /**
     * @throws Exception
     */
    public function registerPlugin(string $type, string $name, callable $callback): void
    {
        $this->smarty->registerPlugin($type, $name, $callback);
    }

    /**
     * @throws Exception
     */
    public function clearCache(?string $template = null): void
    {
        if ($template) {
            $this->smarty->clearCache($template . '.tpl');
            return;
        }

        $this->smarty->clearAllCache();
    }

    /**
     * @throws Exception
     */
    public function registerCustomModifiers(): void
    {
        $this->smarty->registerPlugin('modifier', 'date_format_custom', function ($timestamp, $format = 'd.m.Y') {
            return date($format, strtotime((string) $timestamp));
        });

        $this->smarty->registerPlugin('modifier', 'truncate', function ($string, $length = 100) {
            $string = (string) $string;
            if (mb_strlen($string) <= $length) {
                return $string;
            }

            return mb_substr($string, 0, $length) . '...';
        });
    }

    public function getSmarty(): Smarty
    {
        return $this->smarty;
    }

    /**
     * @throws Exception
     */
    public function fetch(string $template, array $data = []): string
    {
        return $this->render($template, $data);
    }
}
