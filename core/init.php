<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use source\config;
use source\error_type;
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
    $log->append($string, error_type::Log);
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

function dump(mixed $x) : void {
    echo '<style>
        body {
            padding: 10px;
            background-color: #202021;
            color: white;
        }
        th {
            text-align: left;
        }
        td {
            padding: 5px;
        }
    </style>';
    if (is_array($x) && !empty($x)) {
        echo '<table>
            <tr>
                <th>Class</th>
                <th>Function</th>
                <th>Type</th>
                <th>File</th>
                <th>Line</th>
            </tr>';
        for ($i = count($x) - 1; $i >= 0; $i--) {
            $stack = $x[$i];
            echo '<tr>
                    <td>' . $stack['class'] . '</td>
                    <td>' . $stack['function'] . '</td>
                    <td>' . $stack['type'] . '</td>
                    <td>' . $stack['file'] . '</td>
                    <td>' . $stack['line'] . '</td>
                </tr>';
        }
        echo '</table>';
        return;
    }
    echo '<pre>' . var_export($x, true) . '</pre>';
}

function output() : void {
    array_map(function(mixed $x) { 
        dump($x); 
    }, func_get_args());
}

function dd() : never {
    array_map(function(mixed $x) { 
        dump($x); 
    }, func_get_args());
    exit;
}

function redirect(string $to) : never {
    header('Location: ' . $to);
    exit;
}

function is_64bit() : int {
    return PHP_INT_SIZE === 8;
}

set_exception_handler(function(\Throwable $e) {
    //TODO: refactor exception handling (currently comment out code gets no code and always outputs as info)
    // $message = $e->getMessage();
    // $code = $e->getCode();
    // match($code) {
    //     0 => LOG_INFO($message),
    //     1 => LOG_WARNING($message),
    //     2 => LOG_CRITICAL($message)
    // };
    // if (!$code) {
    //     output($message);
    // } else {
    //     dd($e->getMessage());
    // }
    global $log;
    $message = $e->getMessage();
    $log->append($message, error_type::Warning, $e->getFile(), $e->getLine());
    dd($message, $e->getTrace());
});
