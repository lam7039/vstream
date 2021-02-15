<?php

namespace source;

use controllers\controller;

// $initiated_classes = [];

// class route_buffer {
//     public controller $class;
//     public string $method;
//     public string $path;

//     public function __construct(string $destination, array $parameters = []) {
//         if (!str_contains($destination, '@')) {
//             $this->path = $destination;
//             return;
//         }

//         [$class, $this->method] = explode('@', $destination, 2);

//         //TODO: this will reuse yes, but always with the first set parameters
//         global $initiated_classes;
//         if (isset($initiated_classes[$class])) {
//             $this->class = $initiated_classes[$class];
//             return;
//         }
        
//         $initiated_classes[$class] = new $class(...$parameters);
//         $this->class = $initiated_classes[$class];
//     }
// }

class route_buffer {
    public string $class;
    public string $method;
    public string $path;

    public function __construct(string $destination) {
        if (!str_contains($destination, '@')) {
            $this->path = $destination;
            return;
        }

        [$this->class, $this->method] = explode('@', $destination, 2);
    }
}

class router {
    private array $routes = [];
    private array $initiated_classes = [];

    public function bind(string $page, string $destination, array $parameters = []) : void {
        $buffer = new route_buffer($destination);
        $this->routes[$page] = $buffer;
        
        if (!isset($this->initiated_classes[$buffer->class])) {
            $this->initiated_classes[$buffer->class] = new $class(...$parameters);
        }
    }

    public function get(string $page, array $parameters = []) : ?string {
        if (!isset($this->routes[$page])) {
            http_response_code(404);
            return null;
        }

        $route = $this->routes[$page];
        return isset($route->path) ? $route->path : $this->initiated_classes[$route?->class?]->{$route->method}(...$parameters);
    }
}
