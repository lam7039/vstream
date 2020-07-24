<?php

namespace source;

use PDO;
use PDOException;
use PDOStatement;

class database {
    private PDO $connection;
    public int $last_inserted_id = 0;

    public function __construct() {
        $host = env('DB_HOST');
        $dbname = env('DB_DATABASE');
        $port = env('DB_PORT');
        $charset = env('DB_CHARSET');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        try {
            $this->connection = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=$charset", $username, $password, $options);
        } catch (PDOException $e) {
            LOG_CRITICAL($e->getMessage());
        }
    }

    private function find_param_type($value) : int {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        }
        if (is_bool($value)) {
            return PDO::PARAM_BOOL;
        }
        if (is_file($value)) {
            return PDO::PARAM_LOB;
        }
        if (is_null($value)) {
            return PDO::PARAM_NULL;
        }
        return PDO::PARAM_STR;
    }

    private function query(string $sql, array $variables = []) : PDOStatement {
        $query = $this->connection->prepare($sql);
        foreach ($variables as $key => $value) {
            if (!$query->bindValue(":$key", $value, $this->find_param_type($value))) {
                LOG_WARNING("Failed to bind $key with $value");
            }
        }
        return $query;
    }

    public function transaction() : bool {
        try {
            return $this->connection->beginTransaction();
        } catch (PDOException $e) {
            LOG_WARNING($e->getMessage());
        }
        return false;
    }

    public function commit() : bool {
        try {
            $this->last_inserted_id = $this->connection->lastInsertId();
            return $this->connection->commit();
        } catch (PDOException $e) {
            LOG_WARNING($e->getMessage());
        }
        return false;
    }

    public function rollback() : bool {
        try {
            return $this->connection->rollBack();
        } catch (PDOException $e) {
            LOG_WARNING($e->getMessage());
        }
        return false;
    }

    public function execute(string $sql, array $variables = []) : bool {
        try {
            $executed = $this->query($sql, $variables)->execute();
            $this->last_inserted_id = $this->connection->lastInsertId();
            return $executed;
        } catch (PDOException $e) {
            LOG_WARNING($e->getMessage());
        }
        return false;
    }

    //TODO: improve this
    public function execute_multiple(array $sql_queryies, array $variables = []) : bool {
        $this->transaction();
        try {
            foreach ($sql_queryies as $key => $sql) {
                if (!is_string($sql)) {
                    LOG_WARNING("Invalid type in \$sql_queries: " . gettype($sql) . "($sql)");
                    continue;
                }
                if (!$this->query($sql, $variables[$key])->execute()) {
                    LOG_WARNING("Invalid SQL: $sql");
                    $this->rollback();
                    return false;
                }
            }
            return $this->commit();
        } catch (PDOException $e) {
            LOG_WARNING($e->getMessage());
            $this->rollback();
        }
        return false;
    }

    public function fetch(string $sql, array $variables = []) : ?object {
        try {
            $query = $this->query($sql, $variables);
            if ($query->execute() && $response = $query->fetch(PDO::FETCH_OBJ)) {
                return $response;
            }
        } catch (PDOException $e) {
            LOG_WARNING($e->getMessage());
        }
        return null;
    }
}