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
        case 'update_mpesa_config':
            $business_short_code = $_POST['business_short_code'] ?? '';
            $passkey = $_POST['passkey'] ?? '';
            $consumer_key = $_POST['consumer_key'] ?? '';
            $consumer_secret = $_POST['consumer_secret'] ?? '';
            $till_number = $_POST['till_number'] ?? '';
            $paybill_number = $_POST['paybill_number'] ?? '';
            $callback_url = $_POST['callback_url'] ?? '';
            $timeout_url = $_POST['timeout_url'] ?? '';
            $environment = $_POST['environment'] ?? 'sandbox';
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate required fields
            if (empty($business_short_code) || empty($passkey) || empty($consumer_key) || empty($consumer_secret)) {
                $message = 'Please fill in all required fields';
                $messageType = 'error';
            } else {
                // Update M-PESA configuration using key-value structure
                $configs = [
                    'business_short_code' => $business_short_code,
                    'passkey' => $passkey,
                    'consumer_key' => $consumer_key,
                    'consumer_secret' => $consumer_secret,
                    'till_number' => $till_number,
                    'paybill_number' => $paybill_number,
                    'callback_url' => $callback_url,
                    'timeout_url' => $timeout_url,
                    'environment' => $environment,
                    'is_active' => $is_active
                ];
                
                $success = true;
                foreach ($configs as $key => $value) {
                    $updateQuery = "INSERT INTO mpesa_config (config_key, config_value) VALUES (?, ?) 
                                   ON CONFLICT (config_key) DO UPDATE SET config_value = EXCLUDED.config_value, updated_at = NOW()";
                    $updateStmt = $conn->prepare($updateQuery);
                    if (!$updateStmt->execute([$key, $value])) {
                        $success = false;
                        break;
                    }
                }
                
                if ($success) {
                    $message = 'M-PESA configuration updated successfully';
                $messageType = 'success';
                } else {
                    $message = 'Error updating M-PESA configuration';
                    $messageType = 'error';
                }
            }
                break;
            
        case 'test_mpesa_connection':
            // Test M-PESA API connection
            $testQuery = "SELECT config_value FROM mpesa_config WHERE config_key = 'consumer_key'";
            $testStmt = $conn->prepare($testQuery);
            $testStmt->execute();
            $consumer_key = $testStmt->fetchColumn();
            
            $testQuery = "SELECT config_value FROM mpesa_config WHERE config_key = 'consumer_secret'";
            $testStmt = $conn->prepare($testQuery);
            $testStmt->execute();
            $consumer_secret = $testStmt->fetchColumn();
            
            $testQuery = "SELECT config_value FROM mpesa_config WHERE config_key = 'environment'";
            $testStmt = $conn->prepare($testQuery);
            $testStmt->execute();
            $environment = $testStmt->fetchColumn() ?: 'sandbox';
            
            if ($consumer_key && $consumer_secret) {
                // Test API connection (simplified test)
                $test_url = $environment === 'production' 
                    ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
                    : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $test_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Basic ' . base64_encode($consumer_key . ':' . $consumer_secret)
                ]);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($http_code === 200) {
                    $message = 'M-PESA API connection test successful';
                    $messageType = 'success';
                } else {
                    $message = 'M-PESA API connection test failed (HTTP ' . $http_code . ')';
                    $messageType = 'error';
                }
            } else {
                $message = 'No M-PESA configuration found';
                    $messageType = 'error';
                }
                break;
    }
}

// Get current M-PESA configuration
$mpesa_config = [];
try {
    $configQuery = "SELECT config_key, config_value FROM mpesa_config";
    $configStmt = $conn->prepare($configQuery);
    $configStmt->execute();
    $configs = $configStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $mpesa_config = $configs;
} catch (PDOException $e) {
    // Table doesn't exist yet, use default values
    $mpesa_config = [];
}

// Get M-PESA transaction statistics
$mpesa_stats = [
    'total_transactions' => 0,
    'successful_transactions' => 0,
    'failed_transactions' => 0,
    'total_amount' => 0,
    'today_transactions' => 0
];

try {
    $statsQuery = "SELECT 
                   COUNT(*) as total_transactions,
                   COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_transactions,
                   COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_transactions,
                   COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0) as total_amount,
                   COUNT(CASE WHEN created_at >= NOW() - INTERVAL '24 hours' THEN 1 END) as today_transactions
                   FROM mpesa_transactions";
    $statsStmt = $conn->prepare($statsQuery);
    $statsStmt->execute();
    $mpesa_stats = $statsStmt->fetch();
} catch (PDOException $e) {
    // Table doesn't exist yet, use default values
    $mpesa_stats = [
        'total_transactions' => 0,
        'successful_transactions' => 0,
        'failed_transactions' => 0,
        'total_amount' => 0,
        'today_transactions' => 0
    ];
}

$page_title = 'M-PESA Config';
include 'includes/header.php';
?>

<!-- Messages -->
<?php if ($message): ?>
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <div class="alert alert-<?php echo $messageType; ?>" style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border-radius: var(--admin-radius); background: <?php echo $messageType === 'success' ? '#D1FAE5' : '#FEE2E2'; ?>; color: <?php echo $messageType === 'success' ? '#065F46' : '#991B1B'; ?>;">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- M-PESA Statistics -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">M-PESA Transaction Statistics</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="stat-item">
                <h4><?php echo number_format($mpesa_stats['total_transactions']); ?></h4>
                <p>Total Transactions</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($mpesa_stats['successful_transactions']); ?></h4>
                <p>Successful</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($mpesa_stats['failed_transactions']); ?></h4>
                <p>Failed</p>
            </div>
            <div class="stat-item">
                <h4>KSh <?php echo number_format($mpesa_stats['total_amount'], 2); ?></h4>
                <p>Total Amount</p>
        </div>
            <div class="stat-item">
                <h4><?php echo number_format($mpesa_stats['today_transactions']); ?></h4>
                <p>Today's Transactions</p>
            </div>
        </div>
    </div>
                </div>
            
<!-- M-PESA Configuration Form -->
            <div class="admin-card">
                <div class="card-header">
        <h3 class="card-title">M-PESA Gateway Configuration</h3>
        <div class="card-actions">
            <button class="btn btn-secondary" onclick="testConnection()">
                        <i class="fas fa-plug"></i>
                        Test Connection
                    </button>
                </div>
    </div>
    <div class="card-body">
        <form method="POST" id="mpesaConfigForm">
            <input type="hidden" name="action" value="update_mpesa_config">
            
            <div class="form-section">
                <h4 class="form-section-title">Basic Configuration</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="business_short_code">Business Short Code *</label>
                        <input type="text" id="business_short_code" name="business_short_code" 
                               value="<?php echo htmlspecialchars($mpesa_config['business_short_code'] ?? ''); ?>" 
                               required>
                        <small class="form-help">Your M-PESA business short code (e.g., 174379)</small>
                    </div>
                    <div class="form-group">
                        <label for="passkey">Passkey *</label>
                        <input type="password" id="passkey" name="passkey" 
                               value="<?php echo htmlspecialchars($mpesa_config['passkey'] ?? ''); ?>" 
                               required>
                        <small class="form-help">Your M-PESA API passkey</small>
                    </div>
                </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="consumer_key">Consumer Key *</label>
                                <input type="text" id="consumer_key" name="consumer_key" 
                               value="<?php echo htmlspecialchars($mpesa_config['consumer_key'] ?? ''); ?>" 
                               required>
                        <small class="form-help">Your M-PESA API consumer key</small>
                            </div>
                            <div class="form-group">
                                <label for="consumer_secret">Consumer Secret *</label>
                                <input type="password" id="consumer_secret" name="consumer_secret" 
                               value="<?php echo htmlspecialchars($mpesa_config['consumer_secret'] ?? ''); ?>" 
                               required>
                        <small class="form-help">Your M-PESA API consumer secret</small>
                    </div>
                            </div>
                        </div>
                        
            <div class="form-section">
                <h4 class="form-section-title">Payment Methods</h4>
                        <div class="form-row">
                            <div class="form-group">
                        <label for="till_number">Till Number</label>
                        <input type="text" id="till_number" name="till_number" 
                               value="<?php echo htmlspecialchars($mpesa_config['till_number'] ?? ''); ?>">
                        <small class="form-help">M-PESA Till number for payments</small>
                            </div>
                            <div class="form-group">
                        <label for="paybill_number">Paybill Number</label>
                        <input type="text" id="paybill_number" name="paybill_number" 
                               value="<?php echo htmlspecialchars($mpesa_config['paybill_number'] ?? ''); ?>">
                        <small class="form-help">M-PESA Paybill number for payments</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                <h4 class="form-section-title">Callback URLs</h4>
                        <div class="form-row">
                            <div class="form-group">
                        <label for="callback_url">Callback URL</label>
                        <input type="url" id="callback_url" name="callback_url" 
                               value="<?php echo htmlspecialchars($mpesa_config['callback_url'] ?? 'https://bingetv.co.ke/api/mpesa/callback.php'); ?>">
                        <small class="form-help">URL to receive payment notifications</small>
                            </div>
                            <div class="form-group">
                        <label for="timeout_url">Timeout URL</label>
                        <input type="url" id="timeout_url" name="timeout_url" 
                               value="<?php echo htmlspecialchars($mpesa_config['timeout_url'] ?? 'https://bingetv.co.ke/api/mpesa/timeout.php'); ?>">
                        <small class="form-help">URL for payment timeout notifications</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                <h4 class="form-section-title">Environment Settings</h4>
                        <div class="form-row">
                            <div class="form-group">
                        <label for="environment">Environment</label>
                        <select id="environment" name="environment">
                            <option value="sandbox" <?php echo ($mpesa_config['environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : ''; ?>>Sandbox (Testing)</option>
                            <option value="production" <?php echo ($mpesa_config['environment'] ?? '') === 'production' ? 'selected' : ''; ?>>Production (Live)</option>
                                </select>
                        <small class="form-help">Choose between sandbox for testing or production for live payments</small>
                            </div>
                            <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" value="1" 
                                   <?php echo ($mpesa_config['is_active'] ?? 0) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Enable M-PESA Payments
                        </label>
                        <small class="form-help">Enable or disable M-PESA payment processing</small>
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
            
<!-- Recent M-PESA Transactions -->
            <div class="admin-card">
                <div class="card-header">
        <h3 class="card-title">Recent M-PESA Transactions</h3>
        <a href="mpesa-transactions" class="btn btn-secondary">View All</a>
                </div>
    <div class="card-body">
        <?php
        try {
            $recentQuery = "SELECT * FROM mpesa_transactions ORDER BY created_at DESC LIMIT 10";
            $recentStmt = $conn->prepare($recentQuery);
            $recentStmt->execute();
            $recent_transactions = $recentStmt->fetchAll();
        } catch (PDOException $e) {
            $recent_transactions = [];
        }
        ?>
        
        <?php if (empty($recent_transactions)): ?>
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <h3>No transactions yet</h3>
            <p>M-PESA transactions will appear here once payments are processed.</p>
                    </div>
        <?php else: ?>
        <div class="transactions-list">
            <?php foreach ($recent_transactions as $transaction): ?>
            <div class="transaction-item">
                <div class="transaction-info">
                    <div class="transaction-details">
                        <h4><?php echo htmlspecialchars($transaction['phone_number']); ?></h4>
                        <p>Amount: KSh <?php echo number_format($transaction['amount'], 2); ?></p>
                        <small><?php echo date('M j, Y H:i', strtotime($transaction['created_at'])); ?></small>
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

.status-completed {
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
    if (confirm('This will test the M-PESA API connection. Continue?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="test_mpesa_connection">
        `;
        document.body.appendChild(form);
        form.submit();
    }
        }
        
        function resetForm() {
    if (confirm('Are you sure you want to reset the form? All changes will be lost.')) {
        document.getElementById('mpesaConfigForm').reset();
    }
}

// Form validation
document.getElementById('mpesaConfigForm').addEventListener('submit', function(e) {
    const requiredFields = ['business_short_code', 'passkey', 'consumer_key', 'consumer_secret'];
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