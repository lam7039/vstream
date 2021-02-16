<?php

use source\request;
use source\router;

$request = new request('browse');
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
$file_path = $router->get($url_page, $request->parameters);

if (!is_file($file_path)) {
    redirect('/');
}