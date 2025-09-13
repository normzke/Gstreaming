<?php
/**
 * Database Migration Runner
 * Executes database migrations in order
 */

require_once '../config/config.php';
require_once '../config/database.php';

class MigrationRunner {
    private $db;
    private $conn;
    private $migrationPath;
    
    public function __construct() {
        try {
            $this->db = new Database();
            $this->conn = $this->db->getConnection();
            $this->migrationPath = __DIR__ . '/migrations/';
            
            echo "=== Database Migration Runner ===\n";
            echo "Starting migrations...\n\n";
            
        } catch (Exception $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    public function runMigrations() {
        $this->createMigrationsTable();
        
        $migrations = $this->getPendingMigrations();
        
        if (empty($migrations)) {
            echo "✅ All migrations are up to date.\n";
            return;
        }
        
        foreach ($migrations as $migration) {
            $this->runMigration($migration);
        }
        
        echo "\n🎉 All migrations completed successfully!\n";
    }
    
    private function createMigrationsTable() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id SERIAL PRIMARY KEY,
                migration VARCHAR(255) UNIQUE NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $this->conn->exec($sql);
            echo "✅ Migrations table ready\n";
            
        } catch (Exception $e) {
            echo "❌ Failed to create migrations table: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    private function getPendingMigrations() {
        $migrationFiles = glob($this->migrationPath . '*.sql');
        sort($migrationFiles);
        
        $executedMigrations = [];
        try {
            $stmt = $this->conn->query("SELECT migration FROM migrations");
            $executedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            echo "⚠️  Could not fetch executed migrations: " . $e->getMessage() . "\n";
        }
        
        $pendingMigrations = [];
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.sql');
            if (!in_array($migrationName, $executedMigrations)) {
                $pendingMigrations[] = $file;
            }
        }
        
        return $pendingMigrations;
    }
    
    private function runMigration($migrationFile) {
        $migrationName = basename($migrationFile, '.sql');
        
        echo "\n--- Running Migration: $migrationName ---\n";
        
        try {
            $sql = file_get_contents($migrationFile);
            
            if ($sql === false) {
                throw new Exception("Could not read migration file: $migrationFile");
            }
            
            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            $this->conn->beginTransaction();
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $this->conn->exec($statement);
                }
            }
            
            // Record migration as executed
            $stmt = $this->conn->prepare("INSERT INTO migrations (migration) VALUES (?)");
            $stmt->execute([$migrationName]);
            
            $this->conn->commit();
            
            echo "✅ Migration $migrationName completed successfully\n";
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            echo "❌ Migration $migrationName failed: " . $e->getMessage() . "\n";
            echo "Rolling back transaction...\n";
            exit(1);
        }
    }
    
    public function rollbackMigration($migrationName) {
        echo "\n--- Rolling back Migration: $migrationName ---\n";
        
        try {
            // Check if migration exists
            $stmt = $this->conn->prepare("SELECT migration FROM migrations WHERE migration = ?");
            $stmt->execute([$migrationName]);
            
            if (!$stmt->fetch()) {
                echo "⚠️  Migration $migrationName not found in executed migrations\n";
                return;
            }
            
            // For this simple implementation, we'll just remove the record
            // In a production system, you'd want to implement proper rollback SQL
            $stmt = $this->conn->prepare("DELETE FROM migrations WHERE migration = ?");
            $stmt->execute([$migrationName]);
            
            echo "✅ Migration $migrationName rolled back\n";
            
        } catch (Exception $e) {
            echo "❌ Rollback failed: " . $e->getMessage() . "\n";
        }
    }
    
    public function showStatus() {
        echo "\n=== Migration Status ===\n";
        
        try {
            $stmt = $this->conn->query("SELECT migration, executed_at FROM migrations ORDER BY executed_at");
            $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($migrations)) {
                echo "No migrations have been executed.\n";
            } else {
                foreach ($migrations as $migration) {
                    echo "✅ {$migration['migration']} - {$migration['executed_at']}\n";
                }
            }
            
            $pending = $this->getPendingMigrations();
            if (!empty($pending)) {
                echo "\nPending migrations:\n";
                foreach ($pending as $migration) {
                    echo "⏳ " . basename($migration, '.sql') . "\n";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Could not fetch migration status: " . $e->getMessage() . "\n";
        }
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $command = $argv[1] ?? 'run';
    
    $runner = new MigrationRunner();
    
    switch ($command) {
        case 'run':
            $runner->runMigrations();
            break;
            
        case 'status':
            $runner->showStatus();
            break;
            
        case 'rollback':
            $migrationName = $argv[2] ?? null;
            if ($migrationName) {
                $runner->rollbackMigration($migrationName);
            } else {
                echo "Usage: php run-migrations.php rollback <migration_name>\n";
            }
            break;
            
        default:
            echo "Usage: php run-migrations.php [run|status|rollback]\n";
            echo "  run     - Execute pending migrations\n";
            echo "  status  - Show migration status\n";
            echo "  rollback <name> - Rollback a migration\n";
            break;
    }
} else {
    echo "This migration runner should be executed from the command line.\n";
    echo "Usage: php database/run-migrations.php [run|status|rollback]\n";
}
?>
