<?php

use App\Controllers\DashboardController;

return [
    [
        'method' => 'GET',
        'path' => '/',
        'handler' => DashboardController::class,
    ],
];
