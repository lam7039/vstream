<?php

namespace source;

use controllers\controller;

class route_buffer {
    public string $path;
    public controller $class;
    public string $method;

    public function __construct(string $destination, array $constructor_params = []) {
        // if (str_contains($destination, '@')) {
        if (strpos($destination, '@') === false) {
            $this->path = $destination;
            return;
        }

        [$class, $this->method] = explode('@', $destination, 2);
        $this->class = new $class(...$constructor_params);
    }
}

class router {
    private array $routes = [];

    public function bind(string $page, string $destination, array $constructor_params = []) : void {
        //TODO: find variable get parameters somehow
        // if (preg_match_all('/\{(.*?)\}/', $page, $matches) !== false) {
        //     foreach ($matches as $match) {

        //     }
        // }
        $this->routes[$page] = new route_buffer($destination, $constructor_params);
    }

    public function get(string $page) : ?string {
        if (!isset($this->routes[$page])) {
            http_response_code(404);
            return null;
        }

        $route = $this->routes[$page];
        if (isset($route->path)) {
            return $route->path;
        }
        
        if ($route->class && $route->method) {
            return $route->class->{$route->method}();
        }

        return null;

        //TODO: test this with PHP8
        // return $route?->class?->{$route->method}();
    }
}
