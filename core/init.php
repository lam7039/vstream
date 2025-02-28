<?php
declare(strict_types=1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

use source\{config, error_type, log};
use function source\{session_isset, session_set};

session_start();

function directory_files(string $path, array $except = []) : array {
    return array_filter(array_diff(scandir($path), $except), fn ($item) => !is_dir("$path/$item"));
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
require_files('controllers');

$log = new log(false);
function LOG_INFO(string $string) : void {
    global $log;
    $log->append($string, error_type::Info);
}
function LOG_WARNING(string $string) : void {
    global $log;
    $log->append($string, error_type::Warning);
}
function LOG_CRITICAL(string $string) : void {
    global $log;
    $log->append($string, error_type::Critical);
}

$config = new config;
function env(string $key) : string|null {
    global $config;
    return $config->get($key);
}

date_default_timezone_set(env('TIMEZONE'));

function get_error_type(int $code) : error_type {
    return match($code) {
        1 => error_type::Info,
        2 => error_type::Warning,
        3 => error_type::Critical,
        default => error_type::Warning
    };
}

function output(mixed $param) : void {
    if (!$param instanceof \Throwable) {
        //TODO: use unified style with log but without table
        echo '<pre>' . var_export($param, true) . '</pre>';
        return;
    }

    //TODO: see if this can be unified with the file logger
    echo file_get_contents('./public/templates/debug.html');

    $defaults = [
        'message' => 'n/a',
        'file' => 'n/a',
        'line' => 'n/a'
    ];
    
    [$message, $file, $line] = array_merge($defaults, [$param->getMessage(), $param->getFile(), $param->getLine()]);

    $timestamp = date('Y-m-d H:i:s', time());
    $route = explode('/', $file);
    $file = array_pop($route);
    $error_type = get_error_type($param->getCode());

    //TODO: collapsible trace
    $table = '<tr class="' . $error_type->value . '">
        <td>' . $timestamp . '</td>
        <td>' . $message . '</td>
        <td>' . $file . '</td>
        <td>' . $line . '</td>
    </tr>';

    foreach ($param->getTrace() as $trace) {
        ['message' => $message, 'file' => $file, 'line' => $line, 'class' => $class, 'type' => $type, 'function' => $function] = array_merge($defaults, $trace);

        if ($message === 'n/a' && $class && $function) {
            $message = $class . $type . $function;
        }
        
        $route = explode('/', $file);
        $file = array_pop($route);

        $table .= '<tr class="' . $error_type->value . '">
            <td>' . $timestamp . '</td>
            <td>' . $message . '</td>
            <td>' . $file . '</td>
            <td>' . $line . '</td>
        </tr>';
    }

    echo $table . '</table>';
}

function dump(mixed ...$params) : void {
    array_map(fn(mixed $param) => output(is_string($param) ? htmlspecialchars($param) : $param), $params);
}

function dd(mixed ...$params) : never {
    dump(...$params);
    exit;
}

function redirect(string $to) : never {
    header('Location: ' . $to);
    exit;
}

set_exception_handler(function(\Throwable $exception) {
    global $log;
    [$message, $file, $line] = [$exception->getMessage(), $exception->getFile(), $exception->getLine()];
    $log->append($message, get_error_type($exception->getCode()), $file, $line);
    do {
        dump($exception);
    } while ($exception = $exception->getPrevious());
    exit;
});
