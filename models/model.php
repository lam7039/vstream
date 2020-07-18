<?php

namespace models;

use library\database;

class model {
    protected database $database;
    protected string $table;

    public function __construct(database $database) {
        $this->database = $database;
    }

	public function find(array $where = [], array $columns = ['*']) : ?object {
        $select_str = $this->sql_columns($columns);
        $sql = "select $select_str from {$this->table}";
        if ($where) {
            $sql .= ' ' . $this->sql_where($where);
        }
		return $this->database->fetch($sql, $where) ?? null;
    }

    protected function sql_columns(array $columns) : string {
        $select_str = '';
        foreach ($columns as $column) {
            $select_str .= $column . ', ';
        }
        return substr($select_str, 0, -2);
    }

    protected function sql_where(array $where, string $operator = 'and') : string {
        $where_str = '';
        foreach (array_keys($where) as $column) {
            $where_str .= "where $column = :$column $operator ";
        }
        return substr($where_str, 0, -(strlen($operator) + 2));
    }
}