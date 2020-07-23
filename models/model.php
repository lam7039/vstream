<?php

namespace models;

use library\database;
use library\sql_builder;
use library\builder;

abstract class model implements sql_builder {
    protected string $table;
    protected sql_builder $builder;

    public function __construct(database $database) {
        $this->builder = new builder($database, $this->table);
    }

    public function execute(string $sql, array $variables = []) : bool {
        return $this->builder->execute($sql, $variables);
    }

    public function fetch(string $sql, array $variables = []) : ?object {
        return $this->builder->fetch($sql, $variables);
    }

    public function find(array $where = [], array $columns = ['*']) : ?object {
        return $this->builder->find($where, $columns);
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

    public function sql_columns(array $columns, bool $colon = false) : string {
        return $this->builder->sql_columns($columns, $colon);
    }
}