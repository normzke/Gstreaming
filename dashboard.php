<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get user details
$userQuery = "SELECT * FROM users WHERE id = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Get user's active subscriptions
$subscriptionsQuery = "SELECT us.*, p.name as package_name, p.price, p.duration_days, p.max_devices, p.features 
                      FROM user_subscriptions us 
                      JOIN packages p ON us.package_id = p.id 
                      WHERE us.user_id = ? AND us.status = 'active' AND us.end_date > NOW() 
                      ORDER BY us.end_date ASC";
$subscriptionsStmt = $conn->prepare($subscriptionsQuery);
$subscriptionsStmt->execute([$_SESSION['user_id']]);
$activeSubscriptions = $subscriptionsStmt->fetchAll();

// Get user's streaming access
$streamingQuery = "SELECT usa.*, us.end_date 
                   FROM user_streaming_access usa 
                   JOIN user_subscriptions us ON usa.subscription_id = us.id 
                   WHERE usa.user_id = ? AND usa.is_active = true AND us.end_date > NOW() 
                   ORDER BY us.end_date ASC";
$streamingStmt = $conn->prepare($streamingQuery);
$streamingStmt->execute([$_SESSION['user_id']]);
$streamingAccess = $streamingStmt->fetchAll();

// Get recent payments
$paymentsQuery = "SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$paymentsStmt = $conn->prepare($paymentsQuery);
$paymentsStmt->execute([$_SESSION['user_id']]);
$recentPayments = $paymentsStmt->fetchAll();

// Get notifications
$notificationsQuery = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$notificationsStmt = $conn->prepare($notificationsQuery);
$notificationsStmt->execute([$_SESSION['user_id']]);
$notifications = $notificationsStmt->fetchAll();

// Calculate days until expiration
$daysUntilExpiration = 0;
if (!empty($activeSubscriptions)) {
    $nearestExpiration = $activeSubscriptions[0]['end_date'];
    $daysUntilExpiration = (strtotime($nearestExpiration) - time()) / (60 * 60 * 24);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GStreaming</title>
    <meta name="description" content="Manage your GStreaming subscription and streaming access">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-satellite-dish"></i>
                <span class="logo-text">GStreaming</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="channels.php" class="nav-link">Channels</a>
                </li>
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link btn-login">Logout</a>
                </li>
            </ul>
            
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Dashboard -->
    <section class="dashboard">
        <div class="container">
            <!-- Welcome Section -->
            <div class="dashboard-header">
                <div class="welcome-card">
                    <div class="welcome-content">
                        <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                        <p>Manage your streaming subscription and access your channels</p>
                        
                        <?php if ($daysUntilExpiration > 0 && $daysUntilExpiration <= 7): ?>
                            <div class="expiry-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Your subscription expires in <?php echo floor($daysUntilExpiration); ?> days</span>
                                <button class="btn btn-warning btn-sm" onclick="showRenewalModal()">
                                    <i class="fas fa-sync"></i>
                                    Renew Now
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tv"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo count($activeSubscriptions); ?></div>
                        <div class="stat-label">Active Subscriptions</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $daysUntilExpiration > 0 ? floor($daysUntilExpiration) : 0; ?></div>
                        <div class="stat-label">Days Remaining</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo !empty($activeSubscriptions) ? $activeSubscriptions[0]['max_devices'] : 0; ?></div>
                        <div class="stat-label">Max Devices</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo count($notifications); ?></div>
                        <div class="stat-label">New Notifications</div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="dashboard-content">
                <!-- Active Subscriptions -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-crown"></i> Active Subscriptions</h2>
                        <a href="index.php#packages" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i>
                            Add Package
                        </a>
                    </div>
                    
                    <?php if (!empty($activeSubscriptions)): ?>
                        <div class="subscriptions-grid">
                            <?php foreach ($activeSubscriptions as $subscription): ?>
                                <div class="subscription-card">
                                    <div class="subscription-header">
                                        <h3><?php echo htmlspecialchars($subscription['package_name']); ?></h3>
                                        <span class="subscription-status status-active">Active</span>
                                    </div>
                                    
                                    <div class="subscription-details">
                                        <div class="detail-row">
                                            <span class="label">Price:</span>
                                            <span class="value">KES <?php echo number_format($subscription['price']); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="label">Duration:</span>
                                            <span class="value"><?php echo $subscription['duration_days']; ?> days</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="label">Max Devices:</span>
                                            <span class="value"><?php echo $subscription['max_devices']; ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="label">Expires:</span>
                                            <span class="value"><?php echo date('M j, Y', strtotime($subscription['end_date'])); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="subscription-actions">
                                        <button class="btn btn-secondary btn-sm" onclick="showSubscriptionDetails(<?php echo $subscription['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                            View Details
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="renewSubscription(<?php echo $subscription['id']; ?>)">
                                            <i class="fas fa-sync"></i>
                                            Renew
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-tv"></i>
                            </div>
                            <h3>No Active Subscriptions</h3>
                            <p>You don't have any active subscriptions. Subscribe to a package to start streaming!</p>
                            <a href="index.php#packages" class="btn btn-primary">
                                <i class="fas fa-crown"></i>
                                Browse Packages
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Streaming Access -->
                <?php if (!empty($streamingAccess)): ?>
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2><i class="fas fa-play"></i> Streaming Access</h2>
                            <button class="btn btn-secondary btn-sm" onclick="copyAllCredentials()">
                                <i class="fas fa-copy"></i>
                                Copy All
                            </button>
                        </div>
                        
                        <div class="streaming-access-card">
                            <div class="access-header">
                                <h3>Your Streaming Credentials</h3>
                                <div class="access-status">
                                    <i class="fas fa-circle status-active"></i>
                                    <span>Active</span>
                                </div>
                            </div>
                            
                            <div class="credentials-grid">
                                <div class="credential-item">
                                    <label>Streaming URL:</label>
                                    <div class="credential-value">
                                        <input type="text" id="streaming-url" value="<?php echo htmlspecialchars($streamingAccess[0]['streaming_url']); ?>" readonly>
                                        <button class="btn btn-sm btn-secondary" onclick="copyCredential('streaming-url')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="credential-item">
                                    <label>Username:</label>
                                    <div class="credential-value">
                                        <input type="text" id="streaming-username" value="<?php echo htmlspecialchars($streamingAccess[0]['username']); ?>" readonly>
                                        <button class="btn btn-sm btn-secondary" onclick="copyCredential('streaming-username')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="credential-item">
                                    <label>Password:</label>
                                    <div class="credential-value">
                                        <input type="password" id="streaming-password" value="<?php echo htmlspecialchars($streamingAccess[0]['password']); ?>" readonly>
                                        <button class="btn btn-sm btn-secondary" onclick="togglePasswordVisibility('streaming-password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-secondary" onclick="copyCredential('streaming-password')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="device-setup">
                                <h4>Quick Setup Instructions</h4>
                                <div class="setup-tabs">
                                    <button class="setup-tab active" data-device="smart-tv">Smart TV</button>
                                    <button class="setup-tab" data-device="firestick">Firestick</button>
                                    <button class="setup-tab" data-device="roku">Roku</button>
                                    <button class="setup-tab" data-device="mobile">Mobile</button>
                                </div>
                                
                                <div class="setup-content">
                                    <div class="setup-instruction active" id="smart-tv">
                                        <ol>
                                            <li>Download VLC Media Player or IPTV Smarters app</li>
                                            <li>Open the app and select "Add Playlist"</li>
                                            <li>Enter your streaming URL</li>
                                            <li>Enter your username and password</li>
                                            <li>Start streaming!</li>
                                        </ol>
                                    </div>
                                    
                                    <div class="setup-instruction" id="firestick">
                                        <ol>
                                            <li>Go to Firestick home screen</li>
                                            <li>Search for "IPTV Smarters" in app store</li>
                                            <li>Install and open the app</li>
                                            <li>Add your streaming URL and credentials</li>
                                            <li>Enjoy your channels!</li>
                                        </ol>
                                    </div>
                                    
                                    <div class="setup-instruction" id="roku">
                                        <ol>
                                            <li>Go to Roku Channel Store</li>
                                            <li>Search for "IPTV Player"</li>
                                            <li>Install the app</li>
                                            <li>Add your streaming details</li>
                                            <li>Start streaming!</li>
                                        </ol>
                                    </div>
                                    
                                    <div class="setup-instruction" id="mobile">
                                        <ol>
                                            <li>Download "IPTV Smarters" from app store</li>
                                            <li>Open the app</li>
                                            <li>Add playlist using your streaming URL</li>
                                            <li>Enter your credentials</li>
                                            <li>Stream on the go!</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recent Payments -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-credit-card"></i> Recent Payments</h2>
                        <a href="payments.php" class="btn btn-secondary btn-sm">View All</a>
                    </div>
                    
                    <?php if (!empty($recentPayments)): ?>
                        <div class="payments-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPayments as $payment): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                                            <td>KES <?php echo number_format($payment['amount']); ?></td>
                                            <td><?php echo strtoupper($payment['payment_method']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $payment['status']; ?>">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($payment['mpesa_receipt_number']): ?>
                                                    <code><?php echo htmlspecialchars($payment['mpesa_receipt_number']); ?></code>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <h3>No Payments Yet</h3>
                            <p>Your payment history will appear here once you make your first subscription payment.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Notifications -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-bell"></i> Notifications</h2>
                        <a href="notifications.php" class="btn btn-secondary btn-sm">View All</a>
                    </div>
                    
                    <?php if (!empty($notifications)): ?>
                        <div class="notifications-list">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                                    <div class="notification-icon">
                                        <i class="fas fa-<?php echo getNotificationIcon($notification['type']); ?>"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <span class="notification-time"><?php echo timeAgo($notification['created_at']); ?></span>
                                    </div>
                                    <?php if (!$notification['is_read']): ?>
                                        <div class="notification-badge">New</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <h3>No Notifications</h3>
                            <p>You'll receive notifications about your subscription, payments, and important updates here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Renewal Modal -->
    <div id="renewalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Renew Subscription</h3>
                <span class="modal-close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="renewal-form">
                    <div class="current-subscription">
                        <h4>Current Subscription</h4>
                        <div class="subscription-info">
                            <span class="package-name"><?php echo !empty($activeSubscriptions) ? $activeSubscriptions[0]['package_name'] : 'No active subscription'; ?></span>
                            <span class="package-price">KES <?php echo !empty($activeSubscriptions) ? number_format($activeSubscriptions[0]['price']) : '0'; ?></span>
                        </div>
                    </div>
                    
                    <div class="renewal-options">
                        <h4>Renewal Options</h4>
                        <div class="renewal-methods">
                            <div class="renewal-method active" data-method="same">
                                <input type="radio" name="renewal_method" value="same" checked>
                                <div class="method-info">
                                    <h5>Renew Current Package</h5>
                                    <p>Extend your current subscription for another billing period</p>
                                </div>
                            </div>
                            
                            <div class="renewal-method" data-method="upgrade">
                                <input type="radio" name="renewal_method" value="upgrade">
                                <div class="method-info">
                                    <h5>Upgrade Package</h5>
                                    <p>Choose a different package with more features</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="renewal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeRenewalModal()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary" onclick="proceedWithRenewal()">
                            <i class="fas fa-credit-card"></i>
                            Proceed to Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating WhatsApp Button -->
    <div class="whatsapp-float">
        <a href="https://wa.me/254768704834?text=Hello%2C%20I%20need%20help%20with%20my%20dashboard" target="_blank" class="whatsapp-btn">
            <i class="fab fa-whatsapp"></i>
            <span class="whatsapp-text">Need Help?</span>
        </a>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-satellite-dish"></i>
                        <span>GStreaming</span>
                    </div>
                    <p>Premium TV streaming service for Kenya. Stream thousands of channels on any device.</p>
                    
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="channels.php">Channels</a></li>
                        <li><a href="index.php#packages">Packages</a></li>
                        <li><a href="index.php#devices">Supported Devices</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="index.php#support">Support</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Account</h4>
                    <ul class="footer-links">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>support@gstreaming.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+254 768 704 834</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Nairobi, Kenya</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> GStreaming. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="privacy.php">Privacy Policy</a>
                        <a href="terms.php">Terms of Service</a>
                        <a href="refund.php">Refund Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>

<?php
// Helper functions
function getNotificationIcon($type) {
    $icons = [
        'subscription' => 'crown',
        'payment' => 'credit-card',
        'support' => 'headset',
        'general' => 'info-circle'
    ];
    return $icons[$type] ?? 'bell';
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>