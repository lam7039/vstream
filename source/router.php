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
    public function resolve(Container $container) : string|controller|array|null {
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
            [$class, $method, $parameters] = $action->destination + [null, 'index', []];

            if (RequestMethod::Post === $this->request->method()) {
                return $container->get($class)->$method(...$this->request->only($container->getMethodParams($class, $method)));
            }
            
            return $container->get($class)->$method(parameters: $parameters);
        }

        throw new RouteNotFoundException($this->request->uri());
    }
}
