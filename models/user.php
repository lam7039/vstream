<?php

namespace models;

use source\database;

class user extends model {
    protected string $table = 'users';

    public function __construct(database $database) {
        parent::__construct($database);
    }
}