<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();
$user = getCurrentUser();
$userId = $user['id'];

// Get user's current subscription
$subscriptionQuery = "SELECT us.*, p.name as package_name, p.price, p.duration_days,
                      CASE 
                          WHEN us.end_date > NOW() THEN 'active'
                          WHEN us.end_date <= NOW() THEN 'expired'
                          ELSE us.status
                      END as current_status,
                      EXTRACT(DAY FROM (us.end_date - NOW())) as days_remaining
                      FROM user_subscriptions us
                      JOIN packages p ON us.package_id = p.id
                      WHERE us.user_id = ?
                      ORDER BY us.created_at DESC
                      LIMIT 1";
$subStmt = $conn->prepare($subscriptionQuery);
$subStmt->execute([$userId]);
$currentSubscription = $subStmt->fetch();

// Get all available packages
$packagesQuery = "SELECT * FROM packages WHERE is_active = true ORDER BY price ASC";
$packagesStmt = $conn->query($packagesQuery);
$packages = $packagesStmt->fetchAll();

// Get subscription history
$historyQuery = "SELECT us.*, p.name as package_name, p.price, p.duration_days
                 FROM user_subscriptions us
                 JOIN packages p ON us.package_id = p.id
                 WHERE us.user_id = ?
                 ORDER BY us.created_at DESC";
$historyStmt = $conn->prepare($historyQuery);
$historyStmt->execute([$userId]);
$subscriptionHistory = $historyStmt->fetchAll();

$page_title = 'My Subscriptions';
include __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner for New Users -->
<?php if (!$currentSubscription || $currentSubscription['current_status'] !== 'active'): ?>
    <div style="background: linear-gradient(135deg, #8B0000, #660000); color: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">
            <i class="fas fa-star"></i>
        </div>
        <h2 style="margin: 0 0 1rem 0; font-size: 1.75rem;">
            Welcome to BingeTV, <?php echo htmlspecialchars($user['first_name']); ?>! 🎉
        </h2>
        <p style="margin: 0 0 1.5rem 0; opacity: 0.9; font-size: 1.1rem;">
            Choose your perfect package and start streaming premium content today!
        </p>
        <p style="margin: 0; opacity: 0.8; font-size: 0.9rem;">
            <i class="fas fa-arrow-down"></i> Browse our packages below and subscribe now
        </p>
    </div>
<?php endif; ?>

<div class="user-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-credit-card"></i>
            Current Subscription
        </h2>
    </div>
    <div class="card-body">
        <?php if ($currentSubscription && $currentSubscription['current_status'] === 'active'): ?>
            <div style="background: linear-gradient(135deg, #10B981, #059669); color: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h3 style="margin: 0 0 0.5rem 0; font-size: 1.5rem;">
                            <?php echo htmlspecialchars($currentSubscription['package_name']); ?>
                        </h3>
                        <p style="margin: 0; opacity: 0.9;">
                            <i class="fas fa-check-circle"></i> Active Subscription
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 2rem; font-weight: bold;">
                            <?php echo (int)$currentSubscription['days_remaining']; ?> Days
                        </div>
                        <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">
                            Remaining
                        </p>
                    </div>
                </div>
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.2);">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <div>
                            <p style="margin: 0; opacity: 0.9; font-size: 0.85rem;">Start Date</p>
                            <p style="margin: 0; font-weight: 600;"><?php echo date('M d, Y', strtotime($currentSubscription['start_date'])); ?></p>
                        </div>
                        <div>
                            <p style="margin: 0; opacity: 0.9; font-size: 0.85rem;">End Date</p>
                            <p style="margin: 0; font-weight: 600;"><?php echo date('M d, Y', strtotime($currentSubscription['end_date'])); ?></p>
                        </div>
                        <div>
                            <p style="margin: 0; opacity: 0.9; font-size: 0.85rem;">Duration</p>
                            <p style="margin: 0; font-weight: 600;"><?php echo $currentSubscription['duration_days']; ?> Days</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="/user/channels" class="btn btn-primary">
                    <i class="fas fa-tv"></i>
                    Watch Channels
                </a>
                <a href="/user/subscriptions/subscribe?package=<?php echo $currentSubscription['package_id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-sync"></i>
                    Renew Subscription
                </a>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem 1rem;">
                <div style="font-size: 4rem; color: #CBD5E0; margin-bottom: 1rem;">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3 style="color: var(--user-text); margin-bottom: 0.5rem;">No Active Subscription</h3>
                <p style="color: var(--user-text-light); margin-bottom: 2rem;">
                    Subscribe to a package to start streaming premium content
                </p>
                <a href="#packages" class="btn btn-primary" onclick="document.getElementById('packages').scrollIntoView({behavior: 'smooth'});">
                    <i class="fas fa-plus"></i>
                    Choose a Package
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Available Packages -->
<div class="user-card" id="packages">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-box-open"></i>
            Available Packages
        </h2>
    </div>
    <div class="card-body">
        <!-- Device Selection Tabs -->
        <div style="margin-bottom: 2rem; text-align: center;">
            <h3 style="margin-bottom: 1rem; color: var(--user-text);">Select Number of Devices</h3>
            <div style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;">
                <button class="device-tab-btn active" data-devices="1" style="padding: 0.75rem 1.5rem; border: 2px solid var(--user-primary); background: var(--user-primary); color: white; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                    <i class="fas fa-mobile-alt"></i> 1 Device
                </button>
                <button class="device-tab-btn" data-devices="2" style="padding: 0.75rem 1.5rem; border: 2px solid var(--user-border); background: white; color: var(--user-text); border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                    <i class="fas fa-laptop"></i> 2 Devices
                </button>
                <button class="device-tab-btn" data-devices="3" style="padding: 0.75rem 1.5rem; border: 2px solid var(--user-border); background: white; color: var(--user-text); border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                    <i class="fas fa-tv"></i> 3 Devices
                </button>
                <button class="device-tab-btn custom-btn" data-devices="custom" style="padding: 0.75rem 1.5rem; border: 2px dashed #8B0000; background: rgba(139, 0, 0, 0.05); color: #8B0000; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                    <i class="fas fa-users"></i> Custom (4+)
                </button>
            </div>
            <p style="margin-top: 1rem; color: var(--user-text-light); font-size: 0.9rem;">
                <i class="fas fa-info-circle"></i> Prices update based on number of devices selected. Need 4+ devices? <a href="/user/support" style="color: #8B0000; font-weight: 600;">Contact us</a>
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;" id="packagesGrid">
            <?php foreach ($packages as $package): 
                $months = round($package['duration_days'] / 30);
                $isCurrentPackage = $currentSubscription && $currentSubscription['package_id'] == $package['id'];
            ?>
                <div style="border: 2px solid <?php echo $isCurrentPackage ? 'var(--user-primary)' : 'var(--user-border)'; ?>; border-radius: 12px; padding: 1.5rem; position: relative; transition: all 0.3s ease;">
                    <?php if ($isCurrentPackage): ?>
                        <div style="position: absolute; top: -10px; right: 20px; background: var(--user-primary); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                            CURRENT
                        </div>
                    <?php endif; ?>
                    
                    <h3 style="margin: 0 0 1rem 0; color: var(--user-text); font-size: 1.25rem;">
                        <?php echo htmlspecialchars($package['name']); ?>
                    </h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--user-primary);" class="package-price" data-base-price="<?php echo $package['price']; ?>" data-months="<?php echo $months; ?>">
                            KES <span class="price-amount"><?php echo number_format($package['price'], 0); ?></span>
                        </div>
                        <div style="color: var(--user-text-light); font-size: 0.9rem;">
                            <?php echo $months; ?> Month<?php echo $months > 1 ? 's' : ''; ?> • <span class="device-count">1 Device</span>
                        </div>
                    </div>
                    
                    <?php if ($package['description']): ?>
                        <p style="color: var(--user-text-light); margin-bottom: 1.5rem; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($package['description']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <a href="/user/subscriptions/subscribe?package=<?php echo $package['id']; ?>&devices=1" class="btn btn-primary subscribe-btn" style="width: 100%; text-align: center;" data-package-id="<?php echo $package['id']; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        Subscribe Now
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Subscription History -->
<?php if (count($subscriptionHistory) > 0): ?>
<div class="user-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-history"></i>
            Subscription History
        </h2>
    </div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Package</th>
                        <th>Duration</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscriptionHistory as $sub): 
                        $status = strtotime($sub['end_date']) > time() ? 'active' : 'expired';
                        $statusColor = $status === 'active' ? '#10B981' : '#6B7280';
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($sub['package_name']); ?></strong></td>
                            <td><?php echo $sub['duration_days']; ?> days</td>
                            <td><?php echo date('M d, Y', strtotime($sub['start_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($sub['end_date'])); ?></td>
                            <td>
                                <span style="background: <?php echo $statusColor; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Device selection and pricing logic - Fixed Pricing Table
document.addEventListener('DOMContentLoaded', function() {
    const deviceButtons = document.querySelectorAll('.device-tab-btn');
    const packagePrices = document.querySelectorAll('.package-price');
    const subscribeButtons = document.querySelectorAll('.subscribe-btn');
    let selectedDevices = 1;

    // Fixed pricing table based on devices and duration
    const pricingTable = {
        1: { 1: 2500, 6: 14000, 12: 28000 },      // 1 Device
        2: { 1: 4500, 6: 27000, 12: 54000 },      // 2 Devices  
        3: { 1: 6500, 6: 39000, 12: 78000 }       // 3 Devices
    };

    // Get price from fixed table
    function getPrice(devices, months) {
        // Normalize months to available tiers
        let tier = 1;
        if (months >= 12) {
            tier = 12;
        } else if (months >= 6) {
            tier = 6;
        }
        
        return pricingTable[devices] && pricingTable[devices][tier] 
            ? pricingTable[devices][tier] 
            : 0;
    }

    // Update all package prices
    function updatePrices(devices) {
        selectedDevices = devices;
        
        packagePrices.forEach(priceElement => {
            const months = parseInt(priceElement.dataset.months);
            const finalPrice = getPrice(devices, months);
            const priceAmount = priceElement.querySelector('.price-amount');
            if (priceAmount) {
                priceAmount.textContent = finalPrice.toLocaleString('en-KE');
            }
        });

        // Update device count display
        const deviceCountElements = document.querySelectorAll('.device-count');
        deviceCountElements.forEach(el => {
            el.textContent = `${devices} Device${devices > 1 ? 's' : ''}`;
        });

        // Update subscribe button links
        subscribeButtons.forEach(btn => {
            const packageId = btn.dataset.packageId;
            btn.href = `/user/subscriptions/subscribe.php?package=${packageId}&devices=${devices}`;
        });
    }

    // Handle device button clicks
    deviceButtons.forEach(button => {
        button.addEventListener('click', function() {
            const devicesValue = this.dataset.devices;
            
            // Handle "Custom" option
            if (devicesValue === 'custom') {
                window.location.href = '/user/support.php?inquiry=custom_package';
                return;
            }
            
            // Remove active class from all buttons
            deviceButtons.forEach(btn => {
                btn.classList.remove('active');
                if (!btn.classList.contains('custom-btn')) {
                    btn.style.background = 'white';
                    btn.style.color = 'var(--user-text)';
                    btn.style.borderColor = 'var(--user-border)';
                }
            });

            // Add active class to clicked button
            this.classList.add('active');
            this.style.background = 'var(--user-primary)';
            this.style.color = 'white';
            this.style.borderColor = 'var(--user-primary)';

            // Update prices
            const devices = parseInt(devicesValue);
            updatePrices(devices);
        });
    });

    // Initialize with 1 device
    updatePrices(1);
});
</script>

<style>
/* Mobile Responsive Styles for Subscriptions Page */
@media (max-width: 768px) {
    .device-tabs {
        flex-direction: column !important;
        gap: 0.5rem !important;
    }
    
    .device-tab-btn {
        width: 100% !important;
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr !important;
    }
    
    .user-table {
        font-size: 0.85rem;
    }
    
    .user-table th,
    .user-table td {
        padding: 0.5rem;
    }
    
    /* Make table scrollable on mobile */
    .card-body {
        overflow-x: auto;
    }
}

@media (max-width: 480px) {
    .device-tab-btn {
        font-size: 0.9rem !important;
        padding: 0.6rem 1rem !important;
    }
    
    .package-price {
        font-size: 1.5rem !important;
    }
    
    .btn {
        padding: 0.6rem 1rem !important;
        font-size: 0.9rem !important;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>

