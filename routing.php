<?php

use library\router;

$route = new router;

$pages = ['browse', 'register', 'login', 'account'];
foreach ($pages as $page) {
    $route->bind($page, "html/$page.html");
}

$route->bind('test', '\controllers\test_class->test');