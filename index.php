<?php
set_include_path(__DIR__);

require('core/init.php');
require('routing.php');

use library\file_buffer;
use library\template;

$url_page = $_GET['request'] ?? 'browse';
$file_path = $route->get($url_page);

if (is_file($file_path)) {

    $file_buffer = new file_buffer($file_path);
    $templating = new template([
        'page_title' => "vstream | $url_page",
        'page_favicon' => 'favicon-32x32.png',
        'page_style' => 'layout.css',
        'page_script' => 'script.js'
    ]);

    switch($url_page) {
        case 'login':
            $file_buffer = $templating->set_parameter($file_buffer, 'test', 'login');
        break;
    }
    
    echo $templating->render($file_buffer);
}