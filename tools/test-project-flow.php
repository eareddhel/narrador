<?php

declare(strict_types=1);

use App\Controllers\DashboardController;
use App\Controllers\NewProjectController;
use App\Controllers\ShowProjectController;
use App\Controllers\StoreProjectController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\View;
use App\Models\Project;
use App\Services\ProjectService;

$bootstrap = require dirname(__DIR__) . '/bootstrap.php';

$checks = 0;
$failures = [];
$originalGet = $_GET;
$originalPost = $_POST;
$originalFiles = $_FILES;
$originalCookie = $_COOKIE;
$originalServer = $_SERVER;

function checkProjectFlow(bool $condition, string $message): void
{
    global $checks, $failures;

    $checks++;

    if ($condition === false) {
        $failures[] = $message;
    }
}

function flowRequest(string $method, string $uri, array $post = []): Request
{
    $_GET = [];
    $_POST = $post;
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

function flowDatabase(): Database
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

    return $database;
}

function flowRouter(View $view, ProjectService $projectService): Router
{
    $router = new Router(static function (string $class) use ($view, $projectService): object {
        return match ($class) {
            DashboardController::class => new DashboardController($view, $projectService),
            NewProjectController::class => new NewProjectController($view),
            StoreProjectController::class => new StoreProjectController($view, $projectService),
            ShowProjectController::class => new ShowProjectController($view, $projectService),
            default => throw new InvalidArgumentException(sprintf('No controller resolver registered for "%s".', $class)),
        };
    });

    $router->get('/', DashboardController::class);
    $router->get('/projects/new', NewProjectController::class);
    $router->post('/projects', StoreProjectController::class);
    $router->get('/projects/{uuid}', ShowProjectController::class);

    return $router;
}

try {
    checkProjectFlow(class_exists(DashboardController::class), 'DashboardController no carga.');
    checkProjectFlow(class_exists(NewProjectController::class), 'NewProjectController no carga.');
    checkProjectFlow(class_exists(StoreProjectController::class), 'StoreProjectController no carga.');
    checkProjectFlow(class_exists(ShowProjectController::class), 'ShowProjectController no carga.');

    $view = $bootstrap['view'] ?? new View(dirname(__DIR__) . '/views');
    $database = flowDatabase();
    $projectService = new ProjectService(new Project($database));
    $router = flowRouter($view, $projectService);

    $response = $router->dispatch(flowRequest('GET', '/'));
    checkProjectFlow($response instanceof Response, 'GET / no devolvio Response.');
    checkProjectFlow($response->getStatus() === 200, 'GET / vacio no devuelve 200.');
    checkProjectFlow(str_contains($response->getBody(), 'Aún no tienes proyectos.'), 'Dashboard vacio no muestra estado vacio.');
    checkProjectFlow(str_contains($response->getBody(), 'Proyectos recientes') === false, 'Dashboard vacio muestra listado.');
    checkProjectFlow(str_contains($response->getBody(), '/projects/new'), 'Dashboard no enlaza a crear proyecto.');
    checkProjectFlow(
        str_contains($response->getBody(), 'href="/" aria-label="Volver al Dashboard de Narrador Studio"'),
        'La identidad no enlaza al Dashboard.'
    );
    checkProjectFlow(str_contains($response->getBody(), 'Inicio'), 'Dashboard no muestra contexto Inicio.');

    $response = $router->dispatch(flowRequest('GET', '/projects/new'));
    checkProjectFlow($response->getStatus() === 200, 'GET /projects/new no devuelve 200.');
    checkProjectFlow(str_contains($response->getBody(), '<form action="/projects" method="post"'), 'Formulario no usa POST tradicional.');
    checkProjectFlow(str_contains($response->getBody(), 'name="name"'), 'Formulario no solicita nombre.');
    checkProjectFlow(str_contains($response->getBody(), 'name="description"'), 'Formulario no solicita descripcion.');
    checkProjectFlow(str_contains($response->getBody(), 'Inicio / Nuevo proyecto'), 'Nuevo proyecto no muestra contexto correcto.');

    $response = $router->dispatch(flowRequest('POST', '/projects', ['name' => '   ', 'description' => 'Texto']));
    checkProjectFlow($response->getStatus() === 422, 'POST invalido no devuelve 422.');
    checkProjectFlow(str_contains($response->getBody(), 'El nombre del proyecto es obligatorio.'), 'POST invalido no muestra validacion.');
    checkProjectFlow(str_contains($response->getBody(), 'Inicio / Nuevo proyecto'), 'Validacion no conserva contexto correcto.');

    $response = $router->dispatch(flowRequest('POST', '/projects', [
        'name' => '  Primer Proyecto  ',
        'description' => '  Guion inicial  ',
    ]));
    $headers = $response->getHeaders();
    checkProjectFlow($response->getStatus() === 302, 'POST valido no redirige.');
    checkProjectFlow(isset($headers['Location']), 'Redirect no contiene Location.');
    checkProjectFlow(str_starts_with((string) $headers['Location'], '/projects/'), 'Redirect no apunta al proyecto creado.');

    $uuid = substr((string) $headers['Location'], strlen('/projects/'));
    $storedProject = $projectService->get($uuid);
    checkProjectFlow($storedProject !== null, 'Proyecto creado no fue persistido.');
    checkProjectFlow($storedProject['name'] === 'Primer Proyecto', 'Proyecto creado no normalizo nombre.');
    checkProjectFlow($storedProject['description'] === 'Guion inicial', 'Proyecto creado no normalizo descripcion.');
    checkProjectFlow($storedProject['status'] === 'draft', 'La base no conserva el estado interno draft.');

    $response = $router->dispatch(flowRequest('GET', '/projects/' . $uuid));
    checkProjectFlow($response->getStatus() === 200, 'GET /projects/{uuid} no devuelve 200.');
    checkProjectFlow(str_contains($response->getBody(), 'Primer Proyecto'), 'Vista de proyecto no muestra nombre.');
    checkProjectFlow(str_contains($response->getBody(), 'Guion inicial'), 'Vista de proyecto no muestra descripcion.');
    checkProjectFlow(str_contains($response->getBody(), 'Borrador'), 'Vista de proyecto no muestra estado traducido.');
    checkProjectFlow(str_contains($response->getBody(), 'Inicio / Primer Proyecto'), 'Vista de proyecto no muestra contexto con nombre.');
    checkProjectFlow(str_contains($response->getBody(), 'draft') === false, 'Vista de proyecto expone estado interno draft.');
    checkProjectFlow(str_contains($response->getBody(), 'Secciones'), 'Vista de proyecto no prepara Secciones.');
    checkProjectFlow(str_contains($response->getBody(), 'Narraciones'), 'Vista de proyecto no prepara Narraciones.');
    checkProjectFlow(str_contains($response->getBody(), 'Exportaciones'), 'Vista de proyecto no prepara Exportaciones.');

    $response = $router->dispatch(flowRequest('GET', '/projects/missing-uuid'));
    checkProjectFlow($response->getStatus() === 404, 'Proyecto inexistente no devuelve 404.');
    checkProjectFlow(str_contains($response->getBody(), 'No pudimos encontrar ese proyecto.'), 'Proyecto inexistente no muestra mensaje seguro.');
    checkProjectFlow(str_contains($response->getBody(), 'Inicio / Proyecto no encontrado'), 'Proyecto inexistente no muestra contexto correcto.');

    $response = $router->dispatch(flowRequest('GET', '/'));
    checkProjectFlow($response->getStatus() === 200, 'GET / con proyectos no devuelve 200.');
    checkProjectFlow(str_contains($response->getBody(), 'Proyectos recientes'), 'Dashboard con proyectos no muestra listado.');
    checkProjectFlow(str_contains($response->getBody(), 'Primer Proyecto'), 'Dashboard con proyectos no lista el proyecto creado.');
    checkProjectFlow(str_contains($response->getBody(), 'Borrador'), 'Dashboard con proyectos no muestra estado traducido.');
    checkProjectFlow(str_contains($response->getBody(), 'draft') === false, 'Dashboard con proyectos expone estado interno draft.');
    checkProjectFlow(str_contains($response->getBody(), 'Aún no tienes proyectos.') === false, 'Dashboard con proyectos muestra estado vacio.');

    $routes = require dirname(__DIR__) . '/config/routes.php';
    $routeKeys = array_map(
        static fn(array $route): string => strtoupper((string) $route['method']) . ' ' . (string) $route['path'],
        $routes
    );
    checkProjectFlow(in_array('GET /', $routeKeys, true), 'Falta ruta GET /.');
    checkProjectFlow(in_array('GET /projects/new', $routeKeys, true), 'Falta ruta GET /projects/new.');
    checkProjectFlow(in_array('POST /projects', $routeKeys, true), 'Falta ruta POST /projects.');
    checkProjectFlow(in_array('GET /projects/{uuid}', $routeKeys, true), 'Falta ruta GET /projects/{uuid}.');

    $controllerSources = '';
    foreach ([
        'DashboardController.php',
        'NewProjectController.php',
        'StoreProjectController.php',
        'ShowProjectController.php',
    ] as $file) {
        $controllerSources .= file_get_contents(dirname(__DIR__) . '/app/Controllers/' . $file) ?: '';
    }

    checkProjectFlow(str_contains($controllerSources, 'echo ') === false, 'Un controller hace echo.');
    checkProjectFlow(str_contains($controllerSources, 'exit') === false, 'Un controller hace exit.');
    checkProjectFlow(str_contains($controllerSources, 'header(') === false, 'Un controller llama header().');
    checkProjectFlow(str_contains($controllerSources, 'new Database') === false, 'Un controller instancia Database directamente.');
    checkProjectFlow(str_contains($controllerSources, '$_') === false, 'Un controller accede a superglobales.');
} catch (Throwable $exception) {
    $failures[] = sprintf('Excepcion inesperada: %s: %s', $exception::class, $exception->getMessage());
} finally {
    $_GET = $originalGet;
    $_POST = $originalPost;
    $_FILES = $originalFiles;
    $_COOKIE = $originalCookie;
    $_SERVER = $originalServer;
}

if ($failures !== []) {
    echo 'Project flow tests failed.' . PHP_EOL;

    foreach ($failures as $failure) {
        echo '[FAIL] ' . $failure . PHP_EOL;
    }

    exit(1);
}

echo 'Project flow tests passed.' . PHP_EOL;
echo 'Checks: ' . $checks . PHP_EOL;
