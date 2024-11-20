<?php

namespace source;

use PDO;
use PDOException;
use PDOStatement;
use SensitiveParameter;

class database {
    private PDO $connection;
    private int $rows_affected = 0;
    private int $last_inserted_id = 0;
    
    // FULL //
    // public private(set) int $rows_affected = 0;
    // public private(set) int $last_inserted_id = 0;
    // SHORTHAND //
    // private(set) int $rows_affected = 0;
    // private(set) int $last_inserted_id = 0;
    
    public function __construct(string $type, string $host, int $port, string $dbname, string $charset, string $username, #[SensitiveParameter] string $password) {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->connection = new PDO("$type:host=$host;port=$port;dbname=$dbname;charset=$charset", $username, $password, $options);
        } catch (DatabaseException) {}
    }

    public function last_inserted_id() : int {
        return $this->last_inserted_id;
    }

    public function rows_affected() : int {
        return $this->rows_affected;
    }

    private function find_param_type(mixed $value) : int {
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

class mysql_db {
    private static database $database;

    private function __construct() {}
    private function __clone() {}

    public static function get() : database {
        return self::$database ?? new database(
            'mysql',
            env('DB_HOST'),
            env('DB_PORT'),
            env('DB_DATABASE'),
            env('DB_CHARSET'),
            env('DB_USERNAME'),
            env('DB_PASSWORD')
        );
    }
}
