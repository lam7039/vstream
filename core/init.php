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

function dump(mixed $param) : void {
    echo '<style>
        body {
            padding: 10px;
            background-color: #202021;
            color: white;
        }
        th {
            text-align: left;
            border-bottom: 1px solid;
            padding: 5px;
        }
        td {
            padding: 5px;
        }
    </style>';
    
    if (!is_array($param)) {
        echo '<pre>' . var_export($param, true) . '</pre>';
        return;
    }

    $table = '<table>
        <tr>
            <th>Class</th>
            <th>Function</th>
            <th>Type</th>
            <th>File</th>
            <th>Line</th>
        </tr>';

    for ($i = count($param) - 1; $i >= 0; $i--) {
        [
            'class' => $class,
            'function' => $function,
            'type' => $type,
            'file' => $file,
            'line' => $line
        ] = $param[$i];

        $table .= "<tr>
            <td>$class</td>
            <td>$function</td>
            <td>$type</td>
            <td>$file</td>
            <td>$line</td>
        </tr>";
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

    do {
        output($message, $e->getTrace());
    } while ($e = $e->getPrevious());
    exit;
});
