<?php

namespace source;

use controllers\controller;

$initiated_classes = [];

class route_buffer {
    public controller $class;
    public string $method;
    public string $path;

    public function __construct(string $destination, array $parameters = []) {
        if (!str_contains($destination, '@')) {
            $this->path = $destination;
            return;
        }

        [$class, $this->method] = explode('@', $destination, 2);

        global $initiated_classes;
        if (isset($initiated_class[$class])) {
            $this->class = $class;
            return;
        }
        
        $initiated_classes[$class] = new $class(...$parameters);
        $this->class = $initiated_classes[$class];
    }
}

class router {
    private array $routes = [];

    public function bind(string $page, string $destination, array $parameters = []) : void {
        $this->routes[$page] = new route_buffer($destination, $parameters);
    }

    public function get(string $page) : ?string {
        if (!isset($this->routes[$page])) {
            http_response_code(404);
            return null;
        }

        $route = $this->routes[$page];
        return isset($route->path) ? $route->path : $route?->class?->{$route->method}();
    }
}
