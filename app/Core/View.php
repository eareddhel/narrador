<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : View.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Core;

use App\Core\Exceptions\ViewNotFoundException;
use Throwable;

final class View
{
    private string $viewsPath;

    public function __construct(?string $viewsPath = null)
    {
        $configuredPath = $viewsPath ?? Config::get('constants.view_path');

        if (is_string($configuredPath) === false || trim($configuredPath) === '') {
            throw new ViewNotFoundException('No se pudo resolver el directorio de vistas configurado.');
        }

        $this->viewsPath = rtrim($configuredPath, "\\/");
    }

    public function render(string $view, array $data = [], ?string $layout = 'layout'): Response
    {
        $content = $this->renderFile($this->resolveViewPath($view), $data);

        if ($layout !== null) {
            $layoutData = $data;
            $layoutData['content'] = $content;

            $content = $this->renderFile($this->resolveViewPath($layout), $layoutData);
        }

        return (new Response())
            ->status(200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->body($content);
    }

    private function resolveViewPath(string $view): string
    {
        $this->validateViewName($view);

        $relativePath = str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
        $filePath = $this->viewsPath . DIRECTORY_SEPARATOR . $relativePath;

        if (is_file($filePath) === false) {
            throw new ViewNotFoundException(
                sprintf('La vista o layout "%s" no existe en el directorio de vistas.', $view)
            );
        }

        return $filePath;
    }

    private function validateViewName(string $view): void
    {
        if ($view === '') {
            throw new ViewNotFoundException('El nombre de la vista no puede estar vacío.');
        }

        if (str_contains($view, "\0")) {
            throw new ViewNotFoundException('El nombre de la vista contiene caracteres no permitidos.');
        }

        if (
            str_starts_with($view, '/')
            || str_starts_with($view, '\\')
            || preg_match('/^[A-Za-z]:/', $view) === 1
        ) {
            throw new ViewNotFoundException('El nombre de la vista debe ser una ruta relativa.');
        }

        if (str_contains($view, '\\') || str_contains($view, '..')) {
            throw new ViewNotFoundException('El nombre de la vista no puede salir del directorio de vistas.');
        }

        if (pathinfo($view, PATHINFO_EXTENSION) !== '') {
            throw new ViewNotFoundException('El nombre de la vista no debe incluir extensión.');
        }
    }

    private function renderFile(string $filePath, array $data): string
    {
        $bufferLevel = ob_get_level();

        try {
            ob_start();

            extract($data, EXTR_SKIP);

            require $filePath;

            $content = ob_get_clean();

            if ($content === false) {
                return '';
            }

            return $content;
        } catch (Throwable $exception) {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }

            throw $exception;
        }
    }
}