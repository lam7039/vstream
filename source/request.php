<?php

namespace source;

class request {
    public array $parameters = [];
    public string $current_page;

    public function __construct($default_page = '') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) {
                http_response_code(500);
                exit;
            }
            $this->parameters = &$_POST;
            unset($this->parameters['token']);
        }
        
        $parameters = explode('/', $_GET['request'] ?? '');
        $this->current_page = array_shift($parameters) ?: $default_page ?: env('HOMEPAGE');
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