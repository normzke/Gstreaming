<?php
/**
 * Database Connection and Query Builder
 * 
 * A PDO database wrapper with query building, prepared statements, and transaction support
 */

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;
    private $transactionLevel = 0;
    private $queryLog = [];
    private $lastQuery;
    private $lastStatement = null;
    private static $instance = null;

    // Public instantiation (legacy support)
    public function __construct()
    {
        $this->host = defined('DB_HOST') ? DB_HOST : '';
        $this->db_name = defined('DB_NAME') ? DB_NAME : '';
        $this->username = defined('DB_USER') ? DB_USER : '';
        $this->password = defined('DB_PASSWORD') ? DB_PASSWORD : '';
        $this->port = defined('DB_PORT') && DB_PORT ? DB_PORT : '5432';
    }

    // Singleton pattern
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Prevent cloning
    private function __clone()
    {
    }

    // Prevent unserialization
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Get database connection
     * @return PDO PDO database connection
     */
    public function getConnection()
    {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn;
    }

    /**
     * Establish database connection
     * @throws PDOException If connection fails
     */
    private function connect()
    {
        try {
            // Prefer configured socket path; fallback to /tmp; then TCP non-SSL
            if (strpos($this->host, '/') === 0) {
                $dsn = "pgsql:host=" . $this->host . ";dbname=" . $this->db_name;
            } else if (trim($this->host) === '') {
                $dsn = "pgsql:host=/tmp;dbname=" . $this->db_name;
            } else {
                $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";sslmode=disable";
            }

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_STATEMENT_CLASS => ['PDOStatementEx', []]
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

            // Set timezone to UTC
            $this->conn->exec("SET timezone = 'UTC'");

            // Set application name for logging
            $appName = defined('APP_NAME') ? APP_NAME : 'Bingetv';
            $this->conn->exec("SET application_name = '" . $appName . "'");

        } catch (PDOException $e) {
            // Log the error
            error_log('Database connection failed: ' . $e->getMessage());

            // Rethrow with a more user-friendly message in production
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
                throw new PDOException('Could not connect to the database. Please try again later.');
            } else {
                throw $e;
            }
        }
    }

    /**
     * Execute a query with parameters
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @return PDOStatement The executed statement
     */
    public function query($sql, $params = [])
    {
        $this->lastQuery = ['sql' => $sql, 'params' => $params];

        try {
            $stmt = $this->getConnection()->prepare($sql);
            $this->bindValues($stmt, $params);
            $stmt->execute();
            $this->lastStatement = $stmt;
            return $stmt;
        } catch (PDOException $e) {
            $this->handleException($e, $sql, $params);
            throw $e; // Re-throw for error handling up the stack
        }
    }

    /**
     * Get a single row
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|false The row as an associative array, or false if no rows
     */
    public function getRow($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Get multiple rows
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array Array of rows
     */
    public function getRows($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get a single column value
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @param int $columnNumber 0-indexed column number
     * @return mixed The value of the column
     */
    public function getValue($sql, $params = [], $columnNumber = 0)
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn($columnNumber);
    }

    /**
     * Get a single column as an array
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @param int $columnNumber 0-indexed column number
     * @return array Array of column values
     */
    public function getColumn($sql, $params = [], $columnNumber = 0)
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, $columnNumber);
    }

    /**
     * Insert a row into a table
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return string The last insert ID
     */
    public function insert($table, $data)
    {
        $columns = array_keys($data);
        $placeholders = array_map(function ($col) {
            return ':' . $col;
        }, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s) RETURNING id',
            $this->quoteIdentifier($table),
            implode(', ', array_map([$this, 'quoteIdentifier'], $columns)),
            implode(', ', $placeholders)
        );

        $stmt = $this->query($sql, $data);
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Update rows in a table
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where WHERE clause (without the WHERE keyword)
     * @param array $params Additional parameters for the WHERE clause
     * @return int Number of affected rows
     */
    public function update($table, $data, $where, $params = [])
    {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = $this->quoteIdentifier($column) . ' = :' . $column;
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->quoteIdentifier($table),
            implode(', ', $set),
            $where
        );

        $stmt = $this->query($sql, array_merge($data, $params));
        return $stmt->rowCount();
    }

    /**
     * Delete rows from a table
     * @param string $table Table name
     * @param string $where WHERE clause (without the WHERE keyword)
     * @param array $params Parameters for the WHERE clause
     * @return int Number of affected rows
     */
    public function delete($table, $where, $params = [])
    {
        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $this->quoteIdentifier($table),
            $where
        );

        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Begin a transaction
     * @return bool True on success
     */
    public function beginTransaction()
    {
        if ($this->transactionLevel === 0) {
            $this->getConnection()->beginTransaction();
        } else {
            $this->getConnection()->exec('SAVEPOINT LEVEL' . $this->transactionLevel);
        }
        $this->transactionLevel++;
        return true;
    }

    /**
     * Commit a transaction
     * @return bool True on success
     */
    public function commit()
    {
        if ($this->transactionLevel === 0) {
            throw new PDOException('No active transaction to commit');
        }

        $this->transactionLevel--;

        if ($this->transactionLevel === 0) {
            return $this->getConnection()->commit();
        }

        return true;
    }

    /**
     * Roll back a transaction
     * @return bool True on success
     */
    public function rollBack()
    {
        if ($this->transactionLevel === 0) {
            throw new PDOException('No active transaction to roll back');
        }

        $this->transactionLevel--;

        if ($this->transactionLevel === 0) {
            return $this->getConnection()->rollBack();
        }

        $this->getConnection()->exec('ROLLBACK TO SAVEPOINT LEVEL' . $this->transactionLevel);
        return true;
    }

    /**
     * Check if inside a transaction
     * @return bool True if in a transaction, false otherwise
     */
    public function inTransaction()
    {
        return $this->transactionLevel > 0;
    }

    /**
     * Quote a string for use in a query
     * @param string $value The string to quote
     * @return string The quoted string
     */
    public function quote($value)
    {
        if ($value === null) {
            return 'NULL';
        }
        return $this->getConnection()->quote($value);
    }

    /**
     * Quote an identifier (table name, column name, etc.)
     * @param string $identifier The identifier to quote
     * @return string The quoted identifier
     */
    public function quoteIdentifier($identifier)
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    /**
     * Get the last insert ID
     * @param string $name Sequence name (optional)
     * @return string The last insert ID
     */
    public function lastInsertId($name = null)
    {
        return $this->getConnection()->lastInsertId($name);
    }

    /**
     * Get the number of rows affected by the last SQL statement
     * @return int Number of affected rows
     */
    public function rowCount()
    {
        return $this->lastStatement ? $this->lastStatement->rowCount() : 0;
    }

    /**
     * Get the last executed SQL query
     * @return string The last SQL query
     */
    public function lastQuery()
    {
        return is_array($this->lastQuery) ? ($this->lastQuery['sql'] ?? '') : (string) $this->lastQuery;
    }

    /**
     * Get the query log
     * @return array Array of executed queries
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * Clear the query log
     */
    public function clearQueryLog()
    {
        $this->queryLog = [];
    }

    /**
     * Bind values to a prepared statement
     * @param PDOStatement $stmt The prepared statement
     * @param array $params Parameters to bind
     */
    private function bindValues($stmt, $params)
    {
        foreach ($params as $key => $value) {
            $param = is_int($key) ? $key + 1 : ':' . ltrim($key, ':');
            $type = $this->getPdoType($value);
            $stmt->bindValue($param, $value, $type);
        }
    }

    /**
     * Get the PDO parameter type for a value
     * @param mixed $value The value
     * @return int The PDO parameter type
     */
    private function getPdoType($value)
    {
        if (is_int($value) || is_bool($value)) {
            return PDO::PARAM_INT;
        } elseif (is_null($value)) {
            return PDO::PARAM_NULL;
        } elseif (is_resource($value)) {
            return PDO::PARAM_LOB;
        } else {
            return PDO::PARAM_STR;
        }
    }

    /**
     * Handle database exceptions
     * @param PDOException $e The exception
     * @param string $sql The SQL query that caused the exception
     * @param array $params The query parameters
     * @throws PDOException The re-thrown exception
     */
    private function handleException($e, $sql, $params = [])
    {
        $message = 'Database Error: ' . $e->getMessage() . "\n";
        $message .= 'Query: ' . $sql . "\n";
        $message .= 'Params: ' . print_r($params, true) . "\n";

        // Log the error
        error_log($message);

        // In development, show detailed error
        if (defined('ENVIRONMENT') && ENVIRONMENT !== 'production') {
            throw new PDOException($message, (int) $e->getCode(), $e);
        }

        // In production, log the error and show a generic message
        throw new PDOException('A database error occurred. Please try again later.');
    }
}

// Extended PDOStatement class for better debugging

class PDOStatementEx extends \PDOStatement
{
    protected $pdo;
    protected $params = [];

    protected function __construct($pdo = null)
    {
        $this->pdo = $pdo;
    }

    #[\ReturnTypeWillChange]

    public function bindValue($parameter, $value, $data_type = \PDO::PARAM_STR)
    {
        $this->params[$parameter] = $value;
        return parent::bindValue($parameter, $value, $data_type);
    }

    #[\ReturnTypeWillChange]

    public function execute($params = null)
    {
        if ($params !== null) {
            $this->params = $params;
        }

        try {
            return parent::execute($params);
        } catch (\PDOException $e) {
            $message = 'SQL Error: ' . $e->getMessage() . "\n";
            $message .= 'Query: ' . $this->queryString . "\n";
            $message .= 'Params: ' . print_r($this->params, true) . "\n";
            throw new \PDOException($message, (int) $e->getCode(), $e);
        }
    }
}

// Global function to get database instance
function db()
{
    return Database::getInstance();
}
