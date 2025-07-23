<?php

use Symfony\Component\Dotenv\Dotenv;

define('BASE_PATH', dirname(__DIR__) . '/../..');

require BASE_PATH . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require  BASE_PATH . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(BASE_PATH . '/.env');
}
