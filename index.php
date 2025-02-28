<?php

use source\Container;
use source\Framework;

const DEBUG = true;

if (DEBUG) {
    $start = microtime(true);
    set_include_path(__DIR__);
}

require('core/init.php');

$container = new Container([
    source\Request::class => source\Request::class,
    source\Template::class => source\Template::class
]);

require('routing.php');

new Framework(
    $container,
    $request,
    $router
)->run();

source\session_clear_temp();

if (DEBUG) {
    //TODO: make toolbar for displaying this type of stuff
    echo microtime(true) - $start;
}
