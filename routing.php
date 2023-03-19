<?php

use controllers\authentication;
use controllers\transcode;
use source\request;
use source\router;

$request = new request;
$router = new router($request);

$pages = ['browse', 'register', 'login', 'account'];
foreach ($pages as $page) {
    $router->get($page, "./public/html/$page.html");
}

$router->get('test', function() {
    return 'this is a test';
});

$router->post('do_register', [authentication::class, 'register']);
$router->post('do_login', [authentication::class, 'login']);
$router->post('do_logout', [authentication::class, 'logout']);
$router->post('do_transcode', [transcode::class, 'run']);
