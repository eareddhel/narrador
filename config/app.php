<?php

use App\Core\Env;

return [
    'name' => Env::getString('APP_NAME'),
    'env' => Env::getString('APP_ENV'),
    'debug' => Env::getBool('APP_DEBUG'),
    'url' => Env::getString('APP_URL'),
    'timezone' => Env::getString('TIMEZONE'),
];
