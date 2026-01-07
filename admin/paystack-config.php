<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Initialize message variables
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_paystack_config':
            $public_key = $_POST['public_key'] ?? '';
            $secret_key = $_POST['secret_key'] ?? '';
            $webhook_secret = $_POST['webhook_secret'] ?? '';
            $environment = $_POST['environment'] ?? 'test';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            // Validate required fields
            if (empty($public_key) || empty($secret_key)) {
                $message = 'Please fill in all required fields';
                $messageType = 'error';
            } else {
                // Update Paystack configuration using key-value structure
                $configs = [
                    'public_key' => $public_key,
                    'secret_key' => $secret_key,
                    'webhook_secret' => $webhook_secret,
                    'environment' => $environment,
                    'is_active' => $is_active
                ];

                $success = true;
                foreach ($configs as $key => $value) {
                    $updateQuery = "INSERT INTO paystack_config (config_key, config_value) VALUES (?, ?) 
                                   ON CONFLICT (config_key) DO UPDATE SET config_value = EXCLUDED.config_value, updated_at = NOW()";
                    $updateStmt = $conn->prepare($updateQuery);
                    if (!$updateStmt->execute([$key, $value])) {
                        $success = false;
                        break;
                    }
                }

                if ($success) {
                    $message = 'Paystack configuration updated successfully';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating Paystack configuration';
                    $messageType = 'error';
                }
            }
            break;

        case 'test_paystack_connection':
            // Test Paystack API connection
            $testQuery = "SELECT config_value FROM paystack_config WHERE config_key = 'secret_key'";
            $testStmt = $conn->prepare($testQuery);
            $testStmt->execute();
            $secret_key = $testStmt->fetchColumn();

            if ($secret_key) {
                // Test API connection by verifying the secret key format
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/verify/test_reference');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $secret_key,
                    'Content-Type: application/json'
                ]);

                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // 401 means unauthorized (invalid key), 404 means valid key but reference not found (which is expected)
                if ($http_code === 404 || $http_code === 200) {
                    $message = 'Paystack API connection test successful - API key is valid';
                    $messageType = 'success';
                } else if ($http_code === 401) {
                    $message = 'Paystack API connection test failed - Invalid API key';
                    $messageType = 'error';
                } else {
                    $message = 'Paystack API connection test failed (HTTP ' . $http_code . ')';
                    $messageType = 'error';
                }
            } else {
                $message = 'No Paystack configuration found';
                $messageType = 'error';
            }
            break;
    }
}

// Get current Paystack configuration
$paystack_config = [];
try {
    $configQuery = "SELECT config_key, config_value FROM paystack_config";
    $configStmt = $conn->prepare($configQuery);
    $configStmt->execute();
    $configs = $configStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $paystack_config = $configs;
} catch (PDOException $e) {
    // Table doesn't exist yet, use default values
    $paystack_config = [];
}

// Get Paystack transaction statistics
$paystack_stats = [
    'total_transactions' => 0,
    'successful_transactions' => 0,
    'failed_transactions' => 0,
    'total_amount' => 0,
    'today_transactions' => 0
];

try {
    $statsQuery = "SELECT 
                   COUNT(*) as total_transactions,
                   COUNT(CASE WHEN status = 'success' THEN 1 END) as successful_transactions,
                   COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_transactions,
                   COALESCE(SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END), 0) as total_amount,
                   COUNT(CASE WHEN created_at >= NOW() - INTERVAL '24 hours' THEN 1 END) as today_transactions
                   FROM paystack_transactions";
    $statsStmt = $conn->prepare($statsQuery);
    $statsStmt->execute();
    $paystack_stats = $statsStmt->fetch();
} catch (PDOException $e) {
    // Table doesn't exist yet, use default values
    $paystack_stats = [
        'total_transactions' => 0,
        'successful_transactions' => 0,
        'failed_transactions' => 0,
        'total_amount' => 0,
        'today_transactions' => 0
    ];
}

$page_title = 'Paystack Config';
include 'includes/header.php';
?>

<!-- Messages -->
<?php if ($message): ?>
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <div class="alert alert-<?php echo $messageType; ?>"
                style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border-radius: var(--admin-radius); background: <?php echo $messageType === 'success' ? '#D1FAE5' : '#FEE2E2'; ?>; color: <?php echo $messageType === 'success' ? '#065F46' : '#991B1B'; ?>;">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Paystack Statistics -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Paystack Transaction Statistics</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="stat-item">
                <h4>
                    <?php echo number_format($paystack_stats['total_transactions']); ?>
                </h4>
                <p>Total Transactions</p>
            </div>
            <div class="stat-item">
                <h4>
                    <?php echo number_format($paystack_stats['successful_transactions']); ?>
                </h4>
                <p>Successful</p>
            </div>
            <div class="stat-item">
                <h4>
                    <?php echo number_format($paystack_stats['failed_transactions']); ?>
                </h4>
                <p>Failed</p>
            </div>
            <div class="stat-item">
                <h4>KSh
                    <?php echo number_format($paystack_stats['total_amount'], 2); ?>
                </h4>
                <p>Total Amount</p>
            </div>
            <div class="stat-item">
                <h4>
                    <?php echo number_format($paystack_stats['today_transactions']); ?>
                </h4>
                <p>Today's Transactions</p>
            </div>
        </div>
    </div>
</div>

<!-- Paystack Configuration Form -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">Paystack Gateway Configuration</h3>
        <div class="card-actions">
            <button class="btn btn-secondary" onclick="testConnection()">
                <i class="fas fa-plug"></i>
                Test Connection
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" id="paystackConfigForm">
            <input type="hidden" name="action" value="update_paystack_config">

            <div class="form-section">
                <h4 class="form-section-title">API Credentials</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="public_key">Public Key *</label>
                        <input type="text" id="public_key" name="public_key"
                            value="<?php echo htmlspecialchars($paystack_config['public_key'] ?? ''); ?>" required>
                        <small class="form-help">Your Paystack public key (starts with pk_)</small>
                    </div>
                    <div class="form-group">
                        <label for="secret_key">Secret Key *</label>
                        <input type="password" id="secret_key" name="secret_key"
                            value="<?php echo htmlspecialchars($paystack_config['secret_key'] ?? ''); ?>" required>
                        <small class="form-help">Your Paystack secret key (starts with sk_)</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="webhook_secret">Webhook Secret</label>
                        <input type="password" id="webhook_secret" name="webhook_secret"
                            value="<?php echo htmlspecialchars($paystack_config['webhook_secret'] ?? ''); ?>">
                        <small class="form-help">Webhook secret for verifying payment notifications</small>
                    </div>
                    <div class="form-group">
                        <label for="environment">Environment</label>
                        <select id="environment" name="environment">
                            <option value="test" <?php echo ($paystack_config['environment'] ?? 'test') === 'test' ? 'selected' : ''; ?>>Test</option>
                            <option value="live" <?php echo ($paystack_config['environment'] ?? '') === 'live' ? 'selected' : ''; ?>>Live</option>
                        </select>
                        <small class="form-help">Choose between test or live mode</small>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4 class="form-section-title">Settings</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" value="1" <?php echo ($paystack_config['is_active'] ?? 0) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Enable Paystack Payments
                        </label>
                        <small class="form-help">Enable or disable Paystack payment processing</small>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Recent Paystack Transactions -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">Recent Paystack Transactions</h3>
        <a href="payments?method=paystack" class="btn btn-secondary">View All</a>
    </div>
    <div class="card-body">
        <?php
        try {
            $recentQuery = "SELECT * FROM paystack_transactions ORDER BY created_at DESC LIMIT 10";
            $recentStmt = $conn->prepare($recentQuery);
            $recentStmt->execute();
            $recent_transactions = $recentStmt->fetchAll();
        } catch (PDOException $e) {
            $recent_transactions = [];
        }
        ?>

        <?php if (empty($recent_transactions)): ?>
            <div class="empty-state">
                <i class="fas fa-credit-card"></i>
                <h3>No transactions yet</h3>
                <p>Paystack transactions will appear here once payments are processed.</p>
            </div>
        <?php else: ?>
            <div class="transactions-list">
                <?php foreach ($recent_transactions as $transaction): ?>
                    <div class="transaction-item">
                        <div class="transaction-info">
                            <div class="transaction-details">
                                <h4>
                                    <?php echo htmlspecialchars($transaction['email']); ?>
                                </h4>
                                <p>Amount: KSh
                                    <?php echo number_format($transaction['amount'], 2); ?>
                                </p>
                                <small>Ref:
                                    <?php echo htmlspecialchars($transaction['reference']); ?> |
                                    <?php echo date('M j, Y H:i', strtotime($transaction['created_at'])); ?>
                                </small>
                            </div>
                            <div class="transaction-status">
                                <span class="status-badge status-<?php echo $transaction['status']; ?>">
                                    <?php echo ucfirst($transaction['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--admin-border);
    }

    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
    }

    .form-section-title {
        margin: 0 0 1rem 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--admin-text);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--admin-text);
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--admin-border);
        border-radius: var(--admin-radius);
        font-size: 1rem;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
    }

    .form-help {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: var(--admin-text-light);
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        cursor: pointer;
        font-weight: 500;
        color: var(--admin-text);
    }

    .checkbox-label input[type="checkbox"] {
        width: auto;
        margin-right: 0.5rem;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--admin-border);
    }

    .stat-item {
        text-align: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: var(--admin-radius);
    }

    .stat-item h4 {
        margin: 0 0 0.5rem 0;
        font-size: 1.5rem;
        color: var(--admin-primary);
    }

    .stat-item p {
        margin: 0;
        color: var(--admin-text-light);
        font-size: 0.875rem;
    }

    .transactions-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .transaction-item {
        background: white;
        border: 1px solid var(--admin-border);
        border-radius: var(--admin-radius);
        padding: 1rem;
    }

    .transaction-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .transaction-details h4 {
        margin: 0 0 0.25rem 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--admin-text);
    }

    .transaction-details p {
        margin: 0 0 0.25rem 0;
        color: var(--admin-text);
        font-weight: 500;
    }

    .transaction-details small {
        color: var(--admin-text-light);
        font-size: 0.75rem;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-success {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-pending {
        background: #FEF3C7;
        color: #92400E;
    }

    .status-failed {
        background: #FEE2E2;
        color: #991B1B;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--admin-text-light);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: var(--admin-text-light);
    }

    .empty-state h3 {
        margin: 0 0 0.5rem 0;
        color: var(--admin-text);
    }

    .empty-state p {
        margin: 0;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }
    }
</style>

<script>
    function testConnection() {
        if (confirm('This will test the Paystack API connection. Continue?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="test_paystack_connection">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function resetForm() {
        if (confirm('Are you sure you want to reset the form? All changes will be lost.')) {
            document.getElementById('paystackConfigForm').reset();
        }
    }

    // Form validation
    document.getElementById('paystackConfigForm').addEventListener('submit', function (e) {
        const requiredFields = ['public_key', 'secret_key'];
        let isValid = true;

        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (!field.value.trim()) {
                field.style.borderColor = '#dc3545';
                isValid = false;
            } else {
                field.style.borderColor = '';
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>