<?php

namespace models;

use source\database;

class user extends model {
    protected string $table = 'users';

    public function __construct(database $database) {
        parent::__construct($database);
    }

    public function access_user_data(int $user_access_id, array $columns = ['*']) : ?object {
        $select_str = $this->sql_columns($columns) . ', users_access.expiry';
        $sql = "select $select_str from {$this->table} join users_access on {$this->table}.id = users_access.user_id where users_access.id = :user_access_id";
        return $this->fetch($sql, ['user_access_id' => $user_access_id]) ?? null;
    }
}