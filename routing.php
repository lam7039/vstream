<?php

use source\database;
use source\router;

$route = new router;
$database = new database;

$pages = ['browse', 'register', 'login', 'account'];
foreach ($pages as $page) {
    $route->bind($page, "public/html/$page.html");
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$verification = $_POST['verification'] ?? '';

$route->bind('do_register', '\controllers\authentication@register', [$username, $password, $verification], [$database]);
$route->bind('do_login', '\controllers\authentication@login', [$username, $password], [$database]);
$route->bind('do_logout', '\controllers\authentication@logout', [], [$database]);
$route->bind('do_transcode', '\controllers\transcode@run', [], [$database]);