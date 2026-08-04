<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : test-projects.php
| Autor   : Roberto + ChatGPT
| Version : 0.1.0
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Core\Exceptions\DatabaseException;
use App\Models\Project;
use App\Services\ProjectService;

$failures = [];
$checks = 0;

function assertTrue(bool $condition, string $message): void
{
    global $failures, $checks;

    $checks++;

    if ($condition === false) {
        $failures[] = $message;
    }
}

function assertThrows(string $expectedClass, callable $callback, string $message): void
{
    try {
        $callback();
        assertTrue(false, $message);
    } catch (Throwable $exception) {
        assertTrue($exception instanceof $expectedClass, $message);
    }
}

function assertNoInternalId(mixed $value, string $message): void
{
    if (is_array($value) === false) {
        assertTrue(true, $message);

        return;
    }

    foreach ($value as $key => $item) {
        if ($key === 'id') {
            assertTrue(false, $message);

            return;
        }

        if (is_array($item)) {
            assertNoInternalId($item, $message);
        }
    }

    assertTrue(true, $message);
}

function isUuidV4(string $uuid): bool
{
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) === 1;
}

function isTimestamp(string $timestamp): bool
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $timestamp);

    return $date instanceof DateTimeImmutable && $date->format('Y-m-d H:i:s') === $timestamp;
}

if (extension_loaded('pdo_sqlite') === false) {
    fwrite(STDERR, 'Missing development dependency: pdo_sqlite extension is not available.' . PHP_EOL);
    exit(1);
}

try {
    assertTrue(class_exists(Project::class), 'Autoloader no resolvio App\Models\Project.');
    assertTrue(class_exists(ProjectService::class), 'Autoloader no resolvio App\Services\ProjectService.');

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
    $database->statement('CREATE INDEX idx_projects_status ON projects (status)');
    $database->statement('CREATE INDEX idx_projects_created_at ON projects (created_at)');
    assertTrue(true, 'No se pudo crear el esquema temporal de projects.');

    $model = new Project($database);
    $service = new ProjectService($model);

    $firstProject = $service->create('  Tutorial Base  ', '   ');
    assertTrue(is_array($firstProject), 'create() no devolvio un proyecto.');
    assertTrue(isUuidV4($firstProject['uuid']), 'create() no genero un UUID v4 valido.');
    assertTrue($firstProject['status'] === 'draft', 'El estado inicial no es draft.');
    assertTrue($firstProject['name'] === 'Tutorial Base', 'El nombre no fue normalizado.');
    assertTrue($firstProject['description'] === null, 'La descripcion vacia no fue convertida en null.');
    assertTrue(isTimestamp($firstProject['created_at']), 'created_at no tiene formato Y-m-d H:i:s.');
    assertTrue(isTimestamp($firstProject['updated_at']), 'updated_at no tiene formato Y-m-d H:i:s.');
    assertTrue($firstProject['archived_at'] === null, 'archived_at debe iniciar como null.');
    assertNoInternalId($firstProject, 'create() expuso el id interno.');

    $foundProject = $service->get($firstProject['uuid']);
    assertTrue($foundProject !== null, 'get() no encontro un UUID existente.');
    assertTrue($foundProject['uuid'] === $firstProject['uuid'], 'get() devolvio un proyecto inesperado.');
    assertNoInternalId($foundProject, 'get() expuso el id interno.');

    assertTrue($service->get('00000000-0000-4000-8000-000000000000') === null, 'UUID inexistente no devolvio null.');

    $secondProject = $service->create('Guion con comillas', "O'Brien prepara el tutorial");
    assertTrue($secondProject['uuid'] !== $firstProject['uuid'], 'Los UUID generados no son unicos.');
    assertTrue(isUuidV4($secondProject['uuid']), 'El segundo UUID no es v4 valido.');

    $activeProjects = $service->listActive();
    assertTrue(count($activeProjects) === 2, 'listActive() no devolvio proyectos activos.');
    assertTrue($activeProjects[0]['uuid'] === $secondProject['uuid'], 'listActive() no ordena los proyectos recientes primero.');
    assertNoInternalId($activeProjects, 'listActive() expuso el id interno.');

    assertTrue(
        $service->update($firstProject['uuid'], '  Tutorial Actualizado  ', '  Nueva descripcion  ') === true,
        'update() no informo una actualizacion exitosa.'
    );

    $updatedProject = $service->get($firstProject['uuid']);
    assertTrue($updatedProject['name'] === 'Tutorial Actualizado', 'update() no actualizo el nombre.');
    assertTrue($updatedProject['description'] === 'Nueva descripcion', 'update() no actualizo la descripcion.');
    assertTrue($updatedProject['uuid'] === $firstProject['uuid'], 'update() modifico el UUID publico.');

    assertThrows(
        InvalidArgumentException::class,
        fn() => $service->create('   '),
        'create() no rechazo un nombre vacio.'
    );
    assertThrows(
        InvalidArgumentException::class,
        fn() => $service->create(str_repeat('A', 151)),
        'create() no rechazo un nombre demasiado largo.'
    );
    assertThrows(
        InvalidArgumentException::class,
        fn() => $service->update('   ', 'Nombre valido'),
        'update() no rechazo UUID vacio.'
    );
    assertThrows(
        InvalidArgumentException::class,
        fn() => $service->archive('missing-project'),
        'archive() no rechazo un UUID inexistente.'
    );

    $attemptedUuid = '11111111-1111-4111-8111-111111111111';
    $model->updateByUuid(
        $firstProject['uuid'],
        [
            'uuid' => $attemptedUuid,
            'name' => 'UUID Intacto',
            'updated_at' => '2026-01-01 00:00:00',
        ]
    );
    assertTrue($service->get($attemptedUuid) === null, 'El Model permitio modificar UUID mediante updateByUuid().');
    assertTrue($service->get($firstProject['uuid'])['name'] === 'UUID Intacto', 'updateByUuid() no aplico cambios permitidos.');

    assertTrue($service->exists($firstProject['uuid']) === true, 'exists() no detecto un proyecto existente.');
    assertTrue($service->exists('22222222-2222-4222-8222-222222222222') === false, 'exists() detecto un UUID inexistente.');

    assertTrue($service->archive($firstProject['uuid']) === true, 'archive() no informo exito.');
    $archivedProject = $service->get($firstProject['uuid']);
    assertTrue($archivedProject['status'] === 'archived', 'archive() no cambio el estado a archived.');
    assertTrue($archivedProject['archived_at'] !== null, 'archive() no establecio archived_at.');
    assertTrue(isTimestamp($archivedProject['archived_at']), 'archived_at no tiene formato Y-m-d H:i:s.');
    assertTrue($service->exists($firstProject['uuid']) === true, 'exists() no conserva proyectos archivados.');

    $activeAfterArchive = $service->listActive();
    assertTrue(count($activeAfterArchive) === 1, 'El proyecto archivado aparece en listActive().');
    assertTrue($activeAfterArchive[0]['uuid'] === $secondProject['uuid'], 'listActive() devolvio un proyecto archivado.');

    $physicalRow = $database->selectOne(
        'SELECT id, uuid, status FROM projects WHERE uuid = :uuid',
        ['uuid' => $firstProject['uuid']]
    );
    assertTrue($physicalRow !== null, 'El archivo elimino fisicamente el proyecto.');
    assertTrue(array_key_exists('id', $physicalRow), 'La tabla no conserva el id interno fisico.');

    $quotedProject = $service->get($secondProject['uuid']);
    assertTrue($quotedProject['description'] === "O'Brien prepara el tutorial", 'Las consultas preparadas no conservaron una comilla simple.');

    $projectSource = file_get_contents(ROOT_PATH . '/app/Models/Project.php') ?: '';
    $serviceSource = file_get_contents(ROOT_PATH . '/app/Services/ProjectService.php') ?: '';

    assertTrue(str_contains($projectSource, ':uuid'), 'El Model no evidencia uso de parametros preparados nombrados.');
    assertTrue(str_contains($projectSource, '->prepare') === false, 'El Model prepara SQL directamente en vez de usar Database.');
    assertTrue(str_contains($projectSource, '$_') === false, 'El Model accede a superglobales.');
    assertTrue(str_contains($serviceSource, 'App\Core\Database') === false, 'El Service depende directamente de Database.');
    assertTrue(str_contains($serviceSource, 'new Database') === false, 'El Service instancia Database directamente.');
    assertTrue(
        preg_match('/\b(SELECT|INSERT|DELETE|FROM|WHERE)\b/i', $serviceSource) !== 1,
        'El Service contiene SQL.'
    );

    $serviceReflection = new ReflectionClass(ProjectService::class);
    $constructor = $serviceReflection->getConstructor();
    $constructorParameters = $constructor?->getParameters() ?? [];
    $projectParameterType = $constructorParameters[0]?->getType();
    assertTrue(
        $projectParameterType instanceof ReflectionNamedType && $projectParameterType->getName() === Project::class,
        'ProjectService no depende exclusivamente de Project en el constructor.'
    );

    $modelReflection = new ReflectionClass(Project::class);
    $modelConstructor = $modelReflection->getConstructor();
    $modelParameters = $modelConstructor?->getParameters() ?? [];
    $databaseParameterType = $modelParameters[0]?->getType();
    assertTrue(
        $databaseParameterType instanceof ReflectionNamedType && $databaseParameterType->getName() === Database::class,
        'Project no recibe Database por constructor.'
    );
    assertTrue($modelReflection->isFinal(), 'Project debe ser final.');
    assertTrue($serviceReflection->isFinal(), 'ProjectService debe ser final.');
    assertTrue(class_exists('App\Repositories\ProjectRepository') === false, 'Se introdujo un Repository no autorizado.');

    try {
        $database->statement('INSERT INTO missing_table (name) VALUES (:name)', ['name' => 'Broken']);
        assertTrue(false, 'Un error PDO no fue transformado por Database.');
    } catch (DatabaseException $exception) {
        assertTrue($exception->getPrevious() instanceof PDOException, 'DatabaseException no conserva PDOException previa.');
    }

    assertNoInternalId($service->create('Proyecto sin ID'), 'Ninguna API publica debe exponer id interno.');
} catch (Throwable $exception) {
    $failures[] = sprintf(
        'Excepcion inesperada: %s: %s',
        $exception::class,
        $exception->getMessage()
    );
}

if ($failures !== []) {
    echo 'Project tests failed.' . PHP_EOL;

    foreach ($failures as $failure) {
        echo '[FAIL] ' . $failure . PHP_EOL;
    }

    exit(1);
}

echo 'Project tests passed.' . PHP_EOL;
echo 'Checks: ' . $checks . PHP_EOL;

