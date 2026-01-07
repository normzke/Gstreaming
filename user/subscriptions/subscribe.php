<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../lib/functions.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Read inputs and persist selection
// First check URL parameter, then fall back to session (for users coming from package-selection.php)
$package_id = isset($_GET['package']) ? (int) $_GET['package'] : (isset($_SESSION['selected_package']) ? (int) $_SESSION['selected_package'] : 0);
$selectedDevices = isset($_GET['devices']) ? (int) $_GET['devices'] : (isset($_SESSION['selected_devices']) ? (int) $_SESSION['selected_devices'] : 1);
$_SESSION['selected_devices'] = max(1, $selectedDevices);

// Detect action (new, renew, upgrade)
$action = isset($_GET['action']) ? $_GET['action'] : 'new';
$isRenewal = ($action === 'renew');
$isUpgrade = ($action === 'upgrade');

// Default months to 1; duration is taken from package for pricing
$selectedMonths = isset($_SESSION['selected_months']) ? (int) $_SESSION['selected_months'] : 1;

$package = null;
if ($package_id > 0) {
    $packageQuery = "SELECT * FROM packages WHERE id = ? AND is_active = true";
    $packageStmt = $conn->prepare($packageQuery);
    $packageStmt->execute([$package_id]);
    $package = $packageStmt->fetch();
}

if (!$package) {
    // Redirect to subscriptions page (user portal) if package invalid
    header('Location: /user/subscriptions#packages');
    exit();
}

// Redirect unauthenticated users to login, preserving intended destination
if (!isLoggedIn()) {
    $_SESSION['post_login_redirect'] = "user/subscriptions/subscribe.php?package=" . $package_id . "&devices=" . $_SESSION['selected_devices'] . ($action !== 'new' ? "&action=" . $action : '');
    header('Location: /login');
    exit();
}

// Get current user
$user = getCurrentUser();
$userId = $user['id'];

// Get current subscription if renewal or upgrade
$currentSubscription = null;
if ($isRenewal || $isUpgrade) {
    $subQuery = "SELECT us.*, p.name as package_name 
                 FROM user_subscriptions us 
                 JOIN packages p ON us.package_id = p.id 
                 WHERE us.user_id = ? 
                 ORDER BY us.created_at DESC 
                 LIMIT 1";
    $subStmt = $conn->prepare($subQuery);
    $subStmt->execute([$userId]);
    $currentSubscription = $subStmt->fetch();
}

// Fixed Pricing Table - matches exactly what admin sees
require_once __DIR__ . '/../../lib/pricing.php';

// Get package duration in months
$packageMonths = round($package['duration_days'] / 30);

// Enforce device limits (1-3 devices only)
$maxAllowedDevices = 3;
$deviceLimitMessage = "Maximum 3 devices per package. For more devices, please contact us for a custom package.";

if ($selectedDevices > $maxAllowedDevices) {
    $selectedDevices = $maxAllowedDevices;
    $_SESSION['device_limit_warning'] = $deviceLimitMessage;
}

if ($selectedDevices < 1) {
    $selectedDevices = 1;
}

// Get price from fixed pricing table
$totalPrice = PricingCalculator::getPackagePrice($package['duration_days'], $selectedDevices);

// Fallback if price not found
if (!$totalPrice) {
    $totalPrice = $package['price'] * $packageMonths;
}

// Calculate per month for display
$perMonth = $totalPrice / $packageMonths;

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

<!-- Page-specific styles -->
<style>
    .subscription-page {
        padding: 2rem 0;
    }

    .package-overview {
        background: white;
        border-radius: var(--user-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--user-shadow);
    }

    .package-header {
        display: flex;
        align-items: flex-start;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .package-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--user-primary), #6B0000);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        flex-shrink: 0;
    }

    .package-info h1 {
        margin: 0 0 0.5rem 0;
        color: var(--user-text);
        font-size: 2rem;
    }

    .package-description {
        color: var(--user-text-light);
        margin: 0 0 1rem 0;
    }

    .package-price {
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
    }

    .price-amount {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--user-primary);
    }

    .price-period {
        color: var(--user-text-light);
        font-size: 1rem;
    }

    .package-features {
        border-top: 1px solid var(--user-border);
        padding-top: 1.5rem;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.5rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: var(--user-bg);
        border-radius: var(--user-radius);
    }

    .feature-item i {
        font-size: 1.5rem;
        color: var(--user-primary);
    }

    .feature-item span {
        font-weight: 500;
        color: var(--user-text);
    }

    .subscription-steps {
        background: white;
        border-radius: var(--user-radius);
        padding: 2rem;
        box-shadow: var(--user-shadow);
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }

    .step-indicator {
        display: flex;
        justify-content: space-between;
        margin-bottom: 3rem;
        position: relative;
    }

    .step-indicator::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 10%;
        right: 10%;
        height: 2px;
        background: var(--user-border);
        z-index: 0;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--user-border);
        color: var(--user-text-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        transition: all 0.3s;
    }

    .step.active .step-number,
    .step.completed .step-number {
        background: var(--user-primary);
        color: white;
    }

    .step-label {
        font-size: 0.875rem;
        color: var(--user-text-light);
        font-weight: 500;
    }

    .step.active .step-label {
        color: var(--user-primary);
        font-weight: 600;
    }

    .step-content {
        display: none;
    }

    .step-content.active {
        display: block;
    }

    .step-header {
        margin-bottom: 2rem;
    }

    .step-header h2 {
        margin: 0 0 0.5rem 0;
        color: var(--user-text);
    }

    .step-header p {
        margin: 0;
        color: var(--user-text-light);
    }

    @media (max-width: 768px) {
        .package-header {
            flex-direction: column;
            text-align: center;
        }

        .package-icon {
            margin: 0 auto;
        }

        .feature-grid {
            grid-template-columns: 1fr;
        }

        .step-indicator {
            flex-wrap: wrap;
            gap: 1rem;
        }

        .step-indicator::before {
            display: none;
        }
    }

    /* Payment Section Styles */
    .payment-section {
        margin-top: 2rem;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }

    .payment-methods h3 {
        margin: 0 0 1.5rem 0;
        color: var(--user-text);
        font-size: 1.125rem;
    }

    .payment-options {
        display: grid;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .payment-option {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        border: 2px solid var(--user-border);
        border-radius: var(--user-radius);
        cursor: pointer;
        transition: all 0.3s;
        background: white;
    }

    .payment-option:hover {
        border-color: var(--user-primary);
        box-shadow: 0 4px 12px rgba(139, 0, 0, 0.1);
    }

    .payment-option.active {
        border-color: var(--user-primary);
        background: rgba(139, 0, 0, 0.05);
    }

    .payment-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--user-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--user-primary);
        flex-shrink: 0;
    }

    .payment-option.active .payment-icon {
        background: var(--user-primary);
        color: white;
    }

    .payment-info {
        flex: 1;
    }

    .payment-info h4 {
        margin: 0 0 0.25rem 0;
        color: var(--user-text);
        font-size: 1rem;
    }

    .payment-info p {
        margin: 0;
        color: var(--user-text-light);
        font-size: 0.875rem;
    }

    .payment-radio input[type="radio"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .payment-form {
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--user-text);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--user-border);
        border-radius: var(--user-radius);
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--user-primary);
        box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
    }

    .form-group small {
        display: block;
        margin-top: 0.25rem;
        color: var(--user-text-light);
        font-size: 0.875rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .payment-summary {
        background: var(--user-bg);
        border-radius: var(--user-radius);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--user-border);
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-row.total {
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--user-primary);
        padding-top: 1rem;
        margin-top: 0.5rem;
        border-top: 2px solid var(--user-border);
    }

    .payment-actions {
        display: flex;
        gap: 1rem;
        justify-content: space-between;
    }

    .payment-actions .btn {
        flex: 1;
    }

    .btn-lg {
        padding: 1rem 2rem;
        font-size: 1.125rem;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .payment-actions {
            flex-direction: column;
        }

        .payment-option {
            flex-direction: column;
            text-align: center;
        }
    }

    /* Package Summary & Step Styles */
    .step-header {
        margin-bottom: 2rem;
        text-align: center;
    }

    .step-header h2 {
        color: var(--user-text);
        font-size: 1.75rem;
        margin: 0 0 0.5rem 0;
    }

    .step-header p {
        color: var(--user-text-light);
        font-size: 1rem;
        margin: 0;
    }

    .package-selected {
        background: #E0F2FE;
        border-left: 4px solid #0284C7;
        padding: 1rem 1.5rem;
        border-radius: var(--user-radius);
        margin-bottom: 2rem;
    }

    .package-selected h3 {
        margin: 0;
        color: #075985;
        font-size: 1.125rem;
        font-weight: 600;
    }

    .package-summary {
        background: white;
        border: 1px solid var(--user-border);
        border-radius: var(--user-radius);
        padding: 1.5rem;
        margin-top: 2rem;
    }

    .package-summary h3 {
        margin: 0 0 1.5rem 0;
        color: var(--user-text);
        font-size: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--user-border);
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--user-border);
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-item span:first-child {
        color: var(--user-text-light);
        font-weight: 500;
    }

    .summary-item span:last-child {
        color: var(--user-text);
        font-weight: 600;
    }

    .summary-item.total {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 2px solid var(--user-border);
        font-size: 1.125rem;
    }

    .summary-item.total span {
        color: var(--user-primary);
        font-weight: 700;
    }

    .step-actions {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
        gap: 1rem;
    }

    .channels-list {
        margin-top: 1.5rem;
    }

    .channels-list h4 {
        margin: 0 0 1rem 0;
        color: var(--user-text);
        font-size: 1rem;
    }

    .channel-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem;
        max-height: 300px;
        overflow-y: auto;
        padding: 1rem;
        background: var(--user-bg);
        border-radius: var(--user-radius);
    }

    .channel-item {
        padding: 0.5rem;
        background: white;
        border: 1px solid var(--user-border);
        border-radius: 4px;
        font-size: 0.875rem;
        color: var(--user-text);
    }

    @media (max-width: 768px) {
        .step-actions {
            flex-direction: column;
        }

        .channel-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Paystack JS -->
<script src="https://js.paystack.co/v1/inline.js"></script>

<!-- Subscription Page Content -->
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
                        <span class="price-amount">KES <?php echo number_format($totalPrice); ?></span>
                        <span class="price-period">for <?php echo (int) $selectedMonths; ?> month(s),
                            <?php echo (int) $selectedDevices; ?> device(s)</span>
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
                        <span><?php echo $package['max_devices']; ?>
                            Device<?php echo $package['max_devices'] > 1 ? 's' : ''; ?></span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-hd-video"></i>
                        <span><?php echo json_decode($package['features'], true)['quality'] ?? 'HD'; ?>
                            Quality</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-headset"></i>
                        <span><?php echo json_decode($package['features'], true)['support'] ?? 'Email'; ?>
                            Support</span>
                    </div>
                </div>
            </div>
        </div>
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
                    <span>KES <?php echo number_format($perMonth); ?> per month</span>
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
                <h2>
                    <?php if ($isRenewal): ?>
                        Renew Your Subscription
                    <?php elseif ($isUpgrade): ?>
                        Upgrade Your Subscription
                    <?php else: ?>
                        Payment Details
                    <?php endif; ?>
                </h2>
                <p>
                    <?php if ($isRenewal): ?>
                        Extend your subscription and continue enjoying premium content
                    <?php elseif ($isUpgrade): ?>
                        Upgrade to a better package with more features
                    <?php else: ?>
                        Complete your subscription payment
                    <?php endif; ?>
                </p>

                <?php if ($isRenewal && $currentSubscription): ?>
                    <div
                        style="background: #E0F2FE; border-left: 4px solid #0284C7; padding: 1rem; border-radius: var(--user-radius); margin-top: 1rem;">
                        <p style="margin: 0; color: #075985; font-weight: 600;">
                            <i class="fas fa-info-circle"></i>
                            Current subscription ends:
                            <?php echo date('M j, Y', strtotime($currentSubscription['end_date'])); ?>
                        </p>
                        <p style="margin: 0.5rem 0 0 0; color: #075985;">
                            After renewal, your new end date will be:
                            <?php
                            $currentEnd = new DateTime($currentSubscription['end_date']);
                            $now = new DateTime();
                            $startFrom = max($currentEnd, $now);
                            $newEnd = clone $startFrom;
                            $newEnd->add(new DateInterval('P' . $package['duration_days'] . 'D'));
                            echo $newEnd->format('M j, Y');
                            ?>
                        </p>
                    </div>
                <?php elseif ($isUpgrade && $currentSubscription): ?>
                    <div
                        style="background: #F3E8FF; border-left: 4px solid #9333EA; padding: 1rem; border-radius: var(--user-radius); margin-top: 1rem;">
                        <p style="margin: 0; color: #581C87; font-weight: 600;">
                            <i class="fas fa-arrow-up"></i>
                            Upgrading from: <?php echo htmlspecialchars($currentSubscription['package_name']); ?>
                        </p>
                        <p style="margin: 0.5rem 0 0 0; color: #581C87;">
                            New package: <?php echo htmlspecialchars($package['name']); ?>
                        </p>
                    </div>
                <?php endif; ?>
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
                                <p>Pay using M-PESA mobile money (Kenya)</p>
                            </div>
                            <div class="payment-radio">
                                <input type="radio" name="payment_method" value="mpesa" checked>
                            </div>
                        </div>

                        <div class="payment-option" data-method="paystack">
                            <div class="payment-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="payment-info">
                                <h4>Card / International</h4>
                                <p>Pay with Visa, Mastercard, or Apple Pay</p>
                            </div>
                            <div class="payment-radio">
                                <input type="radio" name="payment_method" value="paystack">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="payment-form" id="mpesa-form">
                    <div class="form-group">
                        <label for="phone_number">M-PESA Phone Number</label>
                        <input type="tel" id="phone_number" name="phone_number"
                            value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="254XXXXXXXXX">
                        <small>Enter your M-PESA registered phone number</small>
                    </div>
                </div>

                <div class="payment-form" id="paystack-form" style="display: none;">
                    <div class="form-group">
                        <p style="color: #636e72; font-size: 0.9rem; margin-bottom: 1rem;">
                            <i class="fas fa-shield-alt"></i> Secure payment processing by Paystack. Your card
                            details are never stored on our servers.
                        </p>
                    </div>
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
                        <span>KES <?php echo number_format($perMonth); ?></span>
                    </div>
                </div>

                <div class="payment-actions">
                    <button type="button" class="btn btn-secondary" onclick="goBack()">
                        <i class="fas fa-arrow-left"></i>
                        Back
                    </button>
                    <button type="button" class="btn btn-primary btn-lg" id="pay-button" onclick="handlePayment()">
                        <i class="fas fa-mobile-alt" id="pay-icon"></i>
                        <span id="pay-text">Pay KES <?php echo number_format($totalPrice); ?></span>
                    </button>
                </div>

                <!-- Manual Payment Option -->
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px dashed #e0e0e0;">
                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <p style="color: #666; font-weight: 600;">OR</p>
                    </div>

                    <div
                        style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                        <h4 style="margin: 0 0 1rem 0; color: #856404;">
                            <i class="fas fa-university"></i> Manual Payment Option
                        </h4>
                        <div style="background: white; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                            <p style="margin: 0.25rem 0; color: #333;"><strong>Bank:</strong> The Family
                                Bank</p>
                            <p style="margin: 0.25rem 0; color: #333;"><strong>Paybill Number:</strong>
                                <span style="color: #8B0000; font-size: 1.3em; font-weight: bold;">222111</span>
                            </p>
                            <p style="margin: 0.25rem 0; color: #333;"><strong>Account Number:</strong>
                                <span style="color: #8B0000; font-size: 1.3em; font-weight: bold;">085000092737</span>
                            </p>
                            <p style="margin: 0.25rem 0; color: #333;"><strong>Amount:</strong> <span
                                    style="color: #8B0000; font-size: 1.3em; font-weight: bold;">KES
                                    <?php echo number_format($totalPrice, 0); ?></span></p>
                        </div>
                        <p style="margin: 0; color: #856404; font-size: 0.9rem;">
                            <i class="fas fa-info-circle"></i> Go to M-Pesa → Lipa Na M-Pesa → Pay Bill →
                            Enter details above
                        </p>
                    </div>

                    <button type="button" class="btn btn-secondary btn-lg" onclick="proceedToManualPayment()"
                        style="width: 100%; background: #6c757d;">
                        <i class="fas fa-paste"></i>
                        Already Paid? Submit M-PESA Confirmation
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
                        <li>Enter Amount: <strong>KES <?php echo number_format($totalPrice); ?></strong>
                        </li>
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
                        <span>KES <?php echo number_format($totalPrice); ?></span>
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
                            <li>Download the <strong>BingeTV Native App</strong> for Samsung Tizen or LG WebOS
                            </li>
                            <li>Open the app and select "Xtream Codes" login</li>
                            <li>Enter the Server URL, Username, and Password provided above</li>
                            <li>Alternatively, download <strong>TiviMate</strong> for a premium experience</li>
                            <li>Enjoy 500+ premium channels!</li>
                        </ol>
                    </div>

                    <div class="device-instruction" id="firestick">
                        <ol>
                            <li>Download the <strong>BingeTV Android APK</strong> from our <a href="/apps"
                                    target="_blank">apps page</a></li>
                            <li>Install using the "Downloader" app on your Firestick</li>
                            <li>Login using your BingeTV credentials</li>
                            <li><strong>TiviMate</strong> is also highly recommended for Firestick users</li>
                            <li>Start watching live Premier League and more!</li>
                        </ol>
                    </div>

                    <div class="device-instruction" id="roku">
                        <ol>
                            <li>Roku has limited support for third-party IPTV apps</li>
                            <li>We recommend using an <strong>Android TV Box</strong> or
                                <strong>Firestick</strong> for the best experience
                            </li>
                            <li>If you must use Roku, search for "IPTV Player" and use the M3U link from your
                                dashboard</li>
                            <li>Contact support if you need help with Roku setup</li>
                        </ol>
                    </div>

                    <div class="device-instruction" id="mobile">
                        <ol>
                            <li>Download <strong>IPTV Smarters</strong> or <strong>VLC</strong> from the App
                                Store or Play Store</li>
                            <li>Open the app and add a "New User" using Xtream Codes</li>
                            <li>Enter your BingeTV credentials shown above</li>
                            <li>Watch your favorite channels on the go!</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="step-actions">
            <a href="dashboard" class="btn btn-primary">
                <i class="fas fa-tachometer-alt"></i>
                Go to Dashboard
            </a>
            <a href="channels" class="btn btn-secondary">
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
    <a href="https://wa.me/254768704834?text=Hello%2C%20I%20need%20help%20with%20my%20subscription" target="_blank"
        class="whatsapp-btn">
        <i class="fab fa-whatsapp"></i>
        <span class="whatsapp-text">Need Help?</span>
    </a>
</div>



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

    // Manual payment function
    function proceedToManualPayment() {
        // Create payment record first, then redirect to manual submission
        window.location.href = '../payments/submit-mpesa.php?package_id=<?php echo $package_id; ?>&amount=<?php echo $totalPrice; ?>&devices=<?php echo $selectedDevices; ?>';
    }
</script>

<style>
    /* Mobile Responsive Styles for Subscribe Page */
    @media (max-width: 1024px) {
        .package-details {
            grid-template-columns: 1fr !important;
        }

        .payment-section {
            grid-template-columns: 1fr !important;
        }
    }

    @media (max-width: 768px) {
        .subscription-page {
            padding: 1rem !important;
        }

        .package-overview,
        .subscription-steps {
            margin: 0 0.5rem 2rem !important;
        }

        .step-indicator {
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
        }

        .step {
            flex: 1 1 calc(50% - 0.5rem) !important;
            min-width: 120px !important;
        }

        .step-label {
            font-size: 0.8rem !important;
        }

        .channels-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)) !important;
        }

        .payment-actions {
            flex-direction: column !important;
            gap: 1rem !important;
        }

        .payment-actions .btn {
            width: 100% !important;
        }

        .auth-tabs {
            flex-direction: column !important;
        }

        .auth-tab {
            width: 100% !important;
        }
    }

    @media (max-width: 480px) {
        .package-header h1 {
            font-size: 1.5rem !important;
        }

        .step-number {
            width: 30px !important;
            height: 30px !important;
            font-size: 0.9rem !important;
        }

        .btn-lg {
            font-size: 1rem !important;
            padding: 0.75rem 1.5rem !important;
        }

        .payment-summary {
            padding: 1rem !important;
        }

        .summary-row {
            font-size: 0.9rem !important;
        }
    }
</style>

<?php require_once '../includes/footer.php'; ?>