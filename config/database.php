<?php
/**
 * Database Configuration
 * PostgreSQL connection for cPanel hosting
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;

    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASSWORD;
        $this->port = DB_PORT ?? '5432';
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "pgsql:host=" . $this->host . 
                ";port=" . $this->port . 
                ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
