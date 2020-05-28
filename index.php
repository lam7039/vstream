<?php

set_include_path(__DIR__);

require('core/init.php');
require('routing.php');

use library\file_buffer;
use library\file_cache;
use library\template;

use function library\session_exists;
use function library\session_get;
use function library\session_set;

if (!session_exists('file_cache')) {
    session_set('file_cache', new file_cache);
    $html_files = directory_files('html');
    foreach ($html_files as $html_file) {
        session_get('file_cache')->cache_file(basename($html_file), new file_buffer('html/' . $html_file));
    }
}

$templating = new template;
$layout_buffer = session_get('file_cache')->get_cached_file('layout.html');
$templating->set_parameter($layout_buffer, 'page_title', 'vstream');
$templating->set_parameter($layout_buffer, 'page_style', 'layout.css');

$url = isset($_GET['request']) ? $_GET['request'] : 'browse.html';
$file = $route->get($url);
$file_buffer = session_get('file_cache')->get_cached_file($file);

if ($file == 'login.html') {
    $templating->set_parameter($file_buffer, 'test', 'login');
}

echo $templating->render($file_buffer);