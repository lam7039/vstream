<?php
$start = microtime(true);
set_include_path(__DIR__);

require('core/init.php');
require('routing.php');

use source\file_buffer;
use source\template;
use models\user;

use function source\auth_check;
use function source\csrf_create;
use function source\session_get;
use function source\session_clear_temp;

$user = null;
if (auth_check()) {
    $user = new user;
    $user = $user->find(['id' => session_get(env('SESSION_AUTH'))]);
}

$templating = new template([
    'page_title' => "vstream | $url_page",
    'page_favicon' => 'favicon-32x32.png',
    'page_style' => 'layout.css',
    'page_script' => 'script.js',
    'username' => $user ? $user->username : '',
]);

$parameters = match ($url_page) {
    'login' => [
        'error' => session_get('error') ?? '',
        'token' => csrf_create(),
    ],
    'register' => [
        'error' => session_get('error') ?? '',
        'token' => csrf_create(),
    ],
    'account' => [
        'ip' => $user ? long2ip($user->ip_address) : '',
        'testfor' => [
            'first',
            'second',
            'third',
        ],
    ],
    default => [],
};

//TODO: use observer pattern for caching pages, subscribe pages to events that change the page

$templating->bind_parameters($parameters);
echo $templating->render(new file_buffer($response));

session_clear_temp();
echo microtime(true) - $start;
