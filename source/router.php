<?php

namespace source;

class RouteBuffer {
    public string $identifier = '';

    public function __construct(public RequestMethod $method, string $requestUri, public mixed $destination) {
        [$this->identifier] = explode('?', $requestUri);
    }
}

class Router {
    private array $routes = [];

    public function __construct(public Request $request) {
        if ($this->request->uri() === '/') {
            redirect(env('HOMEPAGE'));
        }
    }

    private function store_buffer(RequestMethod $method, string $identifier, string|array|callable $destination) : void {
        $buffer = new RouteBuffer($method, $identifier, $destination);
        $this->routes[$method->value][$identifier] = $buffer;
    }

    public function get(string $identifier, string|array|callable $destination) : void {
        $this->store_buffer(RequestMethod::Get, $identifier, $destination);
    }

    public function post(string $identifier, string|array|callable $destination) : void {
        $this->store_buffer(RequestMethod::Post, $identifier, $destination);
    }

    //TODO: resolve variables in routes by detecting {(?)varname}
    public function resolve(Container $container) : string|controller|null {
        $action = $this->routes[$this->request->method()->value][$this->request->uri()] ?? null;

        if (!$action) {
            throw new RouteNotFoundException($this->request->uri());
        }

        if (is_string($action->destination)) {
            return $action->destination;
        }

        if (is_callable($action->destination)) {
            return ($action->destination)();
        }

        if (is_array($action->destination)) {
            [$class, $method] = $action->destination + [null, 'index'];
            return $container->get($class)->$method();
        }

        throw new RouteNotFoundException($this->request->uri());
    }
}
