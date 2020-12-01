<?php

namespace source;

class request {
    private array $params = [];
    public string $page = '';

    public function __construct(array $keys, string $default_page = 'browse') {
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->params = explode('/', $_GET['request'] ?? '');
                $this->page = array_shift($this->params) ?: $default_page;
                $this->params = array_intersect_key($this->params, $keys);
            break;
            case 'POST':
                if (!csrf_check()) {
                    http_response_code(500);
                    exit;
                }
                $this->params = &$_POST;
            break;
        }
    }

    public function __get(string $key) {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        trigger_error("Undefined property via __get(): $key");
        return null;
    }
}