<?php

namespace source;

class request {
    private $body;

    public function __construct() {
        if (isset($_POST)) {
            foreach($_POST as $key => $value) {
                $this->body[$key] = $value;
            }
        }
        if (isset($_GET)) {
            foreach($_GET as $key => $value) {
                $this->body[$key] = $value;
            }
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