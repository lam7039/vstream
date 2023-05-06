<?php

namespace source;

use Exception;
use PDOException;

class DatabaseException extends PDOException {
    public function __construct() {
        LOG_CRITICAL($this->getMessage());
    }
};
