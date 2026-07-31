<?php

declare(strict_types=1);

if (defined('ROOT_PATH') === false) {
    define('ROOT_PATH', __DIR__);
}

require_once ROOT_PATH . '/app/Core/Autoloader.php';

$autoloader = new App\Core\Autoloader();
$autoloader->register(ROOT_PATH . '/app');

App\Core\Env::load();
