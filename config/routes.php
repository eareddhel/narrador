<?php

use App\Controllers\DashboardController;
use App\Controllers\NewProjectController;
use App\Controllers\ShowProjectController;
use App\Controllers\StoreProjectController;

return [
    [
        'method' => 'GET',
        'path' => '/',
        'handler' => DashboardController::class,
    ],
    [
        'method' => 'GET',
        'path' => '/projects/new',
        'handler' => NewProjectController::class,
    ],
    [
        'method' => 'POST',
        'path' => '/projects',
        'handler' => StoreProjectController::class,
    ],
    [
        'method' => 'GET',
        'path' => '/projects/{uuid}',
        'handler' => ShowProjectController::class,
    ],
];
