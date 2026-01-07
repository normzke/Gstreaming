<?php
/**
 * BingeTV TiviMate Credentials Display
 * Add this section to user dashboard after subscription status
 */

// Get user's TiviMate credentials
$tivimateStmt = $conn->prepare("
    SELECT tivimate_server, tivimate_username, tivimate_password, 
           tivimate_expires_at, tivimate_active 
    FROM users 
    WHERE id = ?
");
$tivimateStmt->execute([$userId]);
$tivimateCredentials = $tivimateStmt->fetch();
?>

<!-- TiviMate Streaming Credentials -->
<div class="user-card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-satellite-dish"></i> BingeTV App Credentials</h2>
        <p style="color: var(--user-text-light); margin: 0;">Use these credentials in the BingeTV app</p>
    </div>
    <div class="card-body">
        <?php if ($tivimateCredentials && $tivimateCredentials['tivimate_active']): ?>
            <div
                style="background: rgba(139, 0, 0, 0.05); border: 2px solid rgba(139, 0, 0, 0.2); border-radius: var(--user-radius); padding: 2rem; margin-bottom: 2rem;">
                <h3 style="color: var(--user-primary); margin-bottom: 1.5rem; font-size: 1.25rem;">
                    <i class="fas fa-key"></i> Your Streaming Credentials
                </h3>

                <!-- Server URL -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; color: var(--user-text); font-weight: 600; margin-bottom: 0.5rem;">
                        <i class="fas fa-server"></i> Server URL
                    </label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" value="<?php echo htmlspecialchars($tivimateCredentials['tivimate_server']); ?>"
                            readonly id="tivimate_server"
                            style="flex: 1; padding: 0.75rem; background: white; border: 1px solid var(--user-border); border-radius: var(--user-radius); font-family: monospace; font-size: 0.875rem;">
                        <button onclick="copyToClipboard('tivimate_server')" class="btn btn-secondary"
                            style="padding: 0.75rem 1.5rem;">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>

                <!-- Username -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; color: var(--user-text); font-weight: 600; margin-bottom: 0.5rem;">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text"
                            value="<?php echo htmlspecialchars($tivimateCredentials['tivimate_username']); ?>" readonly
                            id="tivimate_username"
                            style="flex: 1; padding: 0.75rem; background: white; border: 1px solid var(--user-border); border-radius: var(--user-radius); font-family: monospace; font-size: 0.875rem;">
                        <button onclick="copyToClipboard('tivimate_username')" class="btn btn-secondary"
                            style="padding: 0.75rem 1.5rem;">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>

                <!-- Password -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; color: var(--user-text); font-weight: 600; margin-bottom: 0.5rem;">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="password"
                            value="<?php echo htmlspecialchars($tivimateCredentials['tivimate_password']); ?>" readonly
                            id="tivimate_password"
                            style="flex: 1; padding: 0.75rem; background: white; border: 1px solid var(--user-border); border-radius: var(--user-radius); font-family: monospace; font-size: 0.875rem;">
                        <button onclick="togglePassword()" class="btn btn-secondary" style="padding: 0.75rem 1.5rem;">
                            <i class="fas fa-eye" id="password_icon"></i> <span id="password_text">Show</span>
                        </button>
                        <button onclick="copyToClipboard('tivimate_password')" class="btn btn-secondary"
                            style="padding: 0.75rem 1.5rem;">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>

                <!-- Expiration -->
                <?php if ($tivimateCredentials['tivimate_expires_at']): ?>
                    <div
                        style="padding: 1rem; background: rgba(255, 170, 0, 0.1); border: 1px solid rgba(255, 170, 0, 0.3); border-radius: var(--user-radius); margin-bottom: 1.5rem;">
                        <i class="fas fa-calendar-alt"></i>
                        <strong>Expires:</strong>
                        <?php echo date('F j, Y', strtotime($tivimateCredentials['tivimate_expires_at'])); ?>
                    </div>
                <?php endif; ?>

                <!-- Download Apps -->
                <div
                    style="background: white; padding: 1.5rem; border-radius: var(--user-radius); border: 1px solid var(--user-border);">
                    <h4 style="color: var(--user-text); margin-bottom: 1rem;">
                        <i class="fas fa-download"></i> Download BingeTV Apps
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <a href="/apps/android/bingetv-android-tv.apk" class="btn btn-primary" style="text-align: center;">
                            <i class="fab fa-android"></i> Android TV
                        </a>
                        <a href="/apps/tizen/com.bingetv.app-1.0.0.tpk" class="btn btn-primary" style="text-align: center;">
                            <i class="fas fa-tv"></i> Samsung TV
                        </a>
                        <a href="/apps/webos/com.bingetv.app_1.0.0_all.ipk" class="btn btn-primary"
                            style="text-align: center;">
                            <i class="fas fa-tv"></i> LG TV
                        </a>
                    </div>
                </div>
            </div>

            <!-- Setup Instructions -->
            <div
                style="background: white; padding: 2rem; border-radius: var(--user-radius); border: 1px solid var(--user-border);">
                <h4 style="color: var(--user-text); margin-bottom: 1rem;">
                    <i class="fas fa-info-circle"></i> How to Setup
                </h4>
                <ol style="color: var(--user-text-light); line-height: 1.8; padding-left: 1.5rem;">
                    <li>Download the BingeTV app for your device</li>
                    <li>Install the app on your TV/device</li>
                    <li>Open the app and select "Xtream Codes" login</li>
                    <li>Copy and paste the credentials above</li>
                    <li>Click "Connect" and start watching!</li>
                </ol>
                <div
                    style="margin-top: 1.5rem; padding: 1rem; background: rgba(139, 0, 0, 0.05); border-radius: var(--user-radius);">
                    <p style="margin: 0; color: var(--user-text-light); font-size: 0.875rem;">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Tip:</strong> These credentials work with both the <strong>BingeTV Native App</strong> and
                        <strong>TiviMate</strong> (Premium Alternative).
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem;">
                <i class="fas fa-satellite-dish"
                    style="font-size: 4rem; color: var(--user-text-light); opacity: 0.3; margin-bottom: 1rem;"></i>
                <h3 style="color: var(--user-text); margin-bottom: 0.5rem;">No Streaming Credentials Yet</h3>
                <p style="color: var(--user-text-light); margin-bottom: 1.5rem;">
                    Your streaming credentials will be activated after subscription payment is confirmed.
                </p>
                <p style="color: var(--user-text-light); font-size: 0.875rem;">
                    <i class="fas fa-clock"></i> Usually activated within 24 hours
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function copyToClipboard(elementId) {
        const input = document.getElementById(elementId);
        input.select();
        document.execCommand('copy');

        // Show feedback
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.style.background = '#10B981';

        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.style.background = '';
        }, 2000);
    }

    function togglePassword() {
        const input = document.getElementById('tivimate_password');
        const icon = document.getElementById('password_icon');
        const text = document.getElementById('password_text');

        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
            text.textContent = 'Hide';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
            text.textContent = 'Show';
        }
    }
</script>