<?php

namespace library;

class template {
    private file_cache $file_cache;
    private ?file_buffer $file_buffer_template;

    public function __construct(file_cache &$file_cache, $default_template = 'templates/layout.html') {
        $this->file_cache = $file_cache;

        if (!$this->file_buffer_template = $this->file_cache->get_cached_file(basename($default_template))) {
            $this->file_buffer_template = new file_buffer($default_template);
            $this->file_cache->cache_file(basename($default_template), $this->file_buffer_template);
        }
    }

    public function set_parameter(string $buffer_key, string $key, string $value) : void {
        $buffer = $this->file_cache->get_cached_file($buffer_key);
        if (strpos($buffer->body, "{{{$key}}}") === false) {
            LOG_WARNING('No instance of parameter found in file: ' . $key);
            return;
        }
        
        $buffer->body = str_replace("{{{$key}}}", $value, $buffer->body);
        $this->file_cache->cache_file($buffer_key, $buffer);
    }

    public function render(string $buffer_key) : string {
        if ($buffer = $this->file_cache->get_cached_file($buffer_key)) {
            $buffer_body = str_replace('{{{yield}}}', $buffer->body, $this->file_buffer_template->body);
            return $buffer_body;
        }
        return 404;
    }
}