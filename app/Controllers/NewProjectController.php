<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : NewProjectController.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Controllers;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class NewProjectController
{
    public function __construct(
        private readonly View $view
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $appName = Config::get('app.name', 'Narrador Studio');
        $appVersion = Config::get('app.version');

        return $this->view->render('projects/new', [
            'title' => 'Crear proyecto',
            'appName' => is_string($appName) && $appName !== '' ? $appName : 'Narrador Studio',
            'appVersion' => is_string($appVersion) && $appVersion !== '' ? $appVersion : null,
            'name' => '',
            'description' => '',
            'errors' => [],
            'navigationContext' => 'Inicio / Nuevo proyecto',
        ]);
    }
}
