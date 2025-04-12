<?php

namespace source;

use Exception;
use PDOException;

//TODO: change DatabaseException to static functions that return self with specific errors
class DatabaseException extends PDOException {
    public function __construct() {
        if (!DEBUG) {
            //TODO: display generic 500 error page
            redirect('/browse');
            exit;
        }
        parent::__construct($this->getMessage(), 3);
    }
}

class RequestException extends Exception {
    public static function RouteNotFound(string $route) : self {
        if (!DEBUG) {
            //TODO: display generic 404 error page
            redirect('/browse');
            exit;
        }

        return new self('Route not found: ' . $route, 1);
    }

    public static function CsrfFailed() : self {
        if (!DEBUG) {
            //TODO: display generic 500 error page
            redirect('/browse');
            exit;
        }

        return new self('CSRF check has failed', 2);
    }
}

class ContainerException extends Exception {
    public static function ClassNotFound(string $class) : self {
        return new self('Could not find class: ' . $class, 3);
    }

    public static function MethodNotFound(string $method) : self {
        return new self('Could not find method: ' . $method, 3);
    }

    public static function ParameterNotFound(string $parameter) : self {
        return new self('Could not find parameter: ' . $parameter, 3);
    }

    public static function InstanceNotFound(string $instance) : self {
        return new self('Instance does not exist: ' . $instance, 3);
    }

    public static function InvalidInstance(string $instance) : self {
        return new self('Could not instantiate: ' . $instance, 3);
    }

    public static function InvalidIntersection(string $class, string $parameter) : self {
        return new self("Failed to resolve class '$class' because of intersection type for parameter '$parameter'", 3);
    }

    public static function InvalidParameter(string $class, string $parameter) : self {
        return new self("Failed to resolve class '$class' because of invalid parameter '$parameter'", 3);
    }
}
