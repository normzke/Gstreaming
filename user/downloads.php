<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$user = getCurrentUser();
$page_title = 'Download Apps';
include __DIR__ . '/includes/header.php';
?>

<!-- Download Section -->
<div class="user-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-satellite-dish"></i>
            Native BingeTV Apps
        </h2>
    </div>
    <div class="card-body">
        <p style="text-align: center; color: var(--user-text-light); margin-bottom: 2rem; font-size: 1.1rem;">
            Download our custom-built native apps for 100% feature parity and the best streaming experience
        </p>

        <!-- Platform Grid -->
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <!-- Android TV -->
            <div style="background: white; border: 2px solid var(--user-border); border-radius: 12px; padding: 1.5rem; text-align: center; transition: all 0.3s;"
                onmouseover="this.style.borderColor='var(--user-primary)'; this.style.transform='translateY(-5px)'"
                onmouseout="this.style.borderColor='var(--user-border)'; this.style.transform='translateY(0)'">
                <div style="font-size: 3rem; color: var(--user-primary); margin-bottom: 0.5rem;">
                    <i class="fab fa-android"></i>
                </div>
                <p style="margin: 0; color: var(--user-text); font-weight: 600; font-size: 1.2rem;">Android TV / Fire TV
                </p>
                <p style="margin: 0.5rem 0 1.5rem 0; color: var(--user-text-light); font-size: 0.85rem;">For Android TV
                    Boxes, Sony, TCL, Philips TVs & Amazon Firestick</p>
                <a href="/apps/android/bingetv-android-tv.apk" class="btn btn-primary" download
                    style="width: 100%; justify-content: center;">
                    <i class="fas fa-download"></i> Download APK
                </a>
            </div>

            <!-- LG WebOS -->
            <div style="background: white; border: 2px solid var(--user-border); border-radius: 12px; padding: 1.5rem; text-align: center; transition: all 0.3s;"
                onmouseover="this.style.borderColor='var(--user-primary)'; this.style.transform='translateY(-5px)'"
                onmouseout="this.style.borderColor='var(--user-border)'; this.style.transform='translateY(0)'">
                <div style="font-size: 3rem; color: var(--user-primary); margin-bottom: 0.5rem;">
                    <i class="fas fa-tv"></i>
                </div>
                <p style="margin: 0; color: var(--user-text); font-weight: 600; font-size: 1.2rem;">LG WebOS</p>
                <p style="margin: 0.5rem 0 1.5rem 0; color: var(--user-text-light); font-size: 0.85rem;">For LG Smart
                    TVs (webOS 4.0+)</p>
                <a href="/apps/webos/com.bingetv.app_1.0.0_all.ipk" class="btn btn-primary" download
                    style="width: 100%; justify-content: center;">
                    <i class="fas fa-download"></i> Download IPK
                </a>
            </div>

            <!-- Samsung Tizen -->
            <div style="background: white; border: 2px solid var(--user-border); border-radius: 12px; padding: 1.5rem; text-align: center; transition: all 0.3s;"
                onmouseover="this.style.borderColor='var(--user-primary)'; this.style.transform='translateY(-5px)'"
                onmouseout="this.style.borderColor='var(--user-border)'; this.style.transform='translateY(0)'">
                <div style="font-size: 3rem; color: var(--user-primary); margin-bottom: 0.5rem;">
                    <i class="fas fa-tv"></i>
                </div>
                <p style="margin: 0; color: var(--user-text); font-weight: 600; font-size: 1.2rem;">Samsung Tizen</p>
                <p style="margin: 0.5rem 0 1.5rem 0; color: var(--user-text-light); font-size: 0.85rem;">For Samsung
                    Smart TVs (Tizen 6.0+)</p>
                <a href="/apps/tizen/com.bingetv.app-1.0.0.tpk" class="btn btn-primary" download
                    style="width: 100%; justify-content: center;">
                    <i class="fas fa-download"></i> Download TPK
                </a>
            </div>
        </div>

        <!-- Alternative App Section -->
        <div
            style="background: #f8f9fa; border-radius: 12px; padding: 2rem; margin-top: 1rem; border: 1px dashed var(--user-border);">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; justify-content: center;">
                <i class="fas fa-info-circle" style="color: var(--user-primary); font-size: 1.5rem;"></i>
                <h3 style="margin: 0; color: var(--user-text);">Alternative Player</h3>
            </div>
            <p style="text-align: center; color: var(--user-text-light); margin-bottom: 2rem;">
                If our native BingeTV app is not compatible with your device, you can use <strong>TiviMate8KPro</strong>
                as an alternative.
            </p>

            <div
                style="max-width: 500px; margin: 0 auto; background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--user-border);">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="font-size: 2rem; color: var(--user-primary);">
                        <i class="fab fa-android"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; color: var(--user-text);">TiviMate8KPro</h4>
                        <p style="margin: 0; color: var(--user-text-light); font-size: 0.9rem;">Android Third-Party
                            Player</p>
                    </div>
                </div>
                <a href="/apps/alternatives/TiviMate8KPro.apk" class="btn btn-secondary" download
                    style="width: 100%; justify-content: center;">
                    <i class="fas fa-download"></i> Download TiviMate APK
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Setup Instructions -->
<div class="user-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-question-circle"></i>
            Installation Guide
        </h2>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <!-- Android/Fire TV Instructions -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                <h4 style="margin: 0 0 1rem 0; color: var(--user-text);">
                    <i class="fab fa-android"></i> Android TV / Fire TV
                </h4>
                <ol style="margin: 0; padding-left: 1.5rem; color: var(--user-text-light); font-size: 0.95rem;">
                    <li>Download the APK file to a USB drive or your device</li>
                    <li>Go to <strong>Settings > Security & Restrictions</strong></li>
                    <li>Enable <strong>Unknown Sources</strong></li>
                    <li>Install the APK using a File Manager</li>
                    <li>Open <strong>BingeTV</strong> and log in with your credentials</li>
                </ol>
            </div>

            <!-- LG WebOS Instructions -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                <h4 style="margin: 0 0 1rem 0; color: var(--user-text);">
                    <i class="fas fa-tv"></i> LG Smart TVs
                </h4>
                <ol style="margin: 0; padding-left: 1.5rem; color: var(--user-text-light); font-size: 0.95rem;">
                    <li>Download the IPK file to a USB drive</li>
                    <li>Go to <strong>Settings > General > About This TV</strong></li>
                    <li>Enable <strong>Developer Mode</strong></li>
                    <li>Insert USB and install via File Manager</li>
                    <li>Launch <strong>BingeTV</strong> from your apps bar</li>
                </ol>
            </div>

            <!-- Samsung Tizen Instructions -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                <h4 style="margin: 0 0 1rem 0; color: var(--user-text);">
                    <i class="fas fa-tv"></i> Samsung Smart TVs
                </h4>
                <ol style="margin: 0; padding-left: 1.5rem; color: var(--user-text-light); font-size: 0.95rem;">
                    <li>Download the TPK file to a USB drive</li>
                    <li>Go to <strong>Settings > Support > Device Care</strong></li>
                    <li>Turn on <strong>Developer Mode</strong></li>
                    <li>Navigate to the file on USB and install</li>
                    <li>Open <strong>BingeTV</strong> from your Smart Hub</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Need Help -->
<div
    style="text-align: center; margin-top: 2rem; padding: 2.5rem; background: linear-gradient(135deg, #8B0000, #4a0000); border-radius: 15px; color: white; box-shadow: 0 10px 20px rgba(139,0,0,0.2);">
    <h3 style="margin: 0 0 1rem 0; font-size: 1.8rem; font-family: 'Orbitron', sans-serif;">Need Help with Setup?</h3>
    <p style="margin: 0 0 2rem 0; opacity: 0.9; font-size: 1.1rem;">Our technical team is available 24/7 to help you get
        started on any device.</p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="/user/support" class="btn"
            style="background: white; color: #8B0000; padding: 1rem 2rem; text-decoration: none; border-radius: 8px; font-weight: 700; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <i class="fas fa-headset"></i> Contact Support
        </a>
        <a href="https://wa.me/254768704834" target="_blank" class="btn"
            style="background: #25D366; color: white; padding: 1rem 2rem; text-decoration: none; border-radius: 8px; font-weight: 700; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <i class="fab fa-whatsapp"></i> Chat on WhatsApp
        </a>
    </div>
</div>

<style>
    /* Mobile Responsive for Downloads Page */
    @media (max-width: 768px) {
        .card-body>div {
            grid-template-columns: 1fr !important;
        }
    }

    @media (max-width: 480px) {
        .card-body {
            padding: 1rem !important;
        }

        .btn {
            font-size: 0.9rem !important;
            padding: 0.75rem 1rem !important;
        }
    }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>