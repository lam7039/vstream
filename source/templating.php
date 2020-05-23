<?php

namespace library;

use library\file_cache;

class templating {
    private $file_cache = null;
    private $file_buffer_template = null;

    public function __construct(file_cache &$file_cache, $default_template = 'templates/layout.html') {
        $this->file_cache = $file_cache;
        $this->file_cache->cache_file(basename($default_template), file::create_buffer($default_template));
        $this->file_buffer_template = $this->file_cache->get_cached_file(basename($default_template));
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
        $buffer_body = $this->file_cache->get_cached_file($buffer_key)->body;
        $buffer_body = str_replace('{{yield}}', $buffer_body, $this->file_buffer_template->body);
        return $buffer_body;
    }
}