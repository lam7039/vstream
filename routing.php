<?php

use controllers\browse;
use controllers\account;
use controllers\authentication;

$router = $container->get(source\Router::class);

$default_parameters = [
    'page_favicon' => 'favicon-32x32.png',
    'page_style' => 'layout.css',
    'page_script' => 'script.js',
];

$router->get('/browse', [browse::class, 'index', $default_parameters]);
$router->get('/account', [account::class, 'index', $default_parameters]);
$router->get('/login', [authentication::class, 'index', $default_parameters]);
$router->get('/register', [authentication::class, 'index', $default_parameters]);

$router->post('/do_register', [authentication::class, 'register']);
$router->post('/do_login', [authentication::class, 'login']);
$router->post('/do_logout', [authentication::class, 'logout']);
