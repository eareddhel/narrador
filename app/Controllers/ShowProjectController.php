<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : ShowProjectController.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Controllers;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\ProjectService;

final class ShowProjectController
{
    public function __construct(
        private readonly View $view,
        private readonly ProjectService $projects
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $uuid = (string) $request->route('uuid', '');
        $project = $this->projects->get($uuid);

        if ($project === null) {
            return $this->notFoundResponse();
        }

        $appName = Config::get('app.name', 'Narrador Studio');
        $appVersion = Config::get('app.version');

        return $this->view->render('projects/show', [
            'title' => $project['name'],
            'appName' => is_string($appName) && $appName !== '' ? $appName : 'Narrador Studio',
            'appVersion' => is_string($appVersion) && $appVersion !== '' ? $appVersion : null,
            'project' => $project,
            'navigationContext' => 'Inicio / ' . (string) $project['name'],
        ]);
    }

    private function notFoundResponse(): Response
    {
        $appName = Config::get('app.name', 'Narrador Studio');
        $appVersion = Config::get('app.version');

        return $this->view->render('projects/not-found', [
            'title' => 'Proyecto no encontrado',
            'appName' => is_string($appName) && $appName !== '' ? $appName : 'Narrador Studio',
            'appVersion' => is_string($appVersion) && $appVersion !== '' ? $appVersion : null,
            'navigationContext' => 'Inicio / Proyecto no encontrado',
        ])->status(404);
    }
}
