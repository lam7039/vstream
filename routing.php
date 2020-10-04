<?php

use source\router;

$route = new router;

//TODO: handle redirects in javascript as a one page application
$pages = ['browse', 'register', 'login', 'account'];
foreach ($pages as $page) {
    $route->bind($page, "public/html/$page.html");
}

$route->bind('do_register', '\controllers\authentication@register');
$route->bind('do_login', '\controllers\authentication@login');
$route->bind('do_logout', '\controllers\authentication@logout');
$route->bind('do_transcode', '\controllers\transcode@run');

$url_page = $_GET['request'] ?? 'browse';
$file_path = $route->get($url_page);

if (!is_file($file_path)) {
    redirect('/');
}