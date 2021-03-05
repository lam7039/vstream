<?php

namespace models;

use source\sql_builder;
use source\builder;

abstract class model implements sql_builder {
    protected string $table;
    protected sql_builder $builder;

    public function __construct() {
        $this->builder = new builder($this->table);
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

    public function find(array $where = [], array $columns = ['*'], int $limit = 0) : object|null {
        return $this->builder->find($where, $columns, $limit);
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
