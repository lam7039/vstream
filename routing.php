<?php

use source\request;
use source\router;

$request = new request;
$router = new router;

$pages = ['browse', 'register', 'login', 'account'];
foreach ($pages as $page) {
    $router->bind($page, "public/html/$page.html");
}

$router->bind('do_register', '\controllers\authentication@register', [$request]);
$router->bind('do_login', '\controllers\authentication@login', [$request]);
$router->bind('do_logout', '\controllers\authentication@logout', [$request]);
$router->bind('do_transcode', '\controllers\transcode@run');

$url_page = $request->current_page;
$file_path = $router->get($url_page);

if (!is_file($file_path)) {
    redirect('/');
}