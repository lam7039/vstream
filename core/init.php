<?php
session_start();

function directory_files(string $directory, array $except = []) : array {
    return array_filter(array_diff(scandir($directory), ['..', '.', ...$except]), function ($item) use ($directory) {
        return !is_dir($directory . '/' . $item);
    });
}

$source_files = directory_files('source');
foreach ($source_files as $source_file) {
    require "source/$source_file";
}

require 'models/model.php';
$model_files = directory_files('models', ['model.php']);
foreach ($model_files as $model_file) {
    require "models/$model_file";
}

require 'controllers/controller.php';
$controller_files = directory_files('controllers', ['controller.php']);
foreach ($controller_files as $controller_file) {
    require "controllers/$controller_file";
}

use source\config;
use source\log;

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
function env(string $key) {
    global $config;
    return $config->get($key);
}

date_default_timezone_set(env('TIMEZONE'));

function dump($x) : void {
    var_dump($x);
}

function dd() : void {
    array_map(function($x) { 
        dump($x); 
    }, func_get_args());
    die;
}

function redirect(string $to) {
    header('Location: ' . $to);
    exit;
}