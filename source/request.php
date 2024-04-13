<?php

namespace source;

enum RequestMethod : string {
    case Get = 'GET';
    case Post = 'POST';
    case Put = 'PUT';
    case Head = 'HEAD';
};

class Request {
    private array $post = [];
    private array $query = [];

    public function __construct() {
        if ($this->method() === RequestMethod::Post) {
            if (!csrf_check()) {
                http_response_code(500);
                exit;
            }
            $this->post = &$_POST;
            unset($this->post['token']);
        }
        $this->query = &$_GET;
    }

    public function post(string $key) : mixed {
        return $this->post[$key] ?? null;
    }

    public function query(string $key) : mixed {
        return $this->query[$key] ?? null;
    }

    public function input(string $key) : mixed {
        return $this->post($key) ?? $this->query($key) ?? null;
    }

    public function all() : array {
        return array_merge($this->query, $this->post);
    }

    public function except(array $keys) : array {
        return array_diff_key($this->all(), array_flip($keys));
    }
    
    public function only(array $keys) : array {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function method() : RequestMethod {
        return match ($_SERVER['REQUEST_METHOD']) {
            'POST' => RequestMethod::Post,
            'GET' => RequestMethod::Get
        };
    }
    
    public function uri() : string {
        return $_SERVER['REQUEST_URI'];
    }

    public function csrf_create() : string {
        session_remove('token');
        if (!session_isset('token')) {
            $token = session_set('token', bin2hex(random_bytes(32)));
        }
        return session_get('token');
    }
    
    public function csrf_check(string $token) : bool {
        return hash_equals(session_get('token'), $token);
    }

    public function request(RequestMethod $method, string $url, array $data = []) : mixed {
        $handler = curl_init($url);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);

        $fields = http_build_query($data);
        switch ($method) {
            case RequestMethod::Post:
                curl_setopt($handler, CURLOPT_POST, true);
                curl_setopt($handler, CURLOPT_POSTFIELDS, $fields);
                break;
        }

        $response = curl_exec($handler);
        $error = curl_error($handler);

        if ($error) {
            LOG_CRITICAL($error);
        }
        
        return $response;
    }
}
