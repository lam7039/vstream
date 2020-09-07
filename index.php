<?php
set_include_path(__DIR__);

require('core/init.php');
require('routing.php');

use source\file_buffer;
use source\template;
use models\user;

use function source\csrf_check;
use function source\csrf_create;
use function source\session_isset;
use function source\session_get;
use function source\session_clear_temp;

$url_page = $_GET['request'] ?? 'browse';
$file_path = $route->get($url_page);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_check()) {
    http_response_code(500);
    return;
}

if (is_file($file_path)) {
    $crsf_token = csrf_create();

    $user = null;
    if (session_isset(env('SESSION_AUTH'))) {
        $user = new user($database);
        $user = $user->find(['id' => session_get(env('SESSION_AUTH'))]);
    }

    $parameters = [
        'login' => [
            'error' => session_get('incorrect_login') ?? '',
            'token' => $crsf_token,
        ],
        'register' => [
            'error' => session_get('password_mismatch') ?? '',
            'token' => $crsf_token,
        ],
        'account' => [
            'username' => $user ? $user->username . ' (' . long2ip($user->ip_address) . ')' : '',
        ]
    ];
    
    $templating = new template([
        'page_path' => 'public',
        'page_title' => "vstream | $url_page",
        'page_favicon' => 'favicon-32x32.png',
        'page_style' => 'layout.css',
        'page_script' => 'script.js',
    ]);

    $file_buffer = $templating->bind_parameters(new file_buffer($file_path), $parameters[$url_page] ?? []);
    echo $templating->render($file_buffer);
    session_clear_temp();
}