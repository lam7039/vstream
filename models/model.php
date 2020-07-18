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

    public function insert(array $columns, array $values) : int {
        $columns_str = $this->sql_columns($columns);
        $values_str = $this->sql_columns($columns, true);
        if ($this->database->execute("insert into {$this->table} ($columns_str) values ($values_str)", array_combine($columns, $values))) {
            return $this->database->last_inserted_id;
        }
        return -1;
    }

    public function delete(int $id) : bool {
        return $this->database->execute("delete from {$this->table} where id = :id", ['id' => $id]);
    }

    protected function sql_columns(array $columns, bool $colon = false) : string {
        $select_str = '';
        foreach ($columns as $column) {
            if ($colon) {
                $select_str .= ':';
            }
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