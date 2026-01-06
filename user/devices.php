<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../lib/functions.php';
require_once __DIR__ . '/../../lib/session_manager.php';

// Require user to be logged in
requireLogin();

$user = getCurrentUser();
if (!$user) {
    redirect('/login', 'Please log in to continue');
}

// Get active sessions
$activeSessions = getUserActiveSessions($user['id']);

// Get user's subscription to show device limit
$subscription = getUserSubscription($user['id']);
$maxDevices = $subscription['max_devices'] ?? 1;

// Handle device logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout_device') {
    $sessionId = $_POST['session_id'] ?? 0;
    if (terminateDeviceSession($user['id'], $sessionId)) {
        redirectWithMessage('/user/devices', 'Device logged out successfully', 'success');
    } else {
        redirectWithMessage('/user/devices', 'Failed to logout device', 'error');
    }
}

$pageTitle = 'Active Devices';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $pageTitle; ?> - BingeTV
    </title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .devices-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .devices-header {
            background: linear-gradient(135deg, #8B0000, #660000);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .devices-header h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
        }

        .device-limit {
            font-size: 18px;
            opacity: 0.9;
        }

        .device-limit .current {
            font-weight: bold;
            color: <?php echo count($activeSessions) >= $maxDevices ? '#ffeb3b' : '#4caf50'; ?>;
        }

        .device-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .device-info {
            flex: 1;
        }

        .device-icon {
            font-size: 32px;
            margin-right: 20px;
            color: #8B0000;
        }

        .device-name {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .device-details {
            color: #666;
            font-size: 14px;
        }

        .device-details div {
            margin: 4px 0;
        }

        .current-device {
            background: #f0f9ff;
            border: 2px solid #0ea5e9;
        }

        .btn-logout {
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-logout:hover {
            background: #b91c1c;
        }

        .btn-logout:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../../public/includes/navigation.php'; ?>

    <main class="devices-container" style="padding-top: 100px;">
        <div class="devices-header">
            <h1><i class="fas fa-devices"></i> Active Devices</h1>
            <div class="device-limit">
                <span class="current">
                    <?php echo count($activeSessions); ?>
                </span> /
                <?php echo $maxDevices; ?> devices used
            </div>
        </div>

        <?php if (displayMessage()): ?>
            <!-- Flash message displayed -->
        <?php endif; ?>

        <?php if (empty($activeSessions)): ?>
            <div class="empty-state">
                <i class="fas fa-mobile-alt"></i>
                <h2>No Active Devices</h2>
                <p>You don't have any active sessions</p>
            </div>
        <?php else: ?>
            <?php foreach ($activeSessions as $session): ?>
                <?php
                $isCurrent = isset($_SESSION['session_token']) && $_SESSION['session_token'] === ($session['session_token'] ?? '');
                $deviceIcon = 'fa-desktop';
                switch ($session['device_type']) {
                    case 'android':
                        $deviceIcon = 'fa-tv';
                        break;
                    case 'tizen':
                    case 'webos':
                        $deviceIcon = 'fa-tv';
                        break;
                    case 'mobile':
                        $deviceIcon = 'fa-mobile-alt';
                        break;
                    case 'web':
                    default:
                        $deviceIcon = 'fa-desktop';
                        break;
                }
                ?>
                <div class="device-card <?php echo $isCurrent ? 'current-device' : ''; ?>">
                    <div style="display: flex; align-items: center; flex: 1;">
                        <i class="fas <?php echo $deviceIcon; ?> device-icon"></i>
                        <div class="device-info">
                            <div class="device-name">
                                <?php echo htmlspecialchars($session['device_name']); ?>
                                <?php if ($isCurrent): ?>
                                    <span
                                        style="background: #0ea5e9; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin-left: 8px;">Current
                                        Device</span>
                                <?php endif; ?>
                            </div>
                            <div class="device-details">
                                <div><i class="fas fa-network-wired"></i> IP:
                                    <?php echo htmlspecialchars($session['ip_address']); ?>
                                </div>
                                <div><i class="fas fa-clock"></i> Last active:
                                    <?php echo timeAgo($session['last_activity']); ?>
                                </div>
                                <div><i class="fas fa-calendar"></i> Connected:
                                    <?php echo formatDate($session['created_at'], 'M j, Y g:i A'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="logout_device">
                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                        <button type="submit" class="btn-logout" <?php echo $isCurrent ? 'disabled title="Cannot logout current device"' : ''; ?>>
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 8px;">
            <h3 style="margin-top: 0;"><i class="fas fa-info-circle"></i> Device Management Tips</h3>
            <ul style="color: #666; line-height: 1.8;">
                <li>Your plan allows <strong>
                        <?php echo $maxDevices; ?>
                    </strong> simultaneous device(s)</li>
                <li>Sessions expire after 7 days of inactivity</li>
                <li>Logout unused devices to free up slots</li>
                <li>Upgrade your plan for more device slots</li>
            </ul>
        </div>
    </main>

    <?php include __DIR__ . '/../../public/includes/footer.php'; ?>
</body>

</html>