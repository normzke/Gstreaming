<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/mpesa_integration.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'update_config':
                $configs = [
                    'consumer_key' => $_POST['consumer_key'] ?? '',
                    'consumer_secret' => $_POST['consumer_secret'] ?? '',
                    'shortcode' => $_POST['shortcode'] ?? '',
                    'passkey' => $_POST['passkey'] ?? '',
                    'callback_url' => $_POST['callback_url'] ?? '',
                    'initiator_name' => $_POST['initiator_name'] ?? '',
                    'security_credential' => $_POST['security_credential'] ?? '',
                    'environment' => $_POST['environment'] ?? 'sandbox',
                    'test_phone' => $_POST['test_phone'] ?? ''
                ];
                
                foreach ($configs as $key => $value) {
                    $updateQuery = "UPDATE mpesa_config SET config_value = ?, updated_at = CURRENT_TIMESTAMP WHERE config_key = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->execute([$value, $key]);
                }
                
                $message = 'M-PESA configuration updated successfully!';
                $messageType = 'success';
                break;
                
            case 'test_connection':
                $mpesaConfig = getMpesaConfig($conn);
                $mpesa = new MpesaIntegration($mpesaConfig);
                $testResult = $mpesa->testConnection();
                
                if ($testResult['success']) {
                    $message = 'M-PESA connection test successful! Environment: ' . $testResult['environment'];
                    $messageType = 'success';
                } else {
                    $message = 'M-PESA connection test failed: ' . $testResult['message'];
                    $messageType = 'error';
                }
                break;
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get current M-PESA configuration
function getMpesaConfig($conn) {
    $query = "SELECT config_key, config_value FROM mpesa_config";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    return [
        'consumer_key' => $configs['consumer_key'] ?? '',
        'consumer_secret' => $configs['consumer_secret'] ?? '',
        'shortcode' => $configs['shortcode'] ?? '',
        'passkey' => $configs['passkey'] ?? '',
        'callback_url' => $configs['callback_url'] ?? '',
        'initiator_name' => $configs['initiator_name'] ?? '',
        'security_credential' => $configs['security_credential'] ?? '',
        'environment' => $configs['environment'] ?? 'sandbox',
        'test_phone' => $configs['test_phone'] ?? ''
    ];
}

$currentConfig = getMpesaConfig($conn);

// Test configuration status
$mpesa = new MpesaIntegration($currentConfig);
$configStatus = $mpesa->getConfigStatus();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-PESA Configuration - GStreaming Admin</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-page">
    <!-- Admin Navigation -->
    <nav class="admin-navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-satellite-dish"></i>
                <span class="logo-text">GStreaming Admin</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link">Users</a>
                </li>
                <li class="nav-item">
                    <a href="packages.php" class="nav-link">Packages</a>
                </li>
                <li class="nav-item">
                    <a href="payments.php" class="nav-link">Payments</a>
                </li>
                <li class="nav-item">
                    <a href="channels.php" class="nav-link">Channels</a>
                </li>
                <li class="nav-item">
                    <a href="gallery.php" class="nav-link">Gallery</a>
                </li>
                <li class="nav-item">
                    <a href="mpesa-config.php" class="nav-link active">M-PESA Config</a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">Settings</a>
                </li>
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Admin Main -->
    <main class="admin-main">
        <div class="container">
            <div class="admin-header">
                <h1>M-PESA Configuration</h1>
                <p>Configure M-PESA API credentials and settings</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Configuration Status -->
            <div class="admin-card">
                <div class="card-header">
                    <h3>Configuration Status</h3>
                    <button type="button" class="btn btn-primary btn-sm" onclick="testConnection()">
                        <i class="fas fa-plug"></i>
                        Test Connection
                    </button>
                </div>
                
                <div class="config-status">
                    <div class="status-item">
                        <span class="status-label">Configuration:</span>
                        <span class="status-value status-<?php echo $configStatus['configured'] ? 'success' : 'error'; ?>">
                            <i class="fas fa-<?php echo $configStatus['configured'] ? 'check-circle' : 'times-circle'; ?>"></i>
                            <?php echo $configStatus['configured'] ? 'Complete' : 'Incomplete'; ?>
                        </span>
                    </div>
                    
                    <div class="status-item">
                        <span class="status-label">Environment:</span>
                        <span class="status-value">
                            <i class="fas fa-<?php echo $configStatus['environment'] === 'production' ? 'globe' : 'flask'; ?>"></i>
                            <?php echo ucfirst($configStatus['environment']); ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($configStatus['missing'])): ?>
                        <div class="status-item">
                            <span class="status-label">Missing Fields:</span>
                            <span class="status-value status-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?php echo implode(', ', $configStatus['missing']); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- M-PESA Configuration Form -->
            <div class="admin-card">
                <div class="card-header">
                    <h3>M-PESA API Configuration</h3>
                </div>
                
                <form method="POST" class="config-form">
                    <input type="hidden" name="action" value="update_config">
                    
                    <div class="form-section">
                        <h4>Basic Configuration</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="consumer_key">Consumer Key *</label>
                                <input type="text" id="consumer_key" name="consumer_key" 
                                       value="<?php echo htmlspecialchars($currentConfig['consumer_key']); ?>" 
                                       placeholder="Enter M-PESA Consumer Key" required>
                                <small>Get this from your M-PESA Developer Portal</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="consumer_secret">Consumer Secret *</label>
                                <input type="password" id="consumer_secret" name="consumer_secret" 
                                       value="<?php echo htmlspecialchars($currentConfig['consumer_secret']); ?>" 
                                       placeholder="Enter M-PESA Consumer Secret" required>
                                <small>Get this from your M-PESA Developer Portal</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shortcode">Business Shortcode *</label>
                                <input type="text" id="shortcode" name="shortcode" 
                                       value="<?php echo htmlspecialchars($currentConfig['shortcode']); ?>" 
                                       placeholder="Enter Business Shortcode" required>
                                <small>Your M-PESA Business Shortcode</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="passkey">Passkey *</label>
                                <input type="password" id="passkey" name="passkey" 
                                       value="<?php echo htmlspecialchars($currentConfig['passkey']); ?>" 
                                       placeholder="Enter M-PESA Passkey" required>
                                <small>Get this from your M-PESA Developer Portal</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4>Advanced Configuration</h4>
                        
                        <div class="form-group">
                            <label for="callback_url">Callback URL *</label>
                            <input type="url" id="callback_url" name="callback_url" 
                                   value="<?php echo htmlspecialchars($currentConfig['callback_url']); ?>" 
                                   placeholder="Enter Callback URL" required>
                            <small>URL where M-PESA will send payment notifications</small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="initiator_name">Initiator Name</label>
                                <input type="text" id="initiator_name" name="initiator_name" 
                                       value="<?php echo htmlspecialchars($currentConfig['initiator_name']); ?>" 
                                       placeholder="Enter Initiator Name">
                                <small>For B2C transactions (refunds)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="security_credential">Security Credential</label>
                                <input type="password" id="security_credential" name="security_credential" 
                                       value="<?php echo htmlspecialchars($currentConfig['security_credential']); ?>" 
                                       placeholder="Enter Security Credential">
                                <small>Encrypted password for B2C transactions</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4>Environment Settings</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="environment">Environment *</label>
                                <select id="environment" name="environment" required>
                                    <option value="sandbox" <?php echo $currentConfig['environment'] === 'sandbox' ? 'selected' : ''; ?>>Sandbox (Testing)</option>
                                    <option value="production" <?php echo $currentConfig['environment'] === 'production' ? 'selected' : ''; ?>>Production (Live)</option>
                                </select>
                                <small>Use Sandbox for testing, Production for live transactions</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="test_phone">Test Phone Number</label>
                                <input type="tel" id="test_phone" name="test_phone" 
                                       value="<?php echo htmlspecialchars($currentConfig['test_phone']); ?>" 
                                       placeholder="254XXXXXXXXX">
                                <small>Test phone number for sandbox environment</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Save Configuration
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-undo"></i>
                            Reset
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- API Documentation -->
            <div class="admin-card">
                <div class="card-header">
                    <h3>M-PESA API Documentation</h3>
                </div>
                
                <div class="documentation">
                    <div class="doc-section">
                        <h4>Getting Started</h4>
                        <ol>
                            <li>Visit the <a href="https://developer.safaricom.co.ke/" target="_blank">M-PESA Developer Portal</a></li>
                            <li>Create an account and register your application</li>
                            <li>Get your Consumer Key and Consumer Secret</li>
                            <li>Get your Business Shortcode and Passkey</li>
                            <li>Configure the callback URL to point to your server</li>
                            <li>Test with sandbox environment first</li>
                        </ol>
                    </div>
                    
                    <div class="doc-section">
                        <h4>Required Information</h4>
                        <ul>
                            <li><strong>Consumer Key:</strong> From M-PESA Developer Portal</li>
                            <li><strong>Consumer Secret:</strong> From M-PESA Developer Portal</li>
                            <li><strong>Business Shortcode:</strong> Your M-PESA Business Number</li>
                            <li><strong>Passkey:</strong> Generated from M-PESA Developer Portal</li>
                            <li><strong>Callback URL:</strong> <code>http://localhost:4000/api/mpesa/callback.php</code></li>
                        </ul>
                    </div>
                    
                    <div class="doc-section">
                        <h4>Testing</h4>
                        <p>Use the sandbox environment for testing with the provided test phone number. 
                        Make sure your callback URL is accessible from the internet for production use.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
    <script src="assets/js/admin.js"></script>
    <script>
        function testConnection() {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
            button.disabled = true;
            
            fetch('mpesa-config.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=test_connection'
            })
            .then(response => response.text())
            .then(data => {
                // Reload page to show result
                window.location.reload();
            })
            .catch(error => {
                button.innerHTML = originalText;
                button.disabled = false;
                alert('Connection test failed: ' + error.message);
            });
        }
        
        function resetForm() {
            if (confirm('Are you sure you want to reset the form? All unsaved changes will be lost.')) {
                document.querySelector('.config-form').reset();
            }
        }
        
        // Auto-save callback URL when environment changes
        document.getElementById('environment').addEventListener('change', function() {
            const callbackInput = document.getElementById('callback_url');
            if (callbackInput.value === '' || callbackInput.value.includes('localhost')) {
                if (this.value === 'sandbox') {
                    callbackInput.value = 'http://localhost:4000/api/mpesa/callback.php';
                } else {
                    callbackInput.value = 'https://yourdomain.com/api/mpesa/callback.php';
                }
            }
        });
    </script>
</body>
</html>
