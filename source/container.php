<?php

namespace source;

use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

class container {
    private array $instances = [];

    public function set(string $abstract, callable|string|null $concrete = null) : void {
        if (!$concrete) {
            $concrete = $abstract;
        }
        $this->instances[$abstract] = $concrete;
    }

    public function get(string $abstract, array $parameters = []) : object {
        if (!$this->has($abstract)) {
            throw new Exception("Could not find class $abstract");
        }
        return $this->resolve($this->instances[$abstract], $parameters);
    }

    public function has(string $abstract) : bool {
        return isset($this->instances[$abstract]);
    }

    private function reflectParams(string $class, string $method) : array {
        $reflected_method = new ReflectionMethod($class, $method);
        return $reflected_method->getParameters();
    }

    //TODO: refactor this method
    public function getMethodParams(string $class, string $method) : array {
        $reflected_parameters = $this->reflectParams($class, $method);
        $parameters = [];
        foreach ($reflected_parameters as $reflected_parameter) {
            $parameters[] = $reflected_parameter->name;
        }
        return $parameters;
    }

    private function resolve(mixed $abstract, array $parameters) : object {
        if (is_callable($abstract)) {
            return $abstract($this, $parameters);
        }

        $reflector = new ReflectionClass($abstract);
        if (!$reflector->isInstantiable()) {
            throw new Exception("Class $abstract is not instantiable");
        }

        $reflected_parameters = $this->reflectParams($abstract, '__construct');
        if (!$reflected_parameters) {
            return new $abstract;
        }

        $constructor_dependencies = $this->dependencies($reflected_parameters, $parameters);
        return new $abstract(...$constructor_dependencies);
    }

    private function dependencies(array $reflected_parameters, array $parameters) : array {
        //TODO: implement union / intersection
        return array_map(function (ReflectionParameter $reflected_parameter) use ($parameters) {
            $name = $reflected_parameter->getName();
            $type = $reflected_parameter->getType();
            $class = $reflected_parameter->getDeclaringClass()->name;
            
            return match (true) {
                !$type => throw new Exception("Failed to resolve class $class because parameter $name is missing a type hint"),
                $type instanceof ReflectionUnionType => throw new Exception("Failed to resolve class $class because of union type for parameter $name"),
                $type instanceof ReflectionIntersectionType => throw new Exception("Failed to resolve class $class because of intersection type for parameter $name"),
                $type instanceof ReflectionNamedType && !$type->isBuiltin() => $this->get($name, $parameters),
                $type instanceof ReflectionNamedType && $type->isBuiltin() && isset($parameters[$name]) => $parameters[$name],
                default => $reflected_parameter->isDefaultValueAvailable() ? $reflected_parameter->getDefaultValue() : throw new Exception("Failed to resolve class $class because of invalid parameter $name")
            };
        }, $reflected_parameters);
    }
}
