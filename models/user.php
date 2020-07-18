<?php

namespace models;

use library\database;

class user extends model {

    public function __construct(database $database) {
        parent::__construct($database);
        $this->table = 'users';
    }

    public function access_user_data(int $access_id, array $columns = ['*']) : ?object {
        $select_str = $this->sql_columns($columns) . ', access.expiry';
        $sql = "select $select_str from {$this->table} join access on {$this->table}.id = access.user_id where access.id = :access_id";
		return $this->database->fetch($sql, ['access_id' => $access_id]) ?? null;
    }
}