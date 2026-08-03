<?php

declare(strict_types=1);

use App\Controllers\DashboardController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\View;

$bootstrap = require dirname(__DIR__) . '/bootstrap.php';

$checks = 0;

function assertDashboard(bool $condition, string $message): void
{
    if ($condition) {
        return;
    }

    fwrite(STDERR, 'Dashboard test failed: ' . $message . PHP_EOL);
    exit(1);
}

function captureDashboardRequest(string $method, string $uri): Request
{
    $_GET = [];
    $_POST = [];
    $_FILES = [];
    $_COOKIE = [];
    $_SERVER = [
        'REQUEST_METHOD' => $method,
        'REQUEST_URI' => $uri,
        'HTTP_HOST' => 'narrador.pwa',
        'REMOTE_ADDR' => '127.0.0.1',
    ];

    return Request::capture();
}

function dashboardRouter(View $view): Router
{
    $router = new Router(static function (string $class) use ($view): object {
        if ($class === DashboardController::class) {
            return new DashboardController($view);
        }

        throw new InvalidArgumentException(sprintf('No controller resolver registered for "%s".', $class));
    });

    $router->get('/', DashboardController::class);

    return $router;
}

$checks++;
assertDashboard(class_exists(DashboardController::class), 'DashboardController should autoload.');

$view = $bootstrap['view'] ?? new View(dirname(__DIR__) . '/views');

$checks++;
assertDashboard($view instanceof View, 'View should be created.');

$router = dashboardRouter($view);

$checks++;
assertDashboard($router instanceof Router, 'Router should be created with explicit controller resolver.');

$request = captureDashboardRequest('GET', '/');

ob_start();
$response = $router->dispatch($request);
$output = ob_get_clean();

$checks++;
assertDashboard($output === '', 'dispatch() should not send output directly.');

$checks++;
assertDashboard($response instanceof Response, 'GET / should return a Response.');

$checks++;
assertDashboard($response->getStatus() === 200, 'GET / should return status 200.');

$headers = $response->getHeaders();

$checks++;
assertDashboard(
    ($headers['Content-Type'] ?? '') === 'text/html; charset=UTF-8',
    'GET / should return HTML UTF-8 content type.'
);

$body = $response->getBody();

$checks++;
assertDashboard(str_contains($body, 'Narrador Studio'), 'Body should contain Narrador Studio.');

$checks++;
assertDashboard(str_contains($body, 'Dashboard operativo'), 'Body should contain Dashboard operativo.');

$checks++;
assertDashboard(str_contains($body, 'Aún no tienes proyectos.'), 'Body should contain empty projects state.');

$controllerSource = file_get_contents(dirname(__DIR__) . '/app/Controllers/DashboardController.php');

$checks++;
assertDashboard(
    is_string($controllerSource) && str_contains($controllerSource, 'Database') === false,
    'DashboardController should not reference Database.'
);

$postResponse = $router->dispatch(captureDashboardRequest('POST', '/'));

$checks++;
assertDashboard($postResponse->getStatus() === 404, 'POST / should return 404.');

$missingResponse = $router->dispatch(captureDashboardRequest('GET', '/ruta-inexistente'));

$checks++;
assertDashboard($missingResponse->getStatus() === 404, 'Unknown route should return 404.');

$routes = require dirname(__DIR__) . '/config/routes.php';

$checks++;
assertDashboard(count($routes) === 1, 'Only one route should be declared.');

$checks++;
assertDashboard(($routes[0]['handler'] ?? null) === DashboardController::class, 'GET / should target DashboardController.');

echo sprintf('Dashboard tests passed. Checks: %d', $checks) . PHP_EOL;
