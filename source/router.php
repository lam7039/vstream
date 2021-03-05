<?php

namespace source;

class route_buffer {
    public bool $is_page = false;
    public string $path;
    public string $class;
    public string $method;

    public function __construct(string $destination) {
        if (!str_contains($destination, '@')) {
            $this->path = $destination;
            $this->is_page = true;
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
        
        if (!$buffer->is_page && !isset($this->initiated_classes[$buffer->class])) {
            $this->initiated_classes[$buffer->class] = new $buffer->class(...$parameters);
        }
    }

    public function get(string $page, array $parameters = []) : string|null {
        if (!isset($this->routes[$page])) {
            http_response_code(404);
            return null;
        }

        $route = $this->routes[$page];
        return $route->is_page ? $route->path : $this->initiated_classes[$route->class]?->{$route->method}(...$parameters);
    }
}
