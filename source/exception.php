<?php

namespace source;

use Exception;
use PDOException;

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

class RouteNotFoundException extends Exception {
    public function __construct(string $path) {
        if (!DEBUG) {
            //TODO: display generic 404 error page
            redirect('/browse');
            exit;
        }
        //TODO: $path is at risk of query injection
        parent::__construct('Route not found: ' . $path, 1);
    }
}

class CsrfFailedException extends Exception {
    public function __construct() {
        if (!DEBUG) {
            //TODO: display generic 500 error page
            redirect('/browse');
            exit;
        }
        parent::__construct('CSRF check has failed', 2);
    }
}

class ContainerInstanceFailedException extends Exception {
    public function __construct(string $instance) {
        if (!DEBUG) {
            //TODO: display generic 500 error page
            redirect('/browse');
            exit;
        }
        parent::__construct('Instance does not exist: ' . $instance, 3);
    }
}
