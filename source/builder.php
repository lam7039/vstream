<?php

namespace source;

interface SqlBuilderInterface {
    public function fetch(string $sql, array $variables = []) : object|null;
    public function execute(string $sql, array $variables = []) : bool;
    public function execute_multiple(array $sql_queries, array $variables = []) : bool;

    public function find(array $where = [], string|array $columns = '*', string|array $comparitor = '=', string|array $conjunctor = 'and', int $limit = 0) : object|null;
    public function insert(array $columns) : int;
    public function update(array $columns, array $where = [], string|array $comparitor = '=', string|array $conjunctor = 'and') : bool;
    public function delete(array $where, string|array $comparitor = '=', string|array $conjunctor = 'and') : bool;
}

//TODO: implement caching here between the application and database
class MysqlBuilder implements SqlBuilderInterface {
    private Database $database;

    //TODO: implement joins for find
    public function __construct(private string|array $table, private string|null $model = null) {
        $this->database = MysqlDatabase::get();
    }
    
    #[\Override]
    public function fetch(string $sql, array $variables = []) : object|null {
        return $this->database->fetch($sql, $variables);
    }
    
    #[\Override]
    public function execute(string $sql, array $variables = []) : bool {
        return $this->database->execute($sql, $variables);
    }

    #[\Override]
    public function execute_multiple(array $sql_queries, array $variables = []) : bool {
        return $this->database->execute_multiple($sql_queries, $variables);
    }
    
    #[\Override]
    public function find(array $where = [], string|array $columns = '*', string|array $comparitor = '=', string|array $conjunctor = 'and', int $limit = 0) : object|null {
        $select_str = is_array($columns) ? implode(', ', $columns) : $columns;
        $sql = "select $select_str from {$this->table}";
        if ($where) {
            $sql .= ' ' . $this->build_where($where, $comparitor, $conjunctor);
        }
        if ($limit) {
            $sql .= ' limit ' . $limit;
        }
        if ($this->model) {
            return $this->database->fetch($sql, $where, ResponseMode::Model, $this->model) ?? null;
        }
        return $this->database->fetch($sql, $where) ?? null;
    }

    #[\Override]
    public function insert(array $columns) : int {
        $columns_keys = array_keys($columns);
        $columns_str = implode(', ', $columns_keys);
        $values_str = ':' . implode(', :', $columns_keys);
        $sql = "insert into {$this->table} ($columns_str) values ($values_str)";
        if ($this->database->execute($sql, $columns)) {
            return $this->database->last_inserted_id;
        }
        return 0;
    }

    #[\Override]
    public function update(array $columns, array $where = [], string|array $comparitor = '=', string|array $conjunctor = 'and') : bool {
        $sql = "update {$this->table} set {$this->build_set($columns)}";
        if ($where) {
            $sql .= ' ' . $this->build_where($where, $comparitor, $conjunctor);
        }
        return $this->database->execute($sql, array_merge($columns, $where));
    }

    #[\Override]
    public function delete(array $where, string|array $comparitor = '=', string|array $conjunctor = 'and') : bool {
        $sql = "delete from {$this->table}";
        if ($where) {
            $sql .= ' ' . $this->build_where($where, $comparitor, $conjunctor);
        }
        return $this->database->execute($sql, $where);
    }

    private function build_where(array $where, string|array $comparitor = '=', string|array $conjunctor = 'and') : string {
        $where_str = '';
        foreach (array_keys($where) as $i => $column) {
            $where_str .= "where $column ";
            $where_str .= (is_array($comparitor) ? $comparitor[$i] : $comparitor) . " :$column ";
            $where_str .= (is_array($conjunctor) ? $conjunctor[$i] : $conjunctor) . ' ';
        }
        return substr($where_str, 0, -(strlen(is_array($conjunctor) ? end($conjunctor) : $conjunctor) + 2));
    }

    private function build_set(array $set) : string {
        $set_str = '';
        foreach (array_keys($set) as $column) {
            $set_str .= " $column = :$column,";
        }
        return substr($set_str, 0, -1);
    }
}
