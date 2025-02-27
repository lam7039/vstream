<?php

namespace source;

use Exception;
use PDOException;

class DatabaseException extends PDOException {
    public function __construct() {
        if (!DEBUG) {
            //TODO: display generic 500 error page
            return;
        }
        parent::__construct($this->getMessage(), 3);
    }
}

class RouteNotFoundException extends Exception {
    public function __construct(string $path) {
        if (!DEBUG) {
            //TODO: display generic 404 error page
            return;
        }
        //TODO: $path is at risk of query injection
        parent::__construct('Route not found: ' . $path, 3);
    }
}

class CsrfFailedException extends Exception {
    public function __construct() {
        if (!DEBUG) {
            //TODO: display generic 500 error page
            return;
        }
        parent::__construct('CSRF check has failed', 3);
    }
}
