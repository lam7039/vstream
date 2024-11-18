<?php

namespace source;

use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

// class ServiceProvider {
//     private static array $services = [];

//     public function __construct(private Container $container) {
//         $container->bind()
//     }

//     public static function get(string $class) : object|null {
//         return self::$container->get($class);
//     }

//     public static function add(string $identifier, callable|string|null $concrete = null) : void {
//         self::$container->bind($identifier, $concrete);
//     }
// }

class Container {
    public function __construct(private array $instances = []) {}

    public function bind(string $identifier, callable|string|null $concrete = null) : void {
        if (!$concrete) {
            $concrete = $identifier;
        }
        $this->instances[$identifier] = $concrete;
    }

    public function get(string $identifier, array $parameters = []) {
        if (!$this->has($identifier)) {
            throw new Exception("Could not find class $identifier");
        }
        return $this->resolve($this->instances[$identifier], $parameters);
    }

    public function has(string $id) {
        return isset($this->instances[$id]);
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

        //TODO: make instanced classes fetchable from instances so it won't have to re-instance over and over again
        $reflected_parameters = $this->reflectParams($abstract, '__construct');
        if (!$reflected_parameters) {
            return new $abstract;
        }

        $constructor_dependencies = $this->resolveDependencies($reflected_parameters, $parameters);
        // echo '<pre>';
        // var_dump($constructor_dependencies);
        // exit;
        return new $abstract(...$constructor_dependencies);
    }

    private function resolveDependencies(array $reflected_parameters, array $parameters) : array {
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
                default => throw new Exception("Failed to resolve class $class because of invalid parameter $parameterName")
            };
        }, $reflected_parameters);
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
