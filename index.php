<?php
$start = microtime(true);
set_include_path(__DIR__);

require('core/init.php');
require('routing.php');

use controllers\browse;
use controllers\account;
use controllers\authentication;

use function source\session_clear_temp;
use function source\session_once;

$response = $router->response();

if (isset($response['error'])) {
    session_once('error', $response['error']);
}

if (!$response || (is_string($response) && !is_file($response)) || isset($response['path'])) {
    // redirect($response['path'] ?? env('HOMEPAGE'));
}

$output = match($url_page) {
    'login' => new authentication($url_page),
    'register' => new authentication($url_page),
    'account' => new account($url_page),
    'browse' => new browse($url_page),
    default => new browse($url_page)
};

echo $output->index($response);

session_clear_temp();
echo microtime(true) - $start;
