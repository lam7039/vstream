<?php

namespace source;

use ArrayObject;
use InvalidArgumentException;

class RouteBuffer {
    public string $identifier = '';

    public function __construct(public RequestMethod $method, string $requestUri, public mixed $destination) {
        [$this->identifier] = explode('?', $requestUri);
    }
}

class RoutePool extends ArrayObject {
    public function offsetSet(mixed $key, mixed $value) : void
    {
        if (!($value instanceof RouteBuffer)) {
            throw new InvalidArgumentException('Value must be instance of RouteBuffer');
        }
        parent::offsetSet($key, $value);
    }
}

class Router {
    private array $routes = [];

    public function __construct() {
        $this->routes[RequestMethod::Post->value] = new RoutePool;
        $this->routes[RequestMethod::Get->value] = new RoutePool;
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
    public function resolve(RequestMethod $method, string $identifier) : string|null {
        $action = $this->routes[$method->value][$identifier] ?? null;

        if (!$action) {
            throw new RouteNotFoundException($identifier);
        }

        if (is_string($action->destination)) {
            return $action->destination;
        }

        if (is_callable($action->destination)) {
            return ($action->destination)();
        }

        if (is_array($action->destination)) {
            [$class, $method] = $action->destination;
    
            if (class_exists($class)) {
                $class = new $class();
    
                if (method_exists($class, $method)) {
                    return $class->$method();
                }
            }
        }

        throw new RouteNotFoundException($identifier);
    }
}
