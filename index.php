<?php
set_include_path(__DIR__);

require('core/init.php');
require('routing.php');

use library\file_buffer;
use library\template;
use models\user;

use function library\session_exists;
use function library\session_get;
use function library\session_clear_temp;

$url_page = $_GET['request'] ?? 'browse';
$file_path = $route->get($url_page);

if (is_file($file_path)) {
    $user = null;
    if (session_exists(env('SESSION_AUTH'))) {
        $user = new user($database);
        $user = $user->access_user_data(session_get(env('SESSION_AUTH')), ['username', 'ip_address']);
    }

    $parameters = [
        'login' => [
            'test' => 'login'
        ],
        'browse' => [
            'error' => session_get('incorrect_login') ?? ''
        ],
        'account' => [
            'username' => $user ? $user->username . ' (' . long2ip($user->ip_address) . ')' : ''
        ]
    ];
    
    $templating = new template([
        'page_path' => 'public',
        'page_title' => "vstream | $url_page",
        'page_favicon' => 'favicon-32x32.png',
        'page_style' => 'layout.css',
        'page_script' => 'script.js'
    ]);

    $file_buffer = $templating->bind_parameters(new file_buffer($file_path), $parameters[$url_page] ?? []);
    echo $templating->render($file_buffer);
    session_clear_temp();
}