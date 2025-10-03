<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../lib/functions.php';

$db = new Database();
$conn = $db->getConnection();

// Get selected package
$package_id = isset($_GET['package']) ? (int)$_GET['package'] : 0;
$package = null;

if ($package_id > 0) {
            $packageQuery = "SELECT * FROM packages WHERE id = ? AND is_active = true";
            $packageStmt = $conn->prepare($packageQuery);
    $packageStmt->execute([$package_id]);
            $package = $packageStmt->fetch();
}

if (!$package) {
    header('Location: index.php');
                exit();
}

// Get package channels
$channelsQuery = "SELECT c.* FROM channels c 
                 JOIN package_channels pc ON c.id = pc.channel_id 
                 WHERE pc.package_id = ? AND c.is_active = true 
                 ORDER BY c.sort_order, c.name";
$channelsStmt = $conn->prepare($channelsQuery);
$channelsStmt->execute([$package_id]);
$packageChannels = $channelsStmt->fetchAll();

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $userQuery = "SELECT * FROM users WHERE id = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->execute([$_SESSION['user_id']]);
    $user = $userStmt->fetch();
}

$page_title = 'Subscribe';
include '../includes/header.php';
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe to <?php echo htmlspecialchars($package['name']); ?> - BingeTV</title>
    <meta name="description" content="Subscribe to <?php echo htmlspecialchars($package['name']); ?> and enjoy premium TV streaming">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/subscribe.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-satellite-dish"></i>
                <span class="logo-text">BingeTV</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="channels.php" class="nav-link">Channels</a>
                </li>
                <li class="nav-item">
                    <a href="index.php#packages" class="nav-link">Packages</a>
                </li>
                <li class="nav-item">
                    <a href="index.php#devices" class="nav-link">Devices</a>
                </li>
                <li class="nav-item">
                    <a href="gallery.php" class="nav-link">Gallery</a>
                </li>
                <li class="nav-item">
                    <a href="index.php#support" class="nav-link">Support</a>
                </li>
                <?php if ($user): ?>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link btn-login">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="login.php" class="nav-link btn-login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="register.php" class="nav-link btn-register">Get Started</a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Subscription Page -->
    <section class="subscription-page">
    <div class="container">
            <!-- Package Overview -->
            <div class="package-overview">
                <div class="package-header">
                    <div class="package-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="package-info">
                        <h1><?php echo htmlspecialchars($package['name']); ?></h1>
                        <p class="package-description"><?php echo htmlspecialchars($package['description']); ?></p>
                        <div class="package-price">
                            <span class="price-amount">KES <?php echo number_format($package['price']); ?></span>
                            <span class="price-period">/ <?php echo $package['duration_days']; ?> days</span>
                    </div>
                    </div>
                    </div>
                    
                    <div class="package-features">
                    <div class="feature-grid">
                        <div class="feature-item">
                            <i class="fas fa-tv"></i>
                            <span><?php echo count($packageChannels); ?>+ Channels</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-mobile-alt"></i>
                            <span><?php echo $package['max_devices']; ?> Device<?php echo $package['max_devices'] > 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-hd-video"></i>
                            <span><?php echo json_decode($package['features'], true)['quality'] ?? 'HD'; ?> Quality</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-headset"></i>
                            <span><?php echo json_decode($package['features'], true)['support'] ?? 'Email'; ?> Support</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Steps -->
            <div class="subscription-steps">
                <div class="step-indicator">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Package</div>
                    </div>
                    <div class="step <?php echo $user ? 'active' : ''; ?>" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label"><?php echo $user ? 'Payment' : 'Register'; ?></div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Confirm</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Stream</div>
                    </div>
                </div>

                <!-- Step 1: Package Selection (Always Active) -->
                <div class="step-content active" id="step-1">
                    <div class="step-header">
                        <h2>Package Selected</h2>
                        <p>You have selected the <?php echo htmlspecialchars($package['name']); ?> package</p>
                    </div>
                    
                    <div class="package-details">
                        <div class="channels-preview">
                            <h3>Available Channels</h3>
                            <div class="channels-grid">
                                <?php 
                                $displayChannels = array_slice($packageChannels, 0, 8);
                                foreach ($displayChannels as $channel): 
                                ?>
                                    <div class="channel-item">
                                        <?php if ($channel['logo_url']): ?>
                                            <img src="<?php echo htmlspecialchars($channel['logo_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($channel['name']); ?>"
                                                 onerror="this.src='../images/default-channel.svg'">
                                        <?php else: ?>
                                            <div class="default-logo">
                                                <i class="fas fa-tv"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($channel['name']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($packageChannels) > 8): ?>
                                    <div class="channel-item more-channels">
                                        <div class="more-logo">
                                            <i class="fas fa-plus"></i>
                                        </div>
                                        <span>+<?php echo count($packageChannels) - 8; ?> more</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (count($packageChannels) > 8): ?>
                                <button type="button" class="btn btn-secondary" onclick="showAllChannels()">
                                    <i class="fas fa-eye"></i>
                                    View All <?php echo count($packageChannels); ?> Channels
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="package-summary">
                            <h3>Package Summary</h3>
                            <div class="summary-item">
                                <span>Package Name:</span>
                                <span><?php echo htmlspecialchars($package['name']); ?></span>
                            </div>
                            <div class="summary-item">
                                <span>Duration:</span>
                                <span><?php echo $package['duration_days']; ?> days</span>
                            </div>
                            <div class="summary-item">
                                <span>Max Devices:</span>
                                <span><?php echo $package['max_devices']; ?></span>
                            </div>
                            <div class="summary-item">
                                <span>Channels:</span>
                                <span><?php echo count($packageChannels); ?> channels</span>
                            </div>
                            <div class="summary-item total">
                                <span>Total Amount:</span>
                                <span>KES <?php echo number_format($package['price']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="step-actions">
                        <?php if ($user): ?>
                            <button type="button" class="btn btn-primary btn-lg" onclick="proceedToPayment()">
                                <i class="fas fa-credit-card"></i>
                                Proceed to Payment
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary btn-lg" onclick="proceedToRegister()">
                                <i class="fas fa-user-plus"></i>
                                Register & Subscribe
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg" onclick="proceedToLogin()">
                                <i class="fas fa-sign-in-alt"></i>
                                Login & Subscribe
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Step 2: Registration/Login or Payment -->
                <?php if ($user): ?>
                    <!-- Payment Form -->
                    <div class="step-content" id="step-2">
                        <div class="step-header">
                            <h2>Payment Details</h2>
                            <p>Complete your subscription payment</p>
                        </div>
                        
                        <div class="payment-section">
                        <div class="payment-methods">
                                <h3>Choose Payment Method</h3>
                                <div class="payment-options">
                                    <div class="payment-option active" data-method="mpesa">
                                        <div class="payment-icon">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <div class="payment-info">
                                            <h4>M-PESA</h4>
                                            <p>Pay using M-PESA mobile money</p>
                                        </div>
                                        <div class="payment-radio">
                                            <input type="radio" name="payment_method" value="mpesa" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="payment-form">
                                <div class="form-group">
                                    <label for="phone_number">M-PESA Phone Number</label>
                                    <input type="tel" id="phone_number" name="phone_number" 
                                           value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                           placeholder="254XXXXXXXXX" required>
                                    <small>Enter your M-PESA registered phone number</small>
                                </div>
                                
                                <div class="payment-summary">
                                    <div class="summary-row">
                                        <span>Package:</span>
                                        <span><?php echo htmlspecialchars($package['name']); ?></span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Duration:</span>
                                        <span><?php echo $package['duration_days']; ?> days</span>
                                    </div>
                                    <div class="summary-row total">
                                        <span>Amount to Pay:</span>
                                        <span>KES <?php echo number_format($package['price']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="payment-actions">
                                    <button type="button" class="btn btn-secondary" onclick="goBack()">
                                        <i class="fas fa-arrow-left"></i>
                                        Back
                                    </button>
                                    <button type="button" class="btn btn-primary btn-lg" onclick="initiatePayment()">
                                        <i class="fas fa-mobile-alt"></i>
                                        Pay with M-PESA
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Registration/Login Options -->
                    <div class="step-content" id="step-2">
                        <div class="step-header">
                            <h2>Create Account or Login</h2>
                            <p>You need an account to subscribe to this package</p>
                        </div>
                        
                        <div class="auth-options">
                            <div class="auth-tabs">
                                <button type="button" class="auth-tab active" data-tab="register">
                                    <i class="fas fa-user-plus"></i>
                                    Create Account
                                </button>
                                <button type="button" class="auth-tab" data-tab="login">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Login
                                </button>
                            </div>
                            
                            <div class="auth-content">
                                <!-- Registration Form -->
                                <div class="auth-form active" id="register-form">
                                    <form id="registrationForm">
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="reg_first_name">First Name *</label>
                                                <input type="text" id="reg_first_name" name="first_name" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="reg_last_name">Last Name *</label>
                                                <input type="text" id="reg_last_name" name="last_name" required>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="reg_email">Email Address *</label>
                                            <input type="email" id="reg_email" name="email" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="reg_phone">Phone Number *</label>
                                            <input type="tel" id="reg_phone" name="phone" placeholder="254XXXXXXXXX" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="reg_password">Password *</label>
                                            <input type="password" id="reg_password" name="password" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="reg_confirm_password">Confirm Password *</label>
                                            <input type="password" id="reg_confirm_password" name="confirm_password" required>
                                        </div>
                                        
                                        <div class="form-actions">
                                            <button type="button" class="btn btn-secondary" onclick="goBack()">
                                                <i class="fas fa-arrow-left"></i>
                                                Back
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-user-plus"></i>
                                                Create Account & Continue
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Login Form -->
                                <div class="auth-form" id="login-form">
                                    <form id="loginForm">
                                        <div class="form-group">
                                            <label for="login_email">Email Address</label>
                                            <input type="email" id="login_email" name="email" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="login_password">Password</label>
                                            <input type="password" id="login_password" name="password" required>
                                        </div>
                                        
                                        <div class="form-actions">
                                            <button type="button" class="btn btn-secondary" onclick="goBack()">
                                                <i class="fas fa-arrow-left"></i>
                                                Back
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-sign-in-alt"></i>
                                                Login & Continue
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Step 3: Payment Confirmation -->
                <div class="step-content" id="step-3">
                    <div class="step-header">
                        <h2>Payment Confirmation</h2>
                        <p>Please complete your M-PESA payment</p>
                    </div>
                    
                    <div class="payment-instructions">
                        <div class="instruction-card">
                            <div class="instruction-icon">
                                    <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="instruction-content">
                                <h3>M-PESA Payment Steps</h3>
                                <ol>
                                    <li>Go to your M-PESA menu</li>
                                    <li>Select "Lipa na M-PESA"</li>
                                    <li>Select "Paybill"</li>
                                    <li>Enter Paybill Number: <strong><?php echo MPESA_SHORTCODE; ?></strong></li>
                                    <li>Enter Account Number: <strong id="account-number">Loading...</strong></li>
                                    <li>Enter Amount: <strong>KES <?php echo number_format($package['price']); ?></strong></li>
                                    <li>Enter your M-PESA PIN</li>
                                    <li>Confirm the transaction</li>
                                </ol>
                            </div>
                        </div>
                        
                        <div class="payment-status">
                            <div class="status-indicator">
                                <i class="fas fa-clock"></i>
                                <span>Waiting for payment confirmation...</span>
                            </div>
                            <div class="payment-details">
                                <div class="detail-item">
                                    <span>Transaction ID:</span>
                                    <span id="transaction-id">Loading...</span>
                                </div>
                                <div class="detail-item">
                                    <span>Amount:</span>
                                    <span>KES <?php echo number_format($package['price']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span>Phone:</span>
                                    <span id="payment-phone">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="step-actions">
                        <button type="button" class="btn btn-secondary" onclick="cancelPayment()">
                            <i class="fas fa-times"></i>
                            Cancel Payment
                        </button>
                        <button type="button" class="btn btn-primary" onclick="checkPaymentStatus()">
                            <i class="fas fa-sync"></i>
                            Check Payment Status
                        </button>
                    </div>
                </div>

                <!-- Step 4: Streaming Access -->
                <div class="step-content" id="step-4">
                    <div class="step-header">
                        <h2>Welcome to BingeTV!</h2>
                        <p>Your subscription is now active. Here are your streaming details:</p>
                    </div>
                    
                    <div class="streaming-access">
                        <div class="access-card">
                            <div class="access-header">
                                <i class="fas fa-tv"></i>
                                <h3>Your Streaming Access</h3>
                            </div>
                            
                            <div class="access-details">
                                <div class="detail-group">
                                    <label>Streaming URL:</label>
                                    <div class="url-container">
                                        <input type="text" id="streaming-url" value="Loading..." readonly>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="copyStreamingUrl()">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="detail-group">
                                    <label>Username:</label>
                                    <div class="url-container">
                                        <input type="text" id="streaming-username" value="Loading..." readonly>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="copyUsername()">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="detail-group">
                                    <label>Password:</label>
                                    <div class="url-container">
                                        <input type="password" id="streaming-password" value="Loading..." readonly>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="togglePassword()">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="copyPassword()">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="device-instructions">
                            <h3>How to Access on Your Devices</h3>
                            <div class="device-tabs">
                                <button type="button" class="device-tab active" data-device="smart-tv">
                                    <i class="fas fa-tv"></i>
                                    Smart TV
                                </button>
                                <button type="button" class="device-tab" data-device="firestick">
                                    <i class="fab fa-amazon"></i>
                                    Firestick
                                </button>
                                <button type="button" class="device-tab" data-device="roku">
                                    <i class="fas fa-play"></i>
                                    Roku
                                </button>
                                <button type="button" class="device-tab" data-device="mobile">
                                    <i class="fas fa-mobile-alt"></i>
                                    Mobile
                                </button>
                            </div>
                            
                            <div class="device-content">
                                <div class="device-instruction active" id="smart-tv">
                                    <ol>
                                        <li>Download VLC Media Player or IPTV app on your Smart TV</li>
                                        <li>Open the app and select "Add Playlist" or "Add URL"</li>
                                        <li>Enter the streaming URL provided above</li>
                                        <li>Enter your username and password when prompted</li>
                                        <li>Enjoy your channels!</li>
                                    </ol>
                                </div>
                                
                                <div class="device-instruction" id="firestick">
                                    <ol>
                                        <li>Go to Firestick home screen</li>
                                        <li>Search for "IPTV Smarters" or "VLC" in the app store</li>
                                        <li>Install the app</li>
                                        <li>Open the app and add your streaming URL</li>
                                        <li>Enter your credentials and start watching!</li>
                                    </ol>
                                </div>
                                
                                <div class="device-instruction" id="roku">
                                    <ol>
                                        <li>Go to Roku Channel Store</li>
                                        <li>Search for "IPTV Player" or similar app</li>
                                        <li>Install the app</li>
                                        <li>Add your streaming URL and credentials</li>
                                        <li>Start streaming your favorite channels!</li>
                                    </ol>
                                </div>
                                
                                <div class="device-instruction" id="mobile">
                                    <ol>
                                        <li>Download "IPTV Smarters" or "VLC" from app store</li>
                                        <li>Open the app</li>
                                        <li>Add playlist using the streaming URL</li>
                                        <li>Enter your username and password</li>
                                        <li>Enjoy streaming on your mobile device!</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="step-actions">
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i>
                            Go to Dashboard
                        </a>
                        <a href="channels.php" class="btn btn-secondary">
                            <i class="fas fa-tv"></i>
                            Browse Channels
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- All Channels Modal -->
    <div id="allChannelsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>All Channels in <?php echo htmlspecialchars($package['name']); ?></h3>
                <span class="modal-close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="channels-list">
                    <?php foreach ($packageChannels as $channel): ?>
                        <div class="channel-list-item">
                            <?php if ($channel['logo_url']): ?>
                                <img src="<?php echo htmlspecialchars($channel['logo_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($channel['name']); ?>"
                                     onerror="this.src='../images/default-channel.svg'">
                            <?php else: ?>
                                <div class="default-logo">
                                    <i class="fas fa-tv"></i>
                                </div>
                            <?php endif; ?>
                            <div class="channel-info">
                                <h4><?php echo htmlspecialchars($channel['name']); ?></h4>
                                <p><?php echo htmlspecialchars($channel['description']); ?></p>
                                <div class="channel-meta">
                                    <?php if ($channel['country']): ?>
                                        <span class="meta-item">
                                            <i class="fas fa-globe"></i>
                                            <?php echo htmlspecialchars($channel['country']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($channel['is_hd']): ?>
                                        <span class="meta-item hd">
                                            <i class="fas fa-hd-video"></i>
                                            HD
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating WhatsApp Button -->
    <div class="whatsapp-float">
        <a href="https://wa.me/254768704834?text=Hello%2C%20I%20need%20help%20with%20my%20subscription" target="_blank" class="whatsapp-btn">
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
                        <span>BingeTV</span>
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
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                        <?php if ($user): ?>
                            <li><a href="dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
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
                    <p>&copy; <?php echo date('Y'); ?> BingeTV. All rights reserved.</p>
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
    <script src="../js/main.js"></script>
    <script src="../js/subscribe.js"></script>
    
    <script>
        // Package data for JavaScript
        const packageData = {
            id: <?php echo $package['id']; ?>,
            name: '<?php echo addslashes($package['name']); ?>',
            price: <?php echo $package['price']; ?>,
            duration_days: <?php echo $package['duration_days']; ?>,
            max_devices: <?php echo $package['max_devices']; ?>
        };
        
        const userData = <?php echo $user ? json_encode($user) : 'null'; ?>;
    </script>
</body>
</html>

