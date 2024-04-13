<?php

use controllers\account;
use controllers\authentication;
use controllers\browse;
use source\Container;
use source\Request;
use source\Router;
use source\Template;

$container = new Container([
    Request::class => Request::class,
    Router::class => Router::class,
    Template::class => Template::class,

    //Controllers
    browse::class => browse::class,
    account::class => account::class,
    authentication::class => authentication::class
]);

$request = $container->get(Request::class);
$router = $container->get(Router::class);

$router->get('/browse', [browse::class, 'index']);
$router->get('/account', [account::class, 'index']);
$router->get('/login', [authentication::class, 'index']);
$router->get('/register', [authentication::class, 'index']);

// $router->post('do_register', [authentication::class, 'register']);
// $router->post('do_login', [authentication::class, 'login']);
// $router->post('do_logout', [authentication::class, 'logout']);
