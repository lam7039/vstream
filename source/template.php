<?php

namespace library;

class template {
    private string $template_key;

    public function __construct(string $template_path = 'templates/layout.html') {
        $this->template_key = basename($template_path);
        session_get('file_cache')->cache_file($this->template_key, new file_buffer($template_path));
    }

    public function set_parameter(file_buffer $buffer, string $key, string $value) : void {
        if (strpos($buffer->body, "{{{$key}}}") === false) {
            return;
        }
        
        $buffer->body = str_replace("{{{$key}}}", $value, $buffer->body);
        session_get('file_cache')->cache_file($buffer->path, $buffer);
    }

    public function render(file_buffer $buffer) : string {
        return str_replace('{{{yield}}}', $buffer->body, session_get('file_cache')->get_cached_file($this->template_key)->body);
    }
}