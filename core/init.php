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

function CONFIG(string $string) {
    return library\config::get($string);
}
function LOG_INFO(string $string) : void {
    library\log::info($string);
}
function LOG_WARNING(string $string) : void {
    library\log::warning($string);
}
function LOG_CRITICAL(string $string) : void {
    library\log::critical($string);
}

date_default_timezone_set(CONFIG('timezone'));

function dump($x) : void {
    var_dump($x);
}

function dd() : void {
    array_map(function($x) { 
        dump($x); 
    }, func_get_args());
    die;
}