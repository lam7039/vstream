<?php

use source\router;

$router = new router;

$pages = ['browse', 'register', 'login', 'account'];
foreach ($pages as $page) {
    $router->bind($page, "./public/html/$page.html");
}

$router->bind('do_register', '\controllers\authentication@register');
$router->bind('do_login', '\controllers\authentication@login');
$router->bind('do_logout', '\controllers\authentication@logout');
$router->bind('do_transcode', '\controllers\transcode@run');
