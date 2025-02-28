<?php

namespace source;

enum ErrorType : string {
    case Info = 'info';
    case Warning = 'warning';
    case Critical = 'critical';
};

class log {
    private string $template_path = './public/templates/debug.html';
    private string $debug_file = './debug.html';

    public function __construct(private bool $reset = false) {}

    private function create_debug_file() : void {
        $template_contents = file_get_contents($this->template_path);
        file_put_contents($this->debug_file, $template_contents);
    }

    private function generate_debug(string $string, ErrorType $type, string $file, int $line) : string {
        //TODO: include full stack trace as collapsible item in debug file
        $stacktrace = debug_backtrace()[2];
        $timestamp = date('Y-m-d H:i:s', time());
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

    public function append(string $string, ErrorType $type, string $file = '', int $line = 0) : void {
        if (is_file($this->debug_file) && $this->reset) {
            unlink($this->debug_file);
        }

        if (!is_file($this->debug_file) || $this->reset) {
            $this->create_debug_file();
        }

        $content = $this->generate_debug($string, $type, $file, $line);
        file_put_contents($this->debug_file, $content, FILE_APPEND | LOCK_EX);
    }
}
