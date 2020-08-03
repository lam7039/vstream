<?php

namespace source;

use controllers\controller;

class route_buffer {
    public string $path;
    public string $file_key;
    public controller $class;
    public string $method;
    public array $params;

    public function __construct(string $path, string $destination, array $params = [], array $constructor_params = []) {
        $this->path = $path;
        if (strpos($destination, '->') !== false) {
            [$class, $method] = explode('->', $destination);
            if ($constructor_params) {
                $this->class = new $class(...array_values($constructor_params));
            } else {
                $this->class = new $class;
            }
            $this->method = $method;
            $this->params = $params;
            return;
        }
        $this->file_key = $destination;
    }
}

class router {
    private array $routes = [];

    public function bind(string $path, $destination, array $params = [], array $constructor_params = []) : void {
        $this->routes[$path] = new route_buffer($path, $destination, $params, $constructor_params);
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
                if ($response = call_user_func_array([$route->class, $route->method], $route->params)) {
                    return $response;
                }
            }
        }

        return null;
    }
}
