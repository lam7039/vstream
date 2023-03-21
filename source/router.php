<?php

namespace source;

class route_buffer {
    public bool $is_page = false;
    public string $path;
    public string $class;
    public string $method;
    public array $parameters;

    public function __construct(string|array|callable $destination, array $parameters = []) {
        if (is_string($destination) && !str_contains($destination, '@')) {
            $this->path = $destination;
            $this->is_page = true;
            return;
        }

        if (is_callable($destination)) {
            $this->method = $destination();
        }

        [$this->class, $this->method] = is_array($destination) ? $destination : explode('@', $destination, 2);
        $this->parameters = $parameters;
    }
}

class router {
    private array $routes = [];
    private array $initiated_classes = [];

    public function __construct(private request $request, private container $container) {}

    private function store_buffer(string $page, string|array|callable $destination, array $parameters = []) : void {
        if (is_callable($destination)) {
            $this->routes[$page] = $destination();
            return;
        }

        $buffer = new route_buffer($destination, $parameters);
        $this->routes[$page] = $buffer;
        
        if (!$buffer->is_page && !isset($this->initiated_classes[$buffer->class])) {
            // $this->initiated_classes[$buffer->class] = new $buffer->class(...$parameters);
            $this->container->set($buffer->class);
        }
    }

    public function get(string $page, string|array|callable $destination, array $parameters = []) : void {
        $this->store_buffer($page, $destination, $parameters);
    }

    public function post(string $page, string|array|callable $destination, array $parameters = []) : void {
        $this->store_buffer($page, $destination, $parameters);
    }

    public function response() : string|null {
        $page = $this->request->page();
        if (!isset($this->routes[$page])) {
            http_response_code(404);
            return null;
        }

        $route = $this->routes[$page];

        if ($route->is_page) {
            return $route->path;
        }

        // if (!empty($route->class)) {
        //     $class = $this->initiated_classes[$route->class];

        //     $reflected_method = new ReflectionMethod($route->class, $route->method);
        //     $reflected_parameters = $reflected_method->getParameters();

        //     $parameters = [];
        //     foreach ($reflected_parameters as $reflected_parameter) {
        //         $parameters[] = $reflected_parameter->name;
        //     }

        //     return $class->{$route->method}(...$this->request->only($parameters));
        // }

        if (!empty($route->class)) {
            $class = $this->container->get($route->class, $route->parameters);
            return $class->{$route->method}(...$this->request->only($this->container->getMethodParams($route->class, $route->method)));
        }

        if (!empty($route->method)) {
            return $route->method();
        }

        return null;
    }

    public function resolve() : string|null {
        //TODO: move resolving the route in response to this separate function
        return null;
    }
}
