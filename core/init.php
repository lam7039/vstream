<?php

use source\config;
use source\log;
use function source\session_isset;
use function source\session_set;

session_start();

function directory_files(string $path, array $except = []) : array {
    return array_filter(array_diff(scandir($path), ['..', '.', ...$except]), function ($item) use ($path) {
        return !is_dir("$path/$item");
    });
}

function require_files(string $directory, array $load_first = []) : void {
    $path = getcwd() . "/$directory";
    if ($load_first) {
        foreach ($load_first as $file) {
            require "$path/$file";
        }
    }
    $files = directory_files($path, $load_first);
    foreach ($files as $file) {
        require "$path/$file";
    }
}

require_files('source', ['buffers.php']);

if (!session_isset('SESSION_TEMP')) {
    session_set('SESSION_TEMP', []);
}

require_files('models', ['model.php']);
require_files('controllers', ['controller.php']);

$log = new log;
function LOG_INFO(string $string) : void {
    global $log;
    $log->append($string, 'info');
}
function LOG_WARNING(string $string) : void {
    global $log;
    $log->append($string, 'warning');
}
function LOG_CRITICAL(string $string) : void {
    global $log;
    $log->append($string, 'critical');
}

$config = new config;
function env(string $key) : string|null {
    global $config;
    return $config->get($key);
}

date_default_timezone_set(env('TIMEZONE'));

function dump($x) : void {
    echo '<style>
        body {
            padding: 10px;
            background-color: #202021;
            color: white;
        }
    </style>
    <pre>' . var_export($x, true) . '</pre>';
}

function dd() : void {
    array_map(function($x) { 
        dump($x); 
    }, func_get_args());
    exit;
}

function output() : void {
    array_map(function($x) { 
        dump($x); 
    }, func_get_args());
}

function redirect(string $to) : void {
    header('Location: ' . $to);
    exit;
}

function is_64bit() : int {
    return PHP_INT_SIZE === 8;
}