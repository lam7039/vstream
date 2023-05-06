<?php

namespace source;

enum error_type : string {
    case Log = 'info';
    case Warning = 'warning';
    case Critical = 'critical';
};

class log {
    private $template_path = 'public/templates/debug.html';
    private $debug_file = 'debug.html';

    private function create_debug_file() : void {
        $template_contents = file_get_contents($this->template_path);
        file_put_contents($this->debug_file, $template_contents);
    }

    private function generate_debug(string $string, error_type $type, string $file, int $line) : string {
        $stacktrace = debug_backtrace()[2];
        $timestamp = date('d/m/Y H:i:s', time());
        if (!$file) {
            $file = explode('\\', $stacktrace['file']);
            $file = array_pop($file);
        }
        if (!$line) {
            $line = $stacktrace['line'];
        }
        $string = htmlspecialchars($string);
        return "<tr class='$type->value'>
                    <td>$timestamp</td>
                    <td>$string</td>
                    <td>$file</td>
                    <td>$line</td>
                </tr>";
    }

    public function append(string $string, error_type $type, string $file = '', int $line = 0) : void {
        if (!is_file($this->debug_file)) {
            $this->create_debug_file();
        }

        $content = $this->generate_debug($string, $type, $file, $line);
        file_put_contents($this->debug_file, $content, FILE_APPEND | LOCK_EX);
    }
}
