<?php

namespace source;

class request {
    private array $params = [];

    public function __construct() {
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET': $this->params = &$_GET; break;
            case 'POST': $this->params = &$_POST; break;
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