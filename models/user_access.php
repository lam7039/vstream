<?php

namespace models;

use library\database;

class user_access extends model {
    protected string $table = 'users_access';

    public function __construct(database $database) {
        parent::__construct($database);
    }
}