<?php

namespace source;

interface sql_builder {
    public function fetch(string $sql, array $variables = []) : object|null;
    public function execute(string $sql, array $variables = []) : bool;
    public function execute_multiple(array $sql_queries, array $variables = []) : bool;

    public function find(array $where = [], string|array $columns = '*', string|array $comparitor = '=', int $limit = 0) : object|null;
    public function insert(array $columns) : int;
    public function update(array $columns, array $where = []) : bool;
    public function delete(array $where) : bool;
}

//TODO: implement caching here between the application and database
class mysql_builder implements sql_builder {
    private database $database;

    public function __construct(private string $table) {
        $this->database = db::get();
    }
    
    public function fetch(string $sql, array $variables = []) : object|null {
        return $this->database->fetch($sql, $variables);
    }
    
    public function execute(string $sql, array $variables = []) : bool {
        return $this->database->execute($sql, $variables);
    }

    public function execute_multiple(array $sql_queries, array $variables = []) : bool {
        return $this->database->execute_multiple($sql_queries, $variables);
    }
    
    public function find(array $where = [], string|array $columns = '*', string|array $comparitor = '=', int $limit = 0) : object|null {
        $select_str = $this->sql_columns($columns);
        $sql = "select $select_str from {$this->table}";
        if ($where) {
            $sql .= ' ' . $this->sql_where($where, $comparitor);
        }
        if ($limit) {
            $sql .= ' limit ' . $limit;
        }
        return $this->database->fetch($sql, $where) ?? null;
    }

    public function insert(array $columns) : int {
        $columns_keys = array_keys($columns);
        $columns_str = $this->sql_columns($columns_keys);
        $values_str = $this->sql_columns($columns_keys, true);
        $sql = "insert into {$this->table} ($columns_str) values ($values_str)";
        if ($this->database->execute($sql, $columns)) {
            return $this->database->last_inserted_id;
        }
        return 0;
    }

    public function update(array $columns, array $where = []) : bool {
        $sql = "update {$this->table} set {$this->sql_set($columns)}";
        if ($where) {
            $sql .= ' ' . $this->sql_where($where);
        }
        return $this->database->execute($sql, array_merge($columns, $where));
    }

    public function delete(array $where) : bool {
        $sql = "delete from {$this->table}";
        if ($where) {
            $sql .= ' ' . $this->sql_where($where);
        }
        return $this->database->execute($sql, $where);
    }

    private function sql_columns(string|array $columns, bool $colon = false) : string {
        if (!is_array($columns)) {
            return $columns;
        }
        $select_str = '';
        foreach ($columns as $column) {
            if ($colon) {
                $select_str .= ':';
            }
            $select_str .= $column . ', ';
        }
        return substr($select_str, 0, -2);
    }

    private function sql_where(array $where, string|array $comparitor = '=', string|array $operator = 'and') : string {
        $where_str = '';
        $comparitor_is_array = is_array($comparitor);
        $operator_is_array = is_array($operator);
        foreach (array_keys($where) as $i => $column) {
            $where_str .= "where $column ";
            $where_str .= ($comparitor_is_array ? $comparitor[$i] : $comparitor) . " :$column ";
            $where_str .= ($operator_is_array ? $operator[$i] : $operator) . ' ';
        }
        return substr($where_str, 0, -(strlen($operator_is_array ? end($operator) : $operator) + 2));
    }

    private function sql_set(array $set) : string {
        $set_str = '';
        foreach (array_keys($set) as $column) {
            $set_str .= " $column = :$column,";
        }
        return substr($set_str, 0, -1);
    }
}
