<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : test-view.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Exceptions\ViewNotFoundException;
use App\Core\Response;
use App\Core\View;

$tempPath = ROOT_PATH . '/storage/temp/view-test-' . uniqid('', true);

$failures = [];

function assertTrue(bool $condition, string $message): void
{
    global $failures;

    if ($condition === false) {
        $failures[] = $message;
    }
}

function writeTestFile(string $path, string $content): void
{
    $directory = dirname($path);

    if (is_dir($directory) === false) {
        mkdir($directory, 0777, true);
    }

    file_put_contents($path, $content);
}

function removeDirectory(string $path): void
{
    if (is_dir($path) === false) {
        return;
    }

    $items = scandir($path);

    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $itemPath = $path . DIRECTORY_SEPARATOR . $item;

        if (is_dir($itemPath)) {
            removeDirectory($itemPath);

            continue;
        }

        unlink($itemPath);
    }

    rmdir($path);
}

try {
    mkdir($tempPath, 0777, true);

    writeTestFile(
        $tempPath . '/hello.php',
        '<p>Hello <?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?></p>'
    );

    writeTestFile(
        $tempPath . '/layout.php',
        '<html><head><title><?= htmlspecialchars($title, ENT_QUOTES, "UTF-8") ?></title></head><body><?= $content ?></body></html>'
    );

    writeTestFile(
        $tempPath . '/broken.php',
        '<?php throw new RuntimeException("Template failure");'
    );

    $view = new View($tempPath);

    assertTrue(class_exists(View::class), 'Autoloader no resolvió App\Core\View.');
    assertTrue(
        class_exists(ViewNotFoundException::class),
        'Autoloader no resolvió App\Core\Exceptions\ViewNotFoundException.'
    );

    $response = $view->render('hello', ['name' => 'Narrador'], null);

    assertTrue($response instanceof Response, 'View no devolvió una instancia de Response.');
    assertTrue($response->getStatus() === 200, 'La respuesta sin layout no tiene estado 200.');
    assertTrue(
        ($response->getHeaders()['Content-Type'] ?? null) === 'text/html; charset=UTF-8',
        'La respuesta sin layout no tiene Content-Type HTML UTF-8.'
    );
    assertTrue($response->getBody() === '<p>Hello Narrador</p>', 'El cuerpo sin layout no coincide.');

    $responseWithLayout = $view->render(
        'hello',
        [
            'name' => 'Studio',
            'title' => 'Layout title',
            'content' => 'Original content must not win',
        ]
    );

    assertTrue($responseWithLayout instanceof Response, 'View con layout no devolvió Response.');
    assertTrue($responseWithLayout->getStatus() === 200, 'La respuesta con layout no tiene estado 200.');
    assertTrue(
        ($responseWithLayout->getHeaders()['Content-Type'] ?? null) === 'text/html; charset=UTF-8',
        'La respuesta con layout no tiene Content-Type HTML UTF-8.'
    );
    assertTrue(
        $responseWithLayout->getBody()
            === '<html><head><title>Layout title</title></head><body><p>Hello Studio</p></body></html>',
        'El cuerpo con layout no coincide o $content no tuvo prioridad.'
    );

    try {
        $view->render('missing', [], null);
        assertTrue(false, 'La vista inexistente no lanzó ViewNotFoundException.');
    } catch (ViewNotFoundException) {
        assertTrue(true, 'La vista inexistente lanzó ViewNotFoundException.');
    }

    try {
        $view->render('hello', ['name' => 'Layout'], 'missing-layout');
        assertTrue(false, 'El layout inexistente no lanzó ViewNotFoundException.');
    } catch (ViewNotFoundException) {
        assertTrue(true, 'El layout inexistente lanzó ViewNotFoundException.');
    }

    try {
        $view->render('../secret', [], null);
        assertTrue(false, 'El intento de path traversal no fue rechazado.');
    } catch (ViewNotFoundException) {
        assertTrue(true, 'El intento de path traversal lanzó ViewNotFoundException.');
    }

    $bufferLevel = ob_get_level();

    try {
        $view->render('broken', [], null);
        assertTrue(false, 'La excepción de plantilla no fue relanzada.');
    } catch (RuntimeException) {
        assertTrue(
            ob_get_level() === $bufferLevel,
            'View dejó buffers abiertos después de un error de plantilla.'
        );
    }
} finally {
    removeDirectory($tempPath);
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, '[FAIL] ' . $failure . PHP_EOL);
    }

    exit(1);
}

echo 'View tests passed.' . PHP_EOL;