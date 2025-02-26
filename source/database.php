<?php

namespace source;

use PDO;
use PDOStatement;
use SensitiveParameter;

enum ResponseMode : int {
    case Array = PDO::FETCH_ASSOC;
    case Object = PDO::FETCH_OBJ;
    case Model = PDO::FETCH_CLASS;
};

class database {
    private PDO $connection;
    private(set) int $rows_affected = 0;
    private(set) int $last_inserted_id = 0;
    
    public function __construct(string $type, string $host, int $port, string $dbname, string $charset, string $username, #[SensitiveParameter] string $password) {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->connection = PDO::connect("$type:host=$host;port=$port;dbname=$dbname;charset=$charset", $username, $password, $options);
        } catch (DatabaseException) {}
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

    private function statement(string $sql, array $variables = []) : PDOStatement {
        $statement = $this->connection->prepare($sql);
        foreach ($variables as $key => $value) {
            if (!$statement->bindValue(":$key", $value, $this->find_param_type($value))) {
                LOG_WARNING("Failed to bind $key with $value");
            }
        }
        return $statement;
    }

    public function transaction() : bool {
        try {
            return $this->connection->beginTransaction();
        } catch (DatabaseException $e) {}
        return false;
    }

    public function commit() : bool {
        try {
            $this->last_inserted_id = $this->connection->lastInsertId();
            return $this->connection->commit();
        } catch (DatabaseException $e) {}
        return false;
    }

    public function rollback() : bool {
        try {
            return $this->connection->rollBack();
        } catch (DatabaseException $e) {}
        return false;
    }

    public function execute(string $sql, array $variables = []) : bool {
        try {
            $statement = $this->statement($sql, $variables);
            $executed = $statement->execute();
            $this->rows_affected = $statement->rowCount();
            $this->last_inserted_id = $this->connection->lastInsertId();
            return $executed;
        } catch (DatabaseException $e) {}
        return false;
    }

    public function execute_multiple(array $sql_queries, array $variables = []) : bool {
        $this->transaction();
        $sql = implode(';', $sql_queries);
        if (!$this->statement($sql, $variables)) {
            $this->rollback();
            return false;
        }
        return $this->commit();
    }

    public function fetch(string $sql, array $variables = [], ResponseMode $mode = ResponseMode::Object, string|null $model = null) : array|object|null {
        try {
            $statement = $this->statement($sql, $variables);
            
            match ($mode) {
                ResponseMode::Model => $statement->setFetchMode($mode->value, $model),
                default => $statement->setFetchMode($mode->value)
            };

            if ($statement->execute() && $response = $statement->fetch()) {
                return $response;
            }
        } catch (DatabaseException $e) {}
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
