<?php

use App\Core\Env;

return [
    'host' => Env::getString('DB_HOST'),
    'port' => Env::getInt('DB_PORT'),
    'database' => Env::getString('DB_DATABASE'),
    'username' => Env::getString('DB_USERNAME'),
    'password' => Env::getString('DB_PASSWORD'),
];
