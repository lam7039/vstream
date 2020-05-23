<?php
session_start();

function dump($x) : void {
    var_dump($x);
}

function dd() : void {
    array_map(function($x) { 
        dump($x); 
    }, func_get_args());
    die;
}

require 'autoload.php';

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
