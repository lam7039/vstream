<?php

use source\Container;
use source\Framework;

$start = microtime(true);
set_include_path(__DIR__);

require('core/init.php');

$container = new Container([
    source\Request::class => source\Request::class,
    source\Router::class => source\Router::class,
    source\Template::class => source\Template::class,

    //Controllers (TODO: register controllers automatically)
    controllers\browse::class => controllers\browse::class,
    controllers\account::class => controllers\account::class,
    controllers\authentication::class => controllers\authentication::class
]);

require('routing.php');

(new Framework(
    $container,
    $request,
    $router
))->run();

source\session_clear_temp();
echo microtime(true) - $start;
