<?php

namespace models;

use source\sql_builder;
use source\mysql_builder;

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

    public function find(array $where = [], string|array $columns = '*', string|array $comparitor = '=', int $limit = 0) : object|null {
        return $this->builder->find($where, $columns, $comparitor, $limit);
    }

    public function insert(array $columns) : int {
        return $this->builder->insert($columns);
    }

    public function update(array $columns, array $where = []) : bool {
        return $this->builder->update($columns, $where);
    }

    public function delete(array $where) : bool {
        return $this->builder->delete($where);
    }
}
