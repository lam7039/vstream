<?php

use controllers\browse;
use controllers\account;
use controllers\authentication;

$router = $container->get(source\Router::class);

$router->get('/browse', [browse::class, 'index']);
$router->get('/account', [account::class, 'index']);
$router->get('/login', [authentication::class, 'index']);
$router->get('/register', [authentication::class, 'index']);

$router->post('/do_register', [authentication::class, 'register']);
$router->post('/do_login', [authentication::class, 'login']);
$router->post('/do_logout', [authentication::class, 'logout']);
