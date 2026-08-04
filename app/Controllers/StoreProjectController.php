<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : StoreProjectController.php
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
use InvalidArgumentException;

final class StoreProjectController
{
    public function __construct(
        private readonly View $view,
        private readonly ProjectService $projects
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $name = (string) $request->post('name', '');
        $description = $request->post('description');
        $description = is_string($description) ? $description : null;

        try {
            $project = $this->projects->create($name, $description);
        } catch (InvalidArgumentException $exception) {
            return $this->validationResponse($name, $description, $exception->getMessage());
        }

        return (new Response())->redirect('/projects/' . rawurlencode($project['uuid']));
    }

    private function validationResponse(string $name, ?string $description, string $message): Response
    {
        $appName = Config::get('app.name', 'Narrador Studio');
        $appVersion = Config::get('app.version');

        return $this->view->render('projects/new', [
            'title' => 'Crear proyecto',
            'appName' => is_string($appName) && $appName !== '' ? $appName : 'Narrador Studio',
            'appVersion' => is_string($appVersion) && $appVersion !== '' ? $appVersion : null,
            'name' => $name,
            'description' => $description ?? '',
            'errors' => [$message],
            'navigationContext' => 'Inicio / Nuevo proyecto',
        ])->status(422);
    }
}
