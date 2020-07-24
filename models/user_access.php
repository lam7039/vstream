<?php

namespace models;

use source\database;

class user_access extends model {
    protected string $table = 'users_access';

    public function __construct(database $database) {
        parent::__construct($database);
    }
}