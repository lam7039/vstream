<?php

set_include_path(__DIR__);
require('core/init.php');

use library\file;
use library\file_cache;
use library\templating;

$file_cache = new file_cache;

$html_files = array_diff(scandir('html'), ['..', '.']);
foreach ($html_files as $html_file) {
    $file_cache->cache_file(basename($html_file), file::create_buffer('html/' . $html_file));
}

$templating = new templating($file_cache);
$templating->set_parameter('layout.html', 'page_title', 'Home');
$templating->set_parameter('layout.html', 'page_style', 'layout.css');
$templating->set_parameter('login.html', 'test', 'login');
echo $templating->render('login.html');