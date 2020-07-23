<?php

namespace library;

class log {
    private $template_path = 'public/templates/debug.html';
    private $debug_file = 'debug.html';

    private function create_debug_file() : void {
        $template_contents = file_get_contents($this->template_path);
        file_put_contents($this->debug_file, $template_contents);
    }

    private function generate_debug(string $string = '', string $error_type) : string {
        $stacktrace = debug_backtrace()[2];
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

    public function append(string $string, string $error_type) : void {
        if (!is_file($this->debug_file)) {
            $this->create_debug_file();
        }

        $content = $this->generate_debug($string, $error_type);
        file_put_contents($this->debug_file, $content, FILE_APPEND | LOCK_EX);
    }
}