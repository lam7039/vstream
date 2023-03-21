<?php

namespace source;

use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

class container {
    private array $instances = [];

    public function set(string $abstract, callable|string $concrete = null) : void {
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

    private function resolve(callable|string $abstract, array $parameters) : object {
        if (is_callable($abstract)) {
            return $abstract($this, $parameters);
        }

        $reflector = new ReflectionClass($abstract);
        if (!$reflector->isInstantiable()) {
            throw new Exception("Class $abstract is not instantiable");
        }

        $constructor = $reflector->getConstructor();
        $parameters = $constructor->getParameters();
        if (!$constructor || !$parameters) {
            return new $abstract;
        }

        $dependencies = $this->dependencies($parameters, $abstract);
        return new $abstract(...$dependencies);
    }

    private function dependencies(array $parameters, string $abstract) : array {
        return array_map(function (ReflectionParameter $parameter) use ($abstract) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (!$type) {
                throw new Exception("Failed to resolve class $abstract because parameter $name is missing a type hint");
            }

            if ($type instanceof ReflectionUnionType) {
                throw new Exception("Failed to resolve class $abstract because of union type for parameter $name");
            }

            if ($type instanceof ReflectionIntersectionType) {
                throw new Exception("Failed to resolve class $abstract because of intersection type for parameter $name");
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                //TODO: implement default values and builtIn types (maybe also even union and intersection)
                return $this->get($name);
            }

            throw new Exception("Failed to resolve class $abstract because of invalid param $name");
        }, $parameters);
    }
}
