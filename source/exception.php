<?php

namespace source;

use Exception;
use PDOException;

class DatabaseException extends PDOException {
    public function __construct() {
        parent::__construct($this->getMessage(), 3);
    }
};

class RouteNotFoundException extends Exception {
    public function __construct(string $path) {
        parent::__construct('Route not found: ' . $path, 3);
    }
}

class CsrfFailedException extends Exception {
    public function __construct() {
        parent::__construct('CSRF check has failed', 3);
    }
}
