<?php

namespace library;

class log {
    private static $template = 'templates/debug.html';
    private static $debug_file = 'debug.html';

    private function __construct() {}

    private static function create_debug_file() : void {
        $template_contents = file_get_contents(self::$template);
        file_put_contents(self::$debug_file, $template_contents);
    }

    private static function generate_debug(string $string = '', string $error_type) : string {
        $stacktrace = debug_backtrace()[3];
        $timestamp = date('d/m/Y H:i:s', time());
        $file = explode('\\', $stacktrace['file']);
        $file = array_pop($file);
        $line = $stacktrace['line'];
        $string = htmlspecialchars($string);
        return "<tr class='$error_type'>
                    <td>$timestamp</td>
                    <td>$string</td>
                    <td>$file</td>
                    <td>$line</td>
                </tr>";
    }

    private static function append(string $string, string $error_type) : void {
        if (!is_file(self::$debug_file)) {
            self::create_debug_file();
        }

        $content = self::generate_debug($string, $error_type);
        file_put_contents(self::$debug_file, $content, FILE_APPEND | LOCK_EX);
    }

    //TODO: maybe remove the functions below and use append in the global functions

    public static function info(string $string) : void {
        self::append($string, 'info');
    }

    public static function warning(string $string) : void {
        self::append($string, 'warning');
    }

    public static function critical(string $string) : void {
        self::append($string, 'critical');
    }
}