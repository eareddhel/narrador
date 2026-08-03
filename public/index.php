<?php

declare(strict_types=1);

use App\Controllers\DashboardController;
use App\Core\Router;
use App\Core\View;

$bootstrap = require dirname(__DIR__) . '/bootstrap.php';

$view = $bootstrap['view'] ?? new View();

$router = new Router(static function (string $class) use ($view): object {
    if ($class === DashboardController::class) {
        return new DashboardController($view);
    }

    throw new InvalidArgumentException(sprintf('No controller resolver registered for "%s".', $class));
});

$routes = require dirname(__DIR__) . '/config/routes.php';

foreach ($routes as $route) {
    $method = strtoupper((string) ($route['method'] ?? ''));
    $path = (string) ($route['path'] ?? '');
    $handler = $route['handler'] ?? null;

    if ($method === 'GET' && (is_callable($handler) || is_string($handler))) {
        $router->get($path, $handler);

        continue;
    }

    if ($method === 'POST' && (is_callable($handler) || is_string($handler))) {
        $router->post($path, $handler);

        continue;
    }

    throw new InvalidArgumentException(sprintf('Invalid route definition for path "%s".', $path));
}

$router->run();
