<?php

use source\router;

$route = new router;

$pages = ['browse', 'register', 'login', 'account'];
foreach ($pages as $page) {
    $route->bind($page, "public/html/$page.html");
}

$route->bind('do_register', '\controllers\authentication@register');
$route->bind('do_login', '\controllers\authentication@login');
$route->bind('do_logout', '\controllers\authentication@logout');
$route->bind('do_transcode', '\controllers\transcode@run');