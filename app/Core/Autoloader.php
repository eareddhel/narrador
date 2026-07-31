<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : Autoloader.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Core;

final class Autoloader
{
    private string $baseDir;

    public function register(string $baseDir): void
    {
        $this->baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR);

        spl_autoload_register([$this, 'loadClass']);
    }

    private function loadClass(string $class): void
    {
        if (str_starts_with($class, 'App\\') === false) {
            return;
        }

        $relativePath = substr($class, 4);
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativePath);

        $filePath = $this->baseDir . DIRECTORY_SEPARATOR . $relativePath . '.php';

        if (is_file($filePath)) {
            require_once $filePath;
        }
    }
}
