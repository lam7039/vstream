<?php

namespace models;

use library\database;

class model {
    protected database $database;
    protected string $table;

    public function __construct(database $database) {
        $this->database = $database;
    }

    //TODO: fix/improve this, otherwise check if this is actually necessary/useful
	public function find(array $where = [], array $columns = ['*']) : ?object {
        $select_str = $where_str = '';
        foreach ($columns as $column) {
            $select_str .= $column . ', ';
        }
        $select_str = substr($select_str, 0, -2);

        if ($where) {
            foreach (array_keys($where) as $column) {
                $where_str .= "where $column = :$column and ";
            }
            $where_str = substr($where_str, 0, -4);
        }

        $sql = trim("select $select_str from {$this->table} $where_str");
		return $this->database->fetch($sql, $where) ?? null;
    }
}