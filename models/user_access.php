<?php

namespace models;

use library\database;

class user_access extends model {

    public function __construct(database $database) {
        parent::__construct($database);
        $this->table = 'users_access';
    }
}