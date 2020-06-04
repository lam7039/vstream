<?php

use library\route;

$route = new route;

$route->set('browse', 'html/browse.html');
$route->set('register', 'html/register.html');
$route->set('login', 'html/login.html');
$route->set('account', 'html/account.html');

$route->set('test', '\controllers\controller->test');