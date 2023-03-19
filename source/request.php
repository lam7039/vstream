<?php

namespace source;

enum request_type : string {
    case get = 'GET';
    case post = 'POST';
};

class request {
    private array $parameters = [];
    private string $page;

    public function __construct(public string $default_page = '') {
        if ($this->check_request_method(request_type::post)) {
            if (!csrf_check()) {
                http_response_code(500);
                exit;
            }
            $this->parameters = &$_POST;
            unset($this->parameters['token']);
        }

        $parameters = explode('/', $_GET['request'] ?? '');
        $this->page = array_pop($parameters) ?: $default_page ?: env('HOMEPAGE');
        $this->parameters = array_merge($parameters, $this->parameters);
    }

    public function page() : string {
        return $this->page;
    }

    private function check_request_method(request_type $type) : bool {
        return $_SERVER['REQUEST_METHOD'] === $type->value;
    }

    public function input(string $key, mixed $default = null) : mixed {
        if (!$this->has($key)) {
            return $default;
        }
        return $this->parameters[$key];
    }

    public function has(string $key) : bool {
        return isset($this->parameters[$key]);
    }

    //TODO: test this one
    public function except(array $keys) : array {
        return array_diff_key(array_flip($keys), $this->parameters);
    }
    
    //TODO: test this one
    public function only(array $keys) : array {
        return array_intersect_key($this->parameters, array_flip($keys));
    }

    public function all() : array {
        return $this->parameters;
    }
}
