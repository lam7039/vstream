<?php

use source\Container;
use source\Request;
use source\Router;
use source\Template;

$container = new Container([
    Request::class => Request::class,
    Router::class => Router::class,
    Template::class => Template::class
]);

$controllers = ['browse', 'register', 'login', 'account'];
foreach ($controllers as $controller) {
    $container->bind("controllers\\$controller", "controllers\\$controller");
}

$request = $container->get(Request::class);
$router = $container->get(Router::class);

$pages = ['browse', 'register', 'login', 'account'];
foreach ($pages as $page) {
    $router->get("/$page", ["controllers\\$page", 'index']);
    // $router->get("/$page", "./public/html/$page.html");
}

// $router->post('do_register', [authentication::class, 'register']);
// $router->post('do_login', [authentication::class, 'login']);
// $router->post('do_logout', [authentication::class, 'logout']);
