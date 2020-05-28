<?php

set_include_path(__DIR__);
require('core/init.php');
require('routing.php');

use library\file_buffer;
use library\file_cache;
use library\template;

$file_cache = new file_cache;
$html_files = directory_files('html');
foreach ($html_files as $html_file) {
    $file_cache->cache_file(basename($html_file), new file_buffer('html/' . $html_file));
}

$templating = new template($file_cache);
$templating->set_parameter('layout.html', 'page_title', 'vstream');
$templating->set_parameter('layout.html', 'page_style', 'layout.css');
$templating->set_parameter('login.html', 'test', 'login');

$path = isset($_GET['request']) ? $_GET['request'] : 'browse';
if ($destination = $route->get($path)) {
    echo $templating->render($destination);
}