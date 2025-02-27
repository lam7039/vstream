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

function output(mixed $param) : void {
    //TODO: see if this can be unified with the file logger
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

    if (!is_array($param) || (is_array($param) && !array_key_exists('is_trace', $param))) {
        echo '<pre>' . var_export($param, true) . '</pre>';
        return;
    }

    unset($param['is_trace']);

    $defaults = [
        'message' => 'n/a',
        'class' => 'n/a',
        'function' => 'n/a',
        'file' => 'n/a',
        'line' => 'n/a'
    ];

    $table = '<table>
        <tr class="colhead">
            <td>Message</th>
            <td>Class</th>
            <td>Function</th>
            <td>File</th>
            <td>Line</th>
        </tr>';

    foreach ($param as $trace) {
        [
            'message' => $message,
            'class' => $class,
            'function' => $function,
            'file' => $file,
            'line' => $line
        ] = array_merge($defaults, $trace);

        $table .= '<tr>
            <td>' . $message . '</td>
            <td>' . $class . '</td>
            <td>' . $function . '</td>
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
    [$message, $file, $line] = [$error->getMessage(), $error->getFile(), $error->getLine()];
    match($error->getCode()) {
        0 => $log->append($message, error_type::Log, $file, $line),
        1 => $log->append($message, error_type::Warning, $file, $line),
        2 => $log->append($message, error_type::Critical, $file, $line),
        default => $log->append($message, error_type::Warning, $file, $line)
    };

    $trace = $error->getTrace();
    array_unshift($trace, [
        'message' => $message,
        'class' => get_class($error),
        'file' => $file,
        'line' => $line
    ]);

    do {
        dump(array_merge(['is_trace' => true], $trace));
    } while ($error = $error->getPrevious());
    exit;
});
