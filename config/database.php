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
            $dsn = "pgsql:host=" . $this->host .
                   ";port=" . $this->port .
                   ";dbname=" . $this->db_name .
                   (defined('DB_SSLMODE') ? ";sslmode=" . DB_SSLMODE : '');
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            // Fallback: Try Unix domain socket (common on cPanel is /tmp)
            try {
                $socketHost = '/tmp';
                $dsnSocket = "pgsql:host=" . $socketHost . ";dbname=" . $this->db_name;
                $this->conn = new PDO($dsnSocket, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e2) {
                echo "Connection error: " . $exception->getMessage();
            }
        }

        return $this->conn;
    }
}
?>
