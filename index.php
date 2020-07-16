<?php
set_include_path(__DIR__);

require('core/init.php');
require('routing.php');

use library\file_buffer;
use library\template;

use function library\session_exists;
use function library\session_get;
use function library\session_remove;

$url_page = $_GET['request'] ?? 'browse';
$file_path = $route->get($url_page);

if (is_file($file_path)) {

    $parameters = [
        'login' => [
            'test' => 'login'
        ],
    ];
    
    $templating = new template([
        'page_title' => "vstream | $url_page",
        'page_favicon' => 'favicon-32x32.png',
        'page_style' => 'layout.css',
        'page_script' => 'script.js'
    ]);

    $file_buffer = $templating->bind_parameters(new file_buffer($file_path), $parameters[$url_page] ?? []);
    echo $templating->render($file_buffer);
}

if (session_exists(CONFIG('SESSION_AUTH'))) {
    echo $database->fetch('select * from users where id = ' . session_get(CONFIG('SESSION_AUTH')))->username;
}

//TODO: fix session_once so this test works
if (session_exists('incorrect_login')) {
    echo session_get('incorrect_login');
}

if ($url_page === 'browse') {
    foreach ($temp_sessions as $temp_session) {
        session_remove($temp_session);
    }
}