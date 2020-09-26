<?php

namespace source;

class request {
    private $body;

    public function __construct() {
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET': $this->body = &$_GET; break;
            case 'POST': $this->body = &$_POST; break;
        }
    }

    public function __get(string $key) {
        if (isset($this->body[$key])) {
            return $this->body[$key];
        }
        trigger_error("Undefined property via __get(): $key");
        return null;
    }
}