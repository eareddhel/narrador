<?php

declare(strict_types=1);

use App\Controllers\DashboardController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\View;
use App\Models\Project;
use App\Services\ProjectService;

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

function dashboardRouter(View $view, ProjectService $projectService): Router
{
    $router = new Router(static function (string $class) use ($view, $projectService): object {
        if ($class === DashboardController::class) {
            return new DashboardController($view, $projectService);
        }

        throw new InvalidArgumentException(sprintf('No controller resolver registered for "%s".', $class));
    });

    $router->get('/', DashboardController::class);

    return $router;
}

function dashboardService(): ProjectService
{
    $pdo = new PDO('sqlite::memory:');
    $database = Database::fromPdo($pdo);
    $database->statement(
        "CREATE TABLE projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid CHAR(36) NOT NULL UNIQUE,
            name VARCHAR(150) NOT NULL,
            description TEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            archived_at DATETIME NULL,
            CHECK (status IN ('draft', 'active', 'archived'))
        )"
    );

    return new ProjectService(new Project($database));
}

$checks++;
assertDashboard(class_exists(DashboardController::class), 'DashboardController should autoload.');

$view = $bootstrap['view'] ?? new View(dirname(__DIR__) . '/views');
$projectService = dashboardService();

$checks++;
assertDashboard($view instanceof View, 'View should be created.');

$router = dashboardRouter($view, $projectService);

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
assertDashboard(str_contains($body, '¡Hola! ¿Qué vamos a crear hoy?'), 'Body should contain product greeting.');

$checks++;
assertDashboard(
    str_contains($body, 'href="/" aria-label="Volver al Dashboard de Narrador Studio"'),
    'Brand identity should link to Dashboard with aria-label.'
);

$checks++;
assertDashboard(str_contains($body, 'Ubicación actual'), 'Layout should expose current navigation context label.');

$checks++;
assertDashboard(str_contains($body, 'Inicio'), 'Dashboard should show Inicio context.');

$checks++;
assertDashboard(str_contains($body, 'Aún no tienes proyectos.'), 'Body should contain empty projects state.');

$checks++;
assertDashboard(str_contains($body, 'Proyectos recientes') === false, 'Empty dashboard should not show project list.');

$projectService->create('Proyecto Dashboard', 'Visible en listado');
$response = $router->dispatch(captureDashboardRequest('GET', '/'));
$body = $response->getBody();

$checks++;
assertDashboard(str_contains($body, 'Proyecto Dashboard'), 'Dashboard should list existing projects.');

$checks++;
assertDashboard(str_contains($body, 'Borrador'), 'Dashboard should translate draft status for presentation.');

$checks++;
assertDashboard(str_contains($body, 'draft') === false, 'Dashboard should not expose raw draft status.');

$checks++;
assertDashboard(str_contains($body, 'Aún no tienes proyectos.') === false, 'Dashboard should not show empty state with projects.');

$controllerSource = file_get_contents(dirname(__DIR__) . '/app/Controllers/DashboardController.php');

$checks++;
assertDashboard(
    is_string($controllerSource) && str_contains($controllerSource, 'new Database') === false,
    'DashboardController should not instantiate Database.'
);

$postResponse = $router->dispatch(captureDashboardRequest('POST', '/'));

$checks++;
assertDashboard($postResponse->getStatus() === 404, 'POST / should return 404.');

$missingResponse = $router->dispatch(captureDashboardRequest('GET', '/ruta-inexistente'));

$checks++;
assertDashboard($missingResponse->getStatus() === 404, 'Unknown route should return 404.');

$routes = require dirname(__DIR__) . '/config/routes.php';

$checks++;
assertDashboard(count($routes) === 4, 'Four routes should be declared for M02.2A.');

$checks++;
assertDashboard(($routes[0]['handler'] ?? null) === DashboardController::class, 'GET / should target DashboardController.');

echo sprintf('Dashboard tests passed. Checks: %d', $checks) . PHP_EOL;
