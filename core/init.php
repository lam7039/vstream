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

$log = new log(true);
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

function output(mixed $param) : void {
    //TODO: see if this can be unified with the file logger
    echo file_get_contents('./public/templates/debug.html');

    if (!is_array($param) || (is_array($param) && !array_key_exists('is_trace', $param))) {
        echo '<pre>' . var_export($param, true) . '</pre>';
        return;
    }

    unset($param['is_trace']);

    $defaults = [
        'code' => 1,
        'message' => 'n/a',
        'file' => 'n/a',
        'line' => 'n/a'
    ];

    $classes = [
        1 => 'info',
        2 => 'warning',
        3 => 'critical'
    ];

    $table = '';
    $timestamp = date('d/m/Y H:i:s', time());
    
    foreach ($param as $trace) {
        [
            'code' => $code,
            'message' => $message,
            'file' => $file,
            'line' => $line
        ] = array_merge($defaults, $trace);

        $table .= '<tr class="' . $classes[$code] . '">
            <td>' . $timestamp . '</td>
            <td>' . $message . '</td>
            <td>' . $file . '</td>
            <td>' . $line . '</td>
        </tr>';
    }

    echo $table . '</table>';
}

function dump(...$params) : void {
    array_map(fn(mixed $param) => output(is_string($param) ? htmlspecialchars($param) : $param), $params);
}

function dd(...$params) : never {
    dump(...$params);
    exit;
}

function redirect(string $to) : never {
    header('Location: ' . $to);
    exit;
}

set_exception_handler(function(\Throwable $error) {
    global $log;
    [$code, $message, $file, $line] = [$error->getCode(), $error->getMessage(), $error->getFile(), $error->getLine()];
    match($code) {
        1 => $log->append($message, error_type::Info, $file, $line),
        2 => $log->append($message, error_type::Warning, $file, $line),
        3 => $log->append($message, error_type::Critical, $file, $line),
        default => $log->append($message, error_type::Warning, $file, $line)
    };

    $trace = $error->getTrace();
    array_unshift($trace, [
        'code' => $code,
        'message' => $message,
        'file' => $file,
        'line' => $line,
    ]);

    do {
        dump(array_merge(['is_trace' => true], $trace));
    } while ($error = $error->getPrevious());
    exit;
});
