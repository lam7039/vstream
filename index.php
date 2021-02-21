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
    'page_path' => 'public',
    'page_title' => "vstream | $url_page",
    'page_favicon' => 'favicon-32x32.png',
    'page_style' => 'layout.css',
    'page_script' => 'script.js',
    'username' => $user ? $user->username : '',
]);

$parameters = match ($url_page) {
    'login' => [
        'error' => session_get('incorrect_login') ?? '',
        'token' => csrf_create(),
    ],
    'register' => [
        'error' => session_get('password_mismatch') ?? '',
        'token' => csrf_create(),
    ],
    'account' => [
        'ip' => long2ip($user->ip_address),
    ],
    default => [],
};

$file_buffer = $templating->bind_parameters($parameters);
echo $templating->render(new file_buffer($file_path));
session_clear_temp();
echo microtime(true) - $start;