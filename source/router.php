<?php

namespace source;

class RouteBuffer {
    public string $identifier = '';

    // public function __construct(private(set) RequestMethod $method, string $requestUri, private(set) mixed $destination) {
    public function __construct(public RequestMethod $method, string $requestUri, public mixed $destination) {
        [$this->identifier] = explode('?', $requestUri);
    }
}

class Router {
    private array $routes = [];

    public function __construct(private Request $request, private Container $container) {
        if ($this->request->uri() === '/') {
            redirect(env('HOMEPAGE'));
        }
    }

    private function store_buffer(RequestMethod $method, string $identifier, string|array|callable $destination) : void {
        $buffer = new RouteBuffer($method, $identifier, $destination);
        $this->routes[$method->value][$identifier] = $buffer;

        if (is_string($destination) || is_callable($destination)) {
            return;
        }

        [$class] = $destination + [null];
        if (!$this->container->has($class)) {
            $this->container->bind($class, $class);
        }
    }

    public function get(string $identifier, string|array|callable $destination) : void {
        $this->store_buffer(RequestMethod::Get, $identifier, $destination);
    }

    public function post(string $identifier, string|array|callable $destination) : void {
        $this->store_buffer(RequestMethod::Post, $identifier, $destination);
    }

    //TODO: resolve variables in routes by detecting {(?)varname}
    public function resolve() : string|controller|array|null {
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
            return match($this->request->method()) {
                RequestMethod::Get => $this->fetch_controller_get($class, $method, $parameters),
                RequestMethod::Post => $this->fetch_controller_post($class, $method)
            };
        }

        throw new RouteNotFoundException($this->request->uri());
    }

    private function fetch_controller_get(string $class, string $method, array $parameters) : string|controller {
        return $this->container->get($class)->$method(parameters: $parameters);
    }

    private function fetch_controller_post(string $class, string $method) : string|controller {
        return $this->container->get($class)->$method(...$this->request->only($this->container->getMethodParams($class, $method)));
    }
}
