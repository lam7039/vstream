<?php

namespace source;

use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

//TODO: make container work for all functions, not just the constructor
class Container {
    public function __construct(private array $instances = []) {
        foreach ($this->instances as $key => $instance) {
            if (!class_exists($instance)) {
                throw ContainerException::InstanceFailed($instance);
            }

            if (isset($this->instances[$instance])) {
                continue;
            }

            unset($this->instances[$key]);
            $this->instances[$instance] = $instance;
        }
    }

    public function bind(string $identifier, callable|string|null $concrete = null) : void {
        if (!$concrete) {
            $concrete = $identifier;
        }
        $this->instances[$identifier] = $concrete;
    }

    public function get(string $identifier, array $parameters = []) {
        if (!$this->has($identifier)) {
            throw ContainerException::UnknownClass($identifier);
        }
        return $this->resolve($this->instances[$identifier], $parameters);
    }

    public function has(string $id) {
        return isset($this->instances[$id]);
    }

    private function reflected_parameters(string $class, string $method) : array {
        if (!method_exists($class, $method)) {
            return [];
        }
        $reflected_method = new ReflectionMethod($class, $method);
        return $reflected_method->getParameters();
    }

    public function get_method_params(string $class, string $method) : array {
        return array_column($this->reflected_parameters($class, $method), 'name');
    }

    private function resolve(mixed $abstract, array $parameters) : object {
        if (is_callable($abstract)) {
            return $abstract($this, $parameters);
        }

        $reflector = new ReflectionClass($abstract);
        if (!$reflector->isInstantiable()) {
            throw ContainerException::Uninstantiable($abstract);
        }

        //TODO: make instanced classes fetchable from instances so it won't have to re-instance over and over again
        // if ($this->has($abstract)) {
        //     return $this->get($abstract, $parameters);
        // }

        $reflected_parameters = $this->reflected_parameters($abstract, '__construct');
        if (!$reflected_parameters) {
            return new $abstract;
        }

        $constructor_dependencies = $this->resolve_dependencies($reflected_parameters, $parameters);
        // dd($constructor_dependencies);
        return new $abstract(...$constructor_dependencies);
    }

    private function resolve_dependencies(array $reflected_parameters, array $parameters) : array {
        return array_map(function (ReflectionParameter $reflected_parameter) use ($parameters) {
            if ($reflected_parameter->isDefaultValueAvailable()) {
                return $reflected_parameter->getDefaultValue();
            }

            $parameterName = $reflected_parameter->getName();
            $parameterType = $reflected_parameter->getType();
            $class = $reflected_parameter->getDeclaringClass()->name;

            return match (true) {
                $parameterType instanceof ReflectionNamedType => $this->resolve_named_type($parameterType, $parameterName, $parameters),
                $parameterType instanceof ReflectionUnionType => $this->resolve_union_type($parameterType, $parameterName, $parameters),
                // $parameterType instanceof ReflectionIntersectionType => $this->resolve_intersection_type($parameterType, $parameterName, $parameters),
                $parameterType instanceof ReflectionIntersectionType => throw ContainerException::InvalidIntersection($class, $parameterName),
                default => throw ContainerException::InvalidParameter($class, $parameterName)
            };
        }, $reflected_parameters);
    }

    private function resolve_named_type(ReflectionNamedType $namedType, string $parameterName, array $parameters) : mixed {
        if ($namedType->isBuiltIn() && isset($parameters[$parameterName])) {
            return $parameters[$parameterName];
        }
        if (!$namedType->isBuiltin()) {
            return $this->get($namedType->getName(), $parameters);
        }
        throw ContainerException::UnknownParameter($parameterName);
    }

    private function resolve_union_type(ReflectionUnionType $unionType, string $parameterName, array $parameters) : mixed {
        $namedTypes = $unionType->getTypes();
        $value = null;
        foreach ($namedTypes as $namedType) {
            $value = $this->resolve_named_type($namedType, $parameterName, $parameters);
        }
        return $value;
    }

    // //TODO: test intersection resolve
    // private function resolve_intersection_type(ReflectionIntersectionType $intersectionType, string $parameterName, array $parameters) : mixed {
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
}

/////////////// IMPLEMENTATION EXAMPLE //////////////////
//
// interface AbstractTestClass {
//
// }
//
// class InjectionTestClass implements AbstractTestClass {
//     public function testOutput() {
//         echo 'test output';
//     }
// }
//
// class TestClass {
//     public function __construct(InjectionTestClass $test) {
//         $test->testOutput();
//     }
//
//     public function asdf(string $b) {
//
//     }
//
// }
//
// $container = new container([
//     InjectionTestClass::class => InjectionTestClass::class,
//     'test' => TestClass::class
// ]);
//
// $testClass = $container->get('test');
//
/////////////////////////////////////////////////////////
