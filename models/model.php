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

    public function insert(array $columns) : int {
        $columns_str = $this->sql_columns(array_keys($columns));
        $values_str = $this->sql_columns(array_keys($columns), true);
        $sql = "insert into {$this->table} ($columns_str) values ($values_str)";
        if ($this->database->execute($sql, $columns)) {
            return $this->database->last_inserted_id;
        }
        LOG_WARNING('Insert query failed: ' . $sql);
        return -1;
    }

    public function delete(int $id) : bool {
        if (!$id) {
            return false;
        }
        $sql = "delete from {$this->table} where id = :id";
        $deleted = $this->database->execute($sql, ['id' => $id]);
        if (!$deleted) {
            LOG_WARNING('Delete query failed: ' . $sql);
        }
        return $deleted;
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