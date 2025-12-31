<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'Database Migrations';
include 'includes/header.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Migration status
$migrations = [];
$errors = [];
$success = [];

// Check if migrations table exists
try {
    $conn->query("SELECT 1 FROM migrations LIMIT 1");
} catch (Exception $e) {
    // Create migrations table using PostgreSQL syntax
    $createMigrationsTable = "
CREATE TABLE IF NOT EXISTS migrations (
id SERIAL PRIMARY KEY,
migration_name VARCHAR(255) NOT NULL UNIQUE,
executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
status VARCHAR(50) DEFAULT 'pending',
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

            // Record success using PostgreSQL syntax
            $stmt = $conn->prepare("
INSERT INTO migrations (migration_name, status)
VALUES (?, 'success')
ON CONFLICT (migration_name) DO UPDATE SET status = 'success', executed_at = NOW()
");
            $stmt->execute([$migrationName]);

            $migrations[$migrationName] = ['status' => 'success', 'message' => 'Executed successfully'];
            $success[] = $migrationName;

        } catch (Exception $e) {
            // Record failure using PostgreSQL syntax
            $stmt = $conn->prepare("
INSERT INTO migrations (migration_name, status, error_message)
VALUES (?, 'failed', ?)
ON CONFLICT (migration_name) DO UPDATE SET status = 'failed', error_message = ?, executed_at = NOW()
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

$page_title = 'Database Migrations';
include 'includes/header.php';
?>

<div class="header-actions mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="h4 mb-1">Database Migrations</h2>
        <p class="text-muted small mb-0">Manage and execute database schema changes</p>
    </div>
    <form method="POST">
        <button type="submit" name="run_migrations" class="btn btn-primary"
            onclick="return confirm('Are you sure you want to run all pending migrations?')">
            <i class="fas fa-play mr-2"></i> Run Pending Migrations
        </button>
    </form>
</div>

<?php if (is_array($success) && count($success) > 0): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>
        Successfully executed <?php echo count($success); ?> migration(s)
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <strong>Migration errors:</strong>
        <ul class="mb-0 mt-2 pl-3 small">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<div class="admin-card mb-4">
    <div class="card-header border-bottom">
        <h3 class="card-title h6 mb-0">Available Migrations</h3>
    </div>
    <div class="card-body p-0">
        <?php if (empty($migrationFiles)): ?>
            <div class="p-4 text-center text-muted">
                <i class="fas fa-info-circle mb-2 d-block fa-2x"></i>
                No migration files found in <code>/database/</code>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Migration File</th>
                            <th>Status</th>
                            <th class="text-right">Executed At</th>
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
                                <td><code class="text-primary"><?php echo htmlspecialchars($name); ?></code></td>
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
                                        <span class="badge badge-warning">PENDING</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right text-muted small">
                                    <?php echo $executed ? date('Y-m-d H:i:s', strtotime($executed['executed_at'])) : '-'; ?>
                                </td>
                                <td>
                                    <div class="small text-truncate" style="max-width: 300px;">
                                        <?php
                                        if ($status)
                                            echo htmlspecialchars($status['message']);
                                        elseif ($executed && $executed['error_message'])
                                            echo htmlspecialchars($executed['error_message']);
                                        else
                                            echo '<span class="text-muted">-</span>';
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="admin-card">
    <div class="card-header border-bottom">
        <h3 class="card-title h6 mb-0">
            <i class="fas fa-info-circle mr-2"></i>Instructions
        </h3>
    </div>
    <div class="card-body">
        <ul class="text-muted small mb-0 pl-3">
            <li class="mb-2">Place SQL migration files in the <code>/database/</code> directory.</li>
            <li class="mb-2">Ensure files use PostgreSQL compatible syntax (e.g. <code>SERIAL</code>, not
                <code>AUTO_INCREMENT</code>).
            </li>
            <li class="mb-2">Click <strong>Run Pending Migrations</strong> to apply new changes.</li>
            <li>Executed migrations are tracked and will not be re-run.</li>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
```