<?php

namespace source;

use Exception;
use PDOException;

class DatabaseException extends PDOException {
    public function __construct() {
        LOG_CRITICAL($this->getMessage());
    }
};

class RouteNotFoundException extends Exception {
    public function __construct(string $path) {
        LOG_CRITICAL('Route not found: ' . $path);
    }
}
