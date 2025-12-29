<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    echo "<h2>❌ NOT LOGGED IN AS ADMIN</h2>";
    echo "<p>You need to <a href='login.php'>login as admin</a> first.</p>";
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Session Diagnostic</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }

        h1 {
            color: #8B0000;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .info {
            background: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 10px 0;
        }

        .session-data {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #8B0000;
            color: white;
        }

        .test-link {
            display: inline-block;
            margin: 5px;
            padding: 10px 15px;
            background: #8B0000;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .test-link:hover {
            background: #660000;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>✅ Admin Session Diagnostic</h1>

        <div class="info">
            <strong>✅ You are logged in as admin!</strong>
        </div>

        <h2>Session Data:</h2>
        <table>
            <tr>
                <th>Session Variable</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>admin_id</td>
                <td><?php echo htmlspecialchars($_SESSION['admin_id'] ?? 'NOT SET'); ?></td>
                <td class="success"><?php echo isset($_SESSION['admin_id']) ? '✅ SET' : '❌ MISSING'; ?></td>
            </tr>
            <tr>
                <td>admin_email</td>
                <td><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'NOT SET'); ?></td>
                <td class="success"><?php echo isset($_SESSION['admin_email']) ? '✅ SET' : '❌ MISSING'; ?></td>
            </tr>
            <tr>
                <td>admin_name</td>
                <td><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'NOT SET'); ?></td>
                <td class="success"><?php echo isset($_SESSION['admin_name']) ? '✅ SET' : '❌ MISSING'; ?></td>
            </tr>
            <tr>
                <td>admin_role</td>
                <td><?php echo htmlspecialchars($_SESSION['admin_role'] ?? 'NOT SET'); ?></td>
                <td class="success"><?php echo isset($_SESSION['admin_role']) ? '✅ SET' : '❌ MISSING'; ?></td>
            </tr>
        </table>

        <h2>Full Session Data:</h2>
        <div class="session-data">
            <pre><?php print_r($_SESSION); ?></pre>
        </div>

        <h2>Test Admin Pages:</h2>
        <p>Click these links to test if you can access admin pages:</p>
        <a href="index.php" class="test-link">Dashboard</a>
        <a href="packages.php" class="test-link">Packages</a>
        <a href="users.php" class="test-link">Users</a>
        <a href="streaming-users.php" class="test-link">Streaming Users</a>
        <a href="migrate.php" class="test-link">Database Migration</a>

        <h2>PHP Info:</h2>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>PHP Version</td>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <td>Session ID</td>
                <td><?php echo session_id(); ?></td>
            </tr>
            <tr>
                <td>Session Name</td>
                <td><?php echo session_name(); ?></td>
            </tr>
            <tr>
                <td>OPcache Enabled</td>
                <td><?php echo function_exists('opcache_get_status') && opcache_get_status() ? 'YES' : 'NO'; ?></td>
            </tr>
        </table>

        <p><a href="clear-cache.php" class="test-link">Clear Server Cache</a></p>
        <p><a href="index.php">← Back to Dashboard</a></p>
    </div>
</body>

</html>