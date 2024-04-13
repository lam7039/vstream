<?php

use source\Framework;

$start = microtime(true);
set_include_path(__DIR__);

require('core/init.php');
require('routing.php');

/*

use controllers\browse;
use controllers\account;
use controllers\authentication;

use function source\session_clear_temp;
use function source\session_once;

$response = $router->response();

if (isset($response['error'])) {
    session_once('error', $response['error']);
}

if (!$response || (is_string($response) && !is_file($response)) || isset($response['path'])) {
    redirect($response['path'] ?? env('HOMEPAGE'));
}

$output = match($identifier['url_page']) {
    'login' => new authentication($identifier),
    'register' => new authentication($identifier),
    'account' => new account($identifier),
    'browse' => new browse($identifier),
    default => new browse($identifier)
};

/*
TODO: rearrange the classes like this

$container = new container;
$router = new router($container);

$router->registerRoutesFromControllerAttributes([
    authentication::class
]);
*/

(new Framework(
    $container,
    $request,
    $router
))->run();

// session_clear_temp();
echo microtime(true) - $start;