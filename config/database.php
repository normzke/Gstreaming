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

        $attemptErrors = [];

        // Build list of DSNs to try (production first tries sockets, then TCP variants)
        $ssl = defined('DB_SSLMODE') ? ";sslmode=" . DB_SSLMODE : '';
        $dsns = [];

        if (!defined('LOCALHOST_MODE') || LOCALHOST_MODE === false) {
            // Common socket locations on cPanel/WHM
            foreach (['/var/run/postgresql', '/tmp'] as $socketPath) {
                $dsns[] = "pgsql:host={$socketPath};dbname={$this->db_name}"; // socket DSN ignores port
            }
            // TCP with provided host
            $dsns[] = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}{$ssl}";
            // TCP localhost and 127.0.0.1 fallbacks
            $dsns[] = "pgsql:host=localhost;port={$this->port};dbname={$this->db_name}{$ssl}";
            // Force non-SSL if server rejects SSL
            $dsns[] = "pgsql:host=127.0.0.1;port={$this->port};dbname={$this->db_name};sslmode=disable";
        } else {
            // Local development
            $dsns[] = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}{$ssl}";
        }

        foreach ($dsns as $dsn) {
            try {
                $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                return $this->conn;
            } catch (PDOException $e) {
                $attemptErrors[] = $e->getMessage();
            }
        }

        // If we reach here, all attempts failed
        echo "Connection error: " . htmlspecialchars($attemptErrors[0] ?? 'Unknown error');

        return $this->conn;
    }
}
?>
