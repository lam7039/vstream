<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use source\{config, error_type, log};
use function source\{session_isset, session_set};

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

function dump(mixed $param) : void {
    //TODO: see if you can unify this and the file logger
    echo '<style>
        body {
            padding: 10px;
            background-color: #202021;
            color: white;
        }
        td {
            background: #282828;
            color: #A0A0A0;
            padding: 4px 8px 4px 8px;
            font-family: Monospace;
        }
        .colhead td {
            background: #0A0A0A;
        }
    </style>';
    
    if (!is_array($param)) {
        echo '<pre>' . var_export($param, true) . '</pre>';
        return;
    }

    $table = '<table>
        <tr class="colhead">
            <td>Class</th>
            <td>Function</th>
            <td>File</th>
            <td>Line</th>
        </tr>';

    foreach ($param as $array) {
        $table .= '<tr>
            <td>' . ($array['class'] ?? '') . '</td>
            <td>' . ($array['function'] ?? '') . '</td>
            <td>' . ($array['file'] ?? '') . '</td>
            <td>' . ($array['line'] ?? '') . '</td>
        </tr>';
    }

    echo $table . '</table>';
}

function output(...$params) : void {
    array_map(fn(mixed $param) => dump($param), $params);
}

function dd(...$params) : never {
    output(...$params);
    exit;
}

function redirect(string $to) : never {
    header('Location: ' . $to);
    exit;
}

set_exception_handler(function(\Throwable $error) {
    //TODO: refactor exception handling (currently comment out code gets no code and always outputs as info)
    // $message = $error->getMessage();
    // $code = $error->getCode();
    // match($code) {
    //     0 => LOG_INFO($message),
    //     1 => LOG_WARNING($message),
    //     2 => LOG_CRITICAL($message),
    //     default => LOG_WARNING($message)
    // };
    // if (!$code) {
    //     output($message);
    // } else {
    //     dd($error->getMessage());
    // }
    //TODO: append to file in exception maybe?
    global $log;
    do {
        $log->append($error->getMessage(), error_type::Warning, $error->getFile(), $error->getLine());
        output($error->getMessage(), $error->getTrace());
    } while ($error = $error->getPrevious());
    exit;
});
