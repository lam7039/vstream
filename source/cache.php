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
        if ($this->file_exists($key)) {
            return $this->files[$key];
        }
        return null;
    }

    public function remove_cached_file($key) : void {
        if ($this->file_exists($key)) {
            unset($this->files[$key]);
        }
    }

    public function file_exists($key) : bool {
        if (!isset($this->files[$key])) {
            LOG_WARNING("The given key does not exist: $key");
            return false;
        }
        return true;
    }

    public function clear_cache() : void {
        $this->files = [];
    }
}