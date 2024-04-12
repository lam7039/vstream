<?php

namespace source;

use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

class Container {
    public function __construct(private array $object_pool = []) {}

    public function set(string $identifier, callable|string|null $concrete = null) : void {
        if (!$concrete) {
            $concrete = $identifier;
        }
        $this->object_pool[$identifier] = $concrete;
    }

    public function get(string $identifier, array $parameters = []) : object {
        if (!$this->has($identifier)) {
            throw new Exception("Could not find class $identifier");
        }
        return $this->resolve($this->object_pool[$identifier], $parameters);
    }

    public function has(string $identifier) : bool {
        return isset($this->object_pool[$identifier]);
    }

    private function reflectParams(string $class, string $method) : array {
        if (!method_exists($class, $method)) {
            return [];
        }
        $reflected_method = new ReflectionMethod($class, $method);
        return $reflected_method->getParameters();
    }

    public function getMethodParams(string $class, string $method) : array {
        return array_column($this->reflectParams($class, $method), 'name');
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

    private function resolveNamedType(ReflectionNamedType $namedType, string $parameterName, array $parameters) : mixed {
        if ($namedType->isBuiltIn() && isset($parameters[$parameterName])) {
            return $parameters[$parameterName];
        }
        if (!$namedType->isBuiltin()) {
            return $this->get($namedType->getName(), $parameters);
        }
        throw new Exception("Could not find parameter $parameterName");
    }

    private function resolveUnionType(ReflectionUnionType $unionType, string $parameterName, array $parameters) : mixed {
        $namedTypes = $unionType->getTypes();
        $value = null;
        foreach ($namedTypes as $namedType) {
            $value = $this->resolveNamedType($namedType, $parameterName, $parameters);
        }
        return $value;
    }

    // //TODO: test intersection resolve
    // private function resolveIntersectionType(ReflectionIntersectionType $intersectionType, string $parameterName, array $parameters) : mixed {
    //     $namedTypes = $intersectionType->getTypes();
    //     $reflector = new ReflectionClass($parameterName);
    //     $value = null;
    //     foreach ($namedTypes as $namedType) {
    //         if (!$reflector->implementsInterface($namedType)) {
    //             return null;
    //         }
    //         $value = $this->get($namedType->getName(), $parameters);
    //     }
    //     return $value;
    // }

    private function dependencies(array $reflected_parameters, array $parameters) : array {
        return array_map(function (ReflectionParameter $reflected_parameter) use ($parameters) {
            if ($reflected_parameter->isDefaultValueAvailable()) {
                return $reflected_parameter->getDefaultValue();
            }

            $parameterName = $reflected_parameter->getName();
            $parameterType = $reflected_parameter->getType();
            $class = $reflected_parameter->getDeclaringClass()->name;

            return match (true) {
                $parameterType instanceof ReflectionNamedType => $this->resolveNamedType($parameterType, $parameterName, $parameters),
                $parameterType instanceof ReflectionUnionType => $this->resolveUnionType($parameterType, $parameterName, $parameters),
                // $parameterType instanceof ReflectionIntersectionType => $this->resolveIntersectionType($parameterType, $parameterName, $parameters),
                $parameterType instanceof ReflectionIntersectionType => throw new Exception("Failed to resolve class $class because of intersection type for parameter $parameterName"),
                // !$parameterType => throw new Exception("Failed to resolve class $class because parameter $parameterName is missing a type hint"),
                default => throw new Exception("Failed to resolve class $class because of invalid parameter $parameterName")
            };
        }, $reflected_parameters);
    }
}
