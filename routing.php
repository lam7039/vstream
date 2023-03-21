<?php

use controllers\authentication;
use controllers\transcode;
use source\container;
use source\request;
use source\router;

$container = new container;
$request = new request;
$router = new router($request, $container);

$pages = ['browse', 'register', 'login', 'account'];
foreach ($pages as $page) {
    $router->get($page, "./public/html/$page.html");
}

$url_page = $request->page();

$router->get('test', function() {
    return 'this is a test';
});

$router->post('do_register', [authentication::class, 'register'], ['url_page' => $url_page]);
$router->post('do_login', [authentication::class, 'login'], ['url_page' => $url_page]);
$router->post('do_logout', [authentication::class, 'logout'], ['url_page' => $url_page]);
$router->post('do_transcode', [transcode::class, 'run'], ['url_page' => $url_page]);
