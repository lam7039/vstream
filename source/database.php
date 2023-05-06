<?php

namespace source;

use PDO;
use PDOException;
use PDOStatement;

class database {
    private PDO $connection;
    public readonly int $last_inserted_id = 0;
    public readonly int $rows_affected = 0;

    public function __construct() {
        $host = env('DB_HOST');
        $dbname = env('DB_DATABASE');
        $port = env('DB_PORT');
        $charset = env('DB_CHARSET');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->connection = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=$charset", $username, $password, $options);
        } catch (PDOException $e) {
            LOG_CRITICAL($e->getMessage());
        }
    }

    private function find_param_type($value) : int {
        return match (gettype($value)) {
            'integer' => PDO::PARAM_INT,
            'boolean' => PDO::PARAM_BOOL,
            'object' => PDO::PARAM_LOB,
            'NULL' => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };
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
            $query = $this->query($sql, $variables);
            $executed = $query->execute();
            $this->rows_affected = $query->rowCount();
            $this->last_inserted_id = $this->connection->lastInsertId();
            return $executed;
        } catch (PDOException $e) {
            LOG_WARNING($e->getMessage());
        }
        return false;
    }

    public function execute_multiple(array $sql_queries, array $variables = []) : bool {
        $this->transaction();
        $sql = implode(';', $sql_queries);
        if (!$this->query($sql, $variables)) {
            $this->rollback();
            return false;
        }
        return $this->commit();
    }

    public function fetch(string $sql, array $variables = []) : object|null {
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

class db {
    private static database $database;

    private function __construct() {}
    private function __clone() {}

    public static function get() : database {
        return self::$database ?? new database;
    }
}
