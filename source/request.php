<?php

namespace source;

class request {
    private array $parameters = [];
    public string $current_page;

    public function __construct($default_page = 'browse') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) {
                http_response_code(500);
                exit;
            }
            $this->parameters = &$_POST;
        }
        
        $parameters = explode('/', $_GET['request'] ?? '');
        $this->current_page = array_shift($parameters) ?: $default_page;
        $this->parameters = array_merge($parameters, $this->parameters);
    }

    public function __get(string $key) : mixed {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }
        trigger_error("Undefined property via __get(): $key");
        return null;
    }
}