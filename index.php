<?php
set_include_path(__DIR__);

require('core/init.php');
require('routing.php');

use source\file_buffer;
use source\template;
use models\user;

use function source\session_isset;
use function source\session_get;
use function source\session_clear_temp;
use function source\session_set;
use function source\crsf_token;

$url_page = $_GET['request'] ?? 'browse';
$file_path = $route->get($url_page);

// if (!hash_equals(session_get('token'), $_POST['token'])) {
//     LOG_WARNING('CRSF Token mismatch');
//     return;
// }

// session_set('token', crsf_token());

if (is_file($file_path)) {
    $user = null;
    if (session_isset(env('SESSION_AUTH'))) {
        $user = new user($database);
        $user = $user->access_user_data(session_get(env('SESSION_AUTH')), ['username', 'ip_address']);
    }

    //TODO: XSS and CSRF protection

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