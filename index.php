<?php
set_include_path(__DIR__);

require('core/init.php');
require('routing.php');

use library\file_buffer;
use library\template;

$url_page = $_GET['request'] ?? 'browse';
$file_path = $route->get($url_page);

if (is_file($file_path)) {

    $parameters = [];
    switch($url_page) {
        case 'login':
            $parameters = ['test' => 'login'];
        break;
        case 'register':

        break;
        case 'account':

        break;
        case 'browse':

        break;
    }
    
    $templating = new template([
        'page_title' => "vstream | $url_page",
        'page_favicon' => 'favicon-32x32.png',
        'page_style' => 'layout.css',
        'page_script' => 'script.js'
    ]);

    $file_buffer = $templating->bind_parameters(new file_buffer($file_path), $parameters);
    echo $templating->render($file_buffer);
}