<?php

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    /** @var string $env */
    $env = $context['APP_ENV'];
    $debug = (bool) $context['APP_DEBUG'];

    return new Kernel($env, $debug);
};
