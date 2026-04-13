<?php
namespace system;

use Closure;
use Exception;

class Router
{
    private static array $routes = [];
    private static string $prefix = '';
    private static array $middlewares = [];

    public static function get(string $path, $handler, array $middlewares = []): void
    {
        self::addRoute('GET', $path, $handler, $middlewares);
    }

    public static function post(string $path, $handler, array $middlewares = []): void
    {
        self::addRoute('POST', $path, $handler, $middlewares);
    }

    public static function put(string $path, $handler, array $middlewares = []): void
    {
        self::addRoute('PUT', $path, $handler, $middlewares);
    }

    public static function delete(string $path, $handler, array $middlewares = []): void
    {
        self::addRoute('DELETE', $path, $handler, $middlewares);
    }

    // Группировка маршрутов
    public static function group(array $attributes, callable $callback): void
    {
        $oldPrefix = self::$prefix;
        $oldMiddlewares = self::$middlewares;

        if (isset($attributes['prefix'])) {
            self::$prefix = $oldPrefix . '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middlewares'])) {
            self::$middlewares = array_merge(self::$middlewares, $attributes['middlewares']);
        }

        $callback();

        self::$prefix = $oldPrefix;
        self::$middlewares = $oldMiddlewares;
    }

    private static function addRoute(string $method, string $path, $handler, array $middlewares = []): void
    {
        $path = self::$prefix . '/' . trim($path, '/');
        $path = $path === '' ? '/' : '/' . trim($path, '/');

        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => array_merge(self::$middlewares, $middlewares),
            'params' => []
        ];
    }

    /**
     * @throws Exception
     */
    public static function dispatch(string $method, string $uri)
    {
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        // Удаляем query string
        $uri = strtok($uri, '?');

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (self::matchRoute($route['path'], $uri, $params)) {
                // Выполняем middleware
                $handler = $route['handler'];
                foreach ($route['middlewares'] as $middleware) {
                    $handler = self::runMiddleware($middleware, $handler);
                }

                // Вызываем обработчик
                return self::runHandler($handler, $params);
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "404 - Page Not Found";
        return null;
    }

    private static function matchRoute(string $routePath, string $requestUri, &$params): bool
    {
        $routePattern = preg_replace('/\{[a-zA-Z0-9_]+}/', '([a-zA-Z0-9_\-]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';

        if (preg_match($routePattern, $requestUri, $matches)) {
            array_shift($matches);
            $params = $matches;
            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private static function runHandler($handler, array $params)
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }

        if (is_string($handler)) {
            $parts = explode('@', $handler);
            $controllerClass = "\\controllers\\$parts[0]";
            $method = $parts[1];

            if (!class_exists($controllerClass)) {
                throw new Exception("Controller $controllerClass not found");
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $method)) {
                throw new Exception("Method $method not found in $controllerClass");
            }

            return call_user_func_array([$controller, $method], $params);
        }

        throw new Exception("Invalid route handler");
    }

    private static function runMiddleware(string $middlewareClass, callable $next): Closure
    {
        $middleware = new $middlewareClass();
        return function(...$params) use ($middleware, $next) {
            return $middleware->handle(new Request(), new Response(), function() use ($next, $params) {
                return $next(...$params);
            });
        };
    }

    public static function getRoutes(): array
    {
        return self::$routes;
    }
}
