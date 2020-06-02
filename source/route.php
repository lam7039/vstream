<?php

namespace library;

class route_buffer {
    public string $path;
    public string $file_key;
    public $class; //TODO: type hint class as controller
    public string $method;
    public array $params;

    public function __construct(string $path, string $destination, array $params = []) {
        $this->path = $path;
        if (strpos($destination, '->') !== false) {
            $class_method = explode('->', $destination);
            $this->class = new $class_method[0];
            $this->method = $class_method[1];
            $this->params = $params;
            return;
        }
        $this->file_key = $destination;
    }
}

class route {
    private array $routes = [];

    public function set(string $path, $destination, array $params = []) : void {
        $this->routes[$path] = new route_buffer($path, $destination, $params);
    }

    public function get(string $path) : ?string {
        if (!isset($this->routes[$path])) {
            http_response_code(404);
            return null;
        }

        $route = $this->routes[$path];
        if (isset($route->file_key)) {
            return $route->file_key;
        }
        if ($route->class && $route->method) {
            if (!$route->params) {
                if ($response = call_user_func([$route->class, $route->method])) {
                    return $response;
                }
            } else {
                if ($response = call_user_func([$route->class, $route->method], $route->params)) {
                    return $response;
                }
            }
        }

        return null;
    }
}