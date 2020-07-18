<?php

namespace models;

use library\database;

class access extends model {

    public function __construct(database $database) {
        parent::__construct($database);
        $this->table = 'access';
    }
}