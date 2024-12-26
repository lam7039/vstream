<?php

use controllers\browse;
use controllers\account;
use controllers\authentication;
use source\Router;

$request = $container->get(source\Request::class);
$router = new Router($request, $container);

$default_parameters = [
    'page_favicon' => 'favicon-32x32.png',
    'page_style' => 'layout.css',
    'page_script' => 'script.js',
    'page_title' => env('PROJECT_NAME') . ' | ' . ltrim($request->uri(), '/')
];

$router->get('/browse', [browse::class, 'index', $default_parameters]);
$router->get('/account', [account::class, 'index', $default_parameters]);
$router->get('/login', [authentication::class, 'index', $default_parameters]);
$router->get('/register', [authentication::class, 'index', $default_parameters]);

$router->post('/do_register', [authentication::class, 'register']);
$router->post('/do_login', [authentication::class, 'login']);
$router->get('/do_logout', [authentication::class, 'logout']);
