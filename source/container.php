<?php

namespace source;

use Exception;
use ReflectionClass;

class Container {
    private array $instances = [];

    public function set(string $key, string|object|callable $instance = null) : void {
        if (!$instance) {
            $instance = $key;
        }
        $this->instances[$key] = $instance;
    }

    public function get(string $key, array $parameters = []) {
        if (!isset($this->instances[$key])) {
            $this->set($key);
        }
        return $this->resolve($this->instances[$key], $parameters);
    }

    private function resolve(string|object|callable $instance, array $parameters) {
        if (is_callable($instance)) {
            return $instance(...$parameters);
        }

        $reflector = new ReflectionClass($instance);
        if (!$reflector->isInstantiable()) {
            throw new Exception("Class $instance is not instantiable");
        }

        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            return $reflector->newInstance();
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->dependencies($parameters);

        return $reflector->newInstance(...$dependencies);
    }

    private function dependencies(array $parameters) : array {
        $dependencies = [];
        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();
            if ($dependency) {
                $dependencies[] = $this->get($dependency->name);
                continue;
            }
            if (!$parameter->isDefaultValueAvailable()) {
                throw new Exception("Can not resolve class dependency {$parameter->name}");
            }
            $dependencies[] = $parameter->getDefaultValue();
        }
        return $dependencies;
    }
}
