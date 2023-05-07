<?php

namespace models;

use source\{sql_builder, mysql_builder};

//TODO: add encryption
abstract class model implements sql_builder {
    protected string $table;
    protected sql_builder $builder;

    public function __construct(array $columns = []) {
        $this->builder = new mysql_builder($this->table);
        if ($columns) {
            $this->insert($columns);
        }
    }

    public function fetch(string $sql, array $variables = []) : object|null {
        return $this->builder->fetch($sql, $variables);
    }

    public function execute(string $sql, array $variables = []) : bool {
        return $this->builder->execute($sql, $variables);
    }

    public function execute_multiple(array $sql_queries, array $variables = []) : bool {
        return $this->builder->execute_multiple($sql_queries, $variables);
    }

    public function find(array $where = [], string|array $columns = '*', string|array $comparitor = '=', string|array $conjunctor = 'and', int $limit = 0) : object|null {
        return $this->builder->find($where, $columns, $comparitor, $conjunctor, $limit);
    }

    public function insert(array $columns) : int {
        return $this->builder->insert($columns);
    }

    public function update(array $columns, array $where = [], string|array $comparitor = '=', string|array $conjunctor = 'and') : bool {
        return $this->builder->update($columns, $where, $comparitor, $conjunctor);
    }

    public function delete(array $where, string|array $comparitor = '=', string|array $conjunctor = 'and') : bool {
        return $this->builder->delete($where, $comparitor, $conjunctor);
    }
}
