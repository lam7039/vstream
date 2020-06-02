<?php

set_include_path(__DIR__);

require('core/init.php');
require('routing.php');

use library\file_buffer;
use library\file_cache;
use library\template;
use function library\session_exists;
use function library\session_get;
use function library\session_remove;
use function library\session_set;

session_remove('file_cache');

if (!session_exists('file_cache')) {
    session_set('file_cache', new file_cache);
    $html_files = directory_files('html');
    foreach ($html_files as $html_file) {
        session_get('file_cache')->cache_file(basename($html_file), new file_buffer('html/' . $html_file));
    }
}

$templating = new template;
$layout_buffer = session_get('file_cache')->get_cached_file('layout.html');

$url_page = $_GET['request'] ?? 'browse';
$templating->set_parameter($layout_buffer, 'page_title', 'vstream | ' . $url_page);
$templating->set_parameter($layout_buffer, 'page_favicon', 'favicon-32x32.png');
$templating->set_parameter($layout_buffer, 'page_style', 'layout.css');
$templating->set_parameter($layout_buffer, 'page_script', 'script.js');

$file = $route->get($url_page);
$file_buffer = session_get('file_cache')->get_cached_file($file);

switch($file) {
    case 'login.html':
        $templating->set_parameter($file_buffer, 'test', 'login');
    break;
}

if (is_file("html/$file")) {
    echo $templating->render($file_buffer);
}