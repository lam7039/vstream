<?php

use library\database;
use library\router;

$route = new router;
$database = new database;

$pages = ['browse', 'register', 'login', 'account'];
foreach ($pages as $page) {
    $route->bind($page, "public/html/$page.html");
}

$route->bind('do_register', '\controllers\authentication->register', ['lamram', 'password', 'password'], [$database]);
$route->bind('do_login', '\controllers\authentication->login', ['lamram', 'password'], [$database]);
$route->bind('do_logout', '\controllers\authentication->logout', [], [$database]);
//$route->bind('find_access', '\controllers\authentication->find_access', [], [$database]);