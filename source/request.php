<?php

namespace source;

class request {
    private array $params = [];
    public string $page = '';

    public function __construct(/*array $keys,*/ string $default_page = 'browse') {
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->params = explode('/', $_GET['request'] ?? '');
                $this->page = array_shift($this->params) ?: $default_page;
                // $this->params = array_intersect_key($this->params, $keys);
            break;
            case 'POST':
                if (!csrf_check()) {
                    http_response_code(500);
                    exit;
                }
                foreach ($_POST as $key => $value) {
                    if (!($input = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS))) {
                        LOG_WARNING("$key failed on filter with value: $value");
                    }
                    $this->params[$key] = $input;
                }
            break;
        }
    }

    public function __get(string $key) : mixed {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        trigger_error("Undefined property via __get(): $key");
        return null;
    }
}