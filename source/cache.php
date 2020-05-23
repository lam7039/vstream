<?php

namespace library;

class file_buffer {
    public string $path;
    public string $body;
    public int $size;

    public function __construct(string $path, string $body, int $size) {
        $this->path = $path;
        $this->body = $body;
        $this->size = $size;
    }
}

class file_cache {
    private array $files = [];
    private int $count = 0;

    public function cache_file(string $key, file_buffer $buffer) : void {
        $this->files[$key] = $buffer;
        $this->count++;
    }

    public function file_count() : int {
        return $this->count;
    }

    public function get_cached_file($key) : file_buffer {
        if (!isset($this->files[$key])) {
            LOG_WARNING('Key does not exist in array');
            return null;
        }
        return $this->files[$key];
    }

    public function remove_cached_file($key) : void {
        if (!isset($this->files[$key])) {
            LOG_WARNING('Key does not exist in array');
        }
        unset($this->files[$key]);
    }

    public function clear_cache() : void {
        $this->files = [];
    }
}