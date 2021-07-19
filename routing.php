<?php

use source\request;
use source\router;

use function source\session_once;

$request = new request;
$router = new router;

$pages = ['browse', 'register', 'login', 'account'];
foreach ($pages as $page) {
    $router->bind($page, "public/html/$page.html");
}

$router->bind('do_register', '\controllers\authentication@register');
$router->bind('do_login', '\controllers\authentication@login');
$router->bind('do_logout', '\controllers\authentication@logout');
$router->bind('do_transcode', '\controllers\transcode@run');

$url_page = $request->current_page;
$response = $router->get($url_page, $request->parameters);

if (isset($response['error'])) {
    session_once('error', $response['error']);
}

if (!$response || (is_string($response) && !is_file($response)) || isset($response['path'])) {
    redirect($response['path'] ?? env('HOMEPAGE'));
}