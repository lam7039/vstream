<?php
session_start();

function directory_files(string $directory) : array {
    return array_filter(array_diff(scandir($directory), ['..', '.']), function ($item) {
        return !is_dir($item);
    });
}

$source_files = directory_files('source');
foreach ($source_files as $source_file) {
    require "source/$source_file";
}

$controller_files = directory_files('controllers');
foreach ($controller_files as $controller_file) {
    require "controllers/$controller_file";
}

use library\config;
use library\log;

$config = new config;
function CONFIG(string $key) {
    global $config;
    return $config->get($key);
}

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

date_default_timezone_set(CONFIG('TIMEZONE'));

function dump($x) : void {
    var_dump($x);
}

function dd() : void {
    array_map(function($x) { 
        dump($x); 
    }, func_get_args());
    die;
}
