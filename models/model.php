<?php

namespace models;

use source\{SqlBuilderInterface, MysqlBuilder};

//TODO: add encryption
abstract class AbstractModel implements SqlBuilderInterface {
    protected string $table;
    private SqlBuilderInterface $builder;

    public function __construct(private array $columns = [], bool $insert = false) {
        $this->builder = new MysqlBuilder($this->table, get_called_class());
        if ($insert && $columns) {
            $this->insert($columns);
        }
    }

    public function __set(string $name, mixed $value) : void {
        $this->columns[$name] = $value;
    }

    public function __get(string $name) : mixed {
        return $this->columns[$this->$name] ?? null;
    }

    #[\Override]
    public function fetch(string $sql, array $variables = []) : object|null {
        return $this->builder->fetch($sql, $variables);
    }

    #[\Override]
    public function execute(string $sql, array $variables = []) : bool {
        return $this->builder->execute($sql, $variables);
    }

    #[\Override]
    public function execute_multiple(array $sql_queries, array $variables = []) : bool {
        return $this->builder->execute_multiple($sql_queries, $variables);
    }

    #[\Override]
    public function find(array $where = [], string|array $columns = '*', string|array $comparitor = '=', string|array $conjunctor = 'and', int $limit = 0) : object|null {
        return $this->builder->find($where, $columns, $comparitor, $conjunctor, $limit);
    }

    #[\Override]
    public function insert(array $columns) : int {
        return $this->builder->insert($columns);
    }

    #[\Override]
    public function update(array $columns, array $where = [], string|array $comparitor = '=', string|array $conjunctor = 'and') : bool {
        return $this->builder->update($columns, $where, $comparitor, $conjunctor);
    }

    #[\Override]
    public function delete(array $where, string|array $comparitor = '=', string|array $conjunctor = 'and') : bool {
        return $this->builder->delete($where, $comparitor, $conjunctor);
    }
}
