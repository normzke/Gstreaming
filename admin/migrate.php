<?php
/**
 * BingeTV Database Migration Runner
 * Web-based script to run database migrations
 * Access: /admin/migrate.php
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Migration status
$migrations = [];
$errors = [];
$success = [];

// Check if migrations table exists
try {
    $conn->query("SELECT 1 FROM migrations LIMIT 1");
} catch (Exception $e) {
    // Create migrations table
    $createMigrationsTable = "
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
            error_message TEXT NULL
        )
    ";
    $conn->exec($createMigrationsTable);
}

// Get list of migration files
$migrationFiles = glob(__DIR__ . '/../database/*.sql');
sort($migrationFiles);

// Process migrations if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migrations'])) {
    foreach ($migrationFiles as $file) {
        $migrationName = basename($file);

        // Check if already executed
        $stmt = $conn->prepare("SELECT * FROM migrations WHERE migration_name = ?");
        $stmt->execute([$migrationName]);
        $existing = $stmt->fetch();

        if ($existing && $existing['status'] === 'success') {
            $migrations[$migrationName] = ['status' => 'skipped', 'message' => 'Already executed'];
            continue;
        }

        try {
            // Read SQL file
            $sql = file_get_contents($file);

            // Split into individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function ($stmt) {
                    return !empty($stmt) && !preg_match('/^--/', $stmt);
                }
            );

            // Execute each statement
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $conn->exec($statement);
                }
            }

            // Record success
            $stmt = $conn->prepare("
                INSERT INTO migrations (migration_name, status) 
                VALUES (?, 'success')
                ON DUPLICATE KEY UPDATE status = 'success', executed_at = NOW()
            ");
            $stmt->execute([$migrationName]);

            $migrations[$migrationName] = ['status' => 'success', 'message' => 'Executed successfully'];
            $success[] = $migrationName;

        } catch (Exception $e) {
            // Record failure
            $stmt = $conn->prepare("
                INSERT INTO migrations (migration_name, status, error_message) 
                VALUES (?, 'failed', ?)
                ON DUPLICATE KEY UPDATE status = 'failed', error_message = ?, executed_at = NOW()
            ");
            $stmt->execute([$migrationName, $e->getMessage(), $e->getMessage()]);

            $migrations[$migrationName] = ['status' => 'failed', 'message' => $e->getMessage()];
            $errors[] = $migrationName . ': ' . $e->getMessage();
        }
    }
}

// Get migration status
$executedMigrations = [];
$stmt = $conn->query("SELECT * FROM migrations ORDER BY executed_at DESC");
while ($row = $stmt->fetch()) {
    $executedMigrations[$row['migration_name']] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migrations - BingeTV Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0f1e 0%, #1a1a2e 100%);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 2px solid #00A8FF;
            box-shadow: 0 10px 30px rgba(0, 168, 255, 0.2);
        }

        .header h1 {
            color: #00A8FF;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid rgba(0, 255, 0, 0.3);
            color: #0f0;
        }

        .alert-error {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid rgba(255, 0, 0, 0.3);
            color: #f00;
        }

        .alert-warning {
            background: rgba(255, 170, 0, 0.1);
            border: 2px solid rgba(255, 170, 0, 0.3);
            color: #ffaa00;
        }

        .card {
            background: rgba(0, 168, 255, 0.05);
            border: 2px solid rgba(0, 168, 255, 0.2);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #00A8FF;
            color: white;
        }

        .btn-primary:hover {
            background: #0099E6;
            transform: scale(1.05);
        }

        .btn-secondary {
            background: #666;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 168, 255, 0.1);
        }

        th {
            background: rgba(0, 168, 255, 0.2);
            color: #00A8FF;
            font-weight: 600;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-success {
            background: rgba(0, 255, 0, 0.2);
            color: #0f0;
            border: 1px solid #0f0;
        }

        .badge-failed {
            background: rgba(255, 0, 0, 0.2);
            color: #f00;
            border: 1px solid #f00;
        }

        .badge-pending {
            background: rgba(255, 170, 0, 0.2);
            color: #ffaa00;
            border: 1px solid #ffaa00;
        }

        .badge-skipped {
            background: rgba(128, 128, 128, 0.2);
            color: #888;
            border: 1px solid #888;
        }

        code {
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            color: #0f0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-database"></i> Database Migrations</h1>
            <p>Manage and execute database schema changes</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Successfully executed <?php echo count($success); ?> migration(s)
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Migration errors:</strong>
                    <ul style="margin-top: 10px; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2 style="color: #00A8FF; margin-bottom: 20px;">Available Migrations</h2>

            <?php if (empty($migrationFiles)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    No migration files found in <code>/database/</code> directory
                </div>
            <?php else: ?>

                <table>
                    <thead>
                        <tr>
                            <th>Migration File</th>
                            <th>Status</th>
                            <th>Executed At</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($migrationFiles as $file): ?>
                            <?php
                            $name = basename($file);
                            $executed = $executedMigrations[$name] ?? null;
                            $status = $migrations[$name] ?? null;
                            ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($name); ?></code></td>
                                <td>
                                    <?php if ($status): ?>
                                        <span class="badge badge-<?php echo $status['status']; ?>">
                                            <?php echo strtoupper($status['status']); ?>
                                        </span>
                                    <?php elseif ($executed): ?>
                                        <span class="badge badge-<?php echo $executed['status']; ?>">
                                            <?php echo strtoupper($executed['status']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">PENDING</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($executed) {
                                        echo date('Y-m-d H:i:s', strtotime($executed['executed_at']));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($status) {
                                        echo htmlspecialchars($status['message']);
                                    } elseif ($executed && $executed['error_message']) {
                                        echo htmlspecialchars($executed['error_message']);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <form method="POST" style="margin-top: 30px;">
                    <button type="submit" name="run_migrations" class="btn btn-primary"
                        onclick="return confirm('Are you sure you want to run all pending migrations?')">
                        <i class="fas fa-play"></i>
                        Run Pending Migrations
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 style="color: #00A8FF; margin-bottom: 15px;">
                <i class="fas fa-info-circle"></i> Migration Instructions
            </h3>
            <ol style="line-height: 2; color: #ccc;">
                <li>Place SQL migration files in <code>/database/</code> directory</li>
                <li>Name files descriptively (e.g., <code>tivimate_migration.sql</code>)</li>
                <li>Click "Run Pending Migrations" to execute</li>
                <li>Migrations are tracked and won't run twice</li>
                <li>Check status and error messages in the table above</li>
            </ol>
        </div>
    </div>
</body>

</html>