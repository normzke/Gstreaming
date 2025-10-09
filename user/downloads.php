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
            <i class="fas fa-download"></i>
            Download Ibo Player Pro
        </h2>
    </div>
    <div class="card-body">
        <p style="text-align: center; color: var(--user-text-light); margin-bottom: 2rem; font-size: 1.1rem;">
            Download the Ibo Player Pro app to start streaming on your preferred device
        </p>

        <!-- App Store Buttons -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <!-- Google Play -->
            <a href="https://play.google.com/store/apps/details?id=ibpro.smart.player" target="_blank" style="text-decoration: none;">
                <div style="background: white; border: 2px solid var(--user-border); border-radius: 12px; padding: 1.5rem; text-align: center; transition: all 0.3s; cursor: pointer;" onmouseover="this.style.borderColor='var(--user-primary)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.borderColor='var(--user-border)'; this.style.transform='translateY(0)'">
                    <img src="/public/images/store-google.png" alt="Google Play Store" style="max-width: 150px; height: auto; margin-bottom: 0.5rem;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22150%22 height=%2250%22%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EGoogle Play%3C/text%3E%3C/svg%3E'">
                    <p style="margin: 0; color: var(--user-text); font-weight: 600;">Google Play</p>
                    <p style="margin: 0.25rem 0 0 0; color: var(--user-text-light); font-size: 0.85rem;">Android Phones & Tablets</p>
                </div>
            </a>

            <!-- Apple App Store -->
            <a href="https://apps.apple.com/app/ibo-pro-player/id6449647925" target="_blank" style="text-decoration: none;">
                <div style="background: white; border: 2px solid var(--user-border); border-radius: 12px; padding: 1.5rem; text-align: center; transition: all 0.3s; cursor: pointer;" onmouseover="this.style.borderColor='var(--user-primary)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.borderColor='var(--user-border)'; this.style.transform='translateY(0)'">
                    <img src="/public/images/store-apple.png" alt="Apple App Store" style="max-width: 150px; height: auto; margin-bottom: 0.5rem;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22150%22 height=%2250%22%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EApp Store%3C/text%3E%3C/svg%3E'">
                    <p style="margin: 0; color: var(--user-text); font-weight: 600;">Apple App Store</p>
                    <p style="margin: 0.25rem 0 0 0; color: var(--user-text-light); font-size: 0.85rem;">iPhone & iPad</p>
                </div>
            </a>

            <!-- LG Store -->
            <a href="https://us.lgappstv.com/main/tvapp/detail?appId=1209143" target="_blank" style="text-decoration: none;">
                <div style="background: white; border: 2px solid var(--user-border); border-radius: 12px; padding: 1.5rem; text-align: center; transition: all 0.3s; cursor: pointer;" onmouseover="this.style.borderColor='var(--user-primary)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.borderColor='var(--user-border)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; color: var(--user-primary); margin-bottom: 0.5rem;">
                        <i class="fas fa-tv"></i>
                    </div>
                    <p style="margin: 0; color: var(--user-text); font-weight: 600;">LG Store</p>
                    <p style="margin: 0.25rem 0 0 0; color: var(--user-text-light); font-size: 0.85rem;">LG Smart TVs</p>
                </div>
            </a>

            <!-- Roku Store -->
            <a href="https://channelstore.roku.com/details/8bb2a96953173808e85902295304a2e1:8490a219fcbeecc63b4cc16b6ba9be93/ibo-player-pro" target="_blank" style="text-decoration: none;">
                <div style="background: white; border: 2px solid var(--user-border); border-radius: 12px; padding: 1.5rem; text-align: center; transition: all 0.3s; cursor: pointer;" onmouseover="this.style.borderColor='var(--user-primary)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.borderColor='var(--user-border)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; color: var(--user-primary); margin-bottom: 0.5rem;">
                        <i class="fas fa-stream"></i>
                    </div>
                    <p style="margin: 0; color: var(--user-text); font-weight: 600;">Roku Store</p>
                    <p style="margin: 0.25rem 0 0 0; color: var(--user-text-light); font-size: 0.85rem;">Roku Devices</p>
                </div>
            </a>

            <!-- Windows Store -->
            <a href="https://www.microsoft.com/store/apps/9MSNK97XPVRK" target="_blank" style="text-decoration: none;">
                <div style="background: white; border: 2px solid var(--user-border); border-radius: 12px; padding: 1.5rem; text-align: center; transition: all 0.3s; cursor: pointer;" onmouseover="this.style.borderColor='var(--user-primary)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.borderColor='var(--user-border)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; color: var(--user-primary); margin-bottom: 0.5rem;">
                        <i class="fab fa-windows"></i>
                    </div>
                    <p style="margin: 0; color: var(--user-text); font-weight: 600;">Microsoft Store</p>
                    <p style="margin: 0.25rem 0 0 0; color: var(--user-text-light); font-size: 0.85rem;">Windows Desktop</p>
                </div>
            </a>
        </div>

        <!-- Direct Downloads -->
        <div style="background: #f8f9fa; border-radius: 12px; padding: 2rem; margin-top: 2rem;">
            <h3 style="margin: 0 0 1.5rem 0; color: var(--user-text); text-align: center;">
                <i class="fas fa-download"></i> Direct Downloads
            </h3>

            <!-- Android/Fire TV APK -->
            <div style="background: white; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; border: 1px solid var(--user-border);">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="font-size: 2rem; color: var(--user-primary);">
                        <i class="fab fa-android"></i>
                    </div>
                    <div style="flex: 1;">
                        <h4 style="margin: 0; color: var(--user-text);">Android & Amazon Fire TV</h4>
                        <p style="margin: 0.25rem 0 0 0; color: var(--user-text-light); font-size: 0.9rem;">APK Installation</p>
                    </div>
                </div>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                    <p style="margin: 0 0 0.5rem 0; color: var(--user-text); font-weight: 600;">Download Link:</p>
                    <a href="https://iboproapp.com/ibopro.apk" target="_blank" style="color: var(--user-primary); word-break: break-all; text-decoration: none; font-weight: 600;">
                        https://iboproapp.com/ibopro.apk
                    </a>
                </div>
                <a href="https://iboproapp.com/ibopro.apk" target="_blank" class="btn btn-primary" style="width: 100%; text-align: center;">
                    <i class="fas fa-download"></i> Download APK
                </a>
            </div>

            <!-- Samsung TV -->
            <div style="background: white; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; border: 1px solid var(--user-border);">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="font-size: 2rem; color: var(--user-primary);">
                        <i class="fas fa-tv"></i>
                    </div>
                    <div style="flex: 1;">
                        <h4 style="margin: 0; color: var(--user-text);">Samsung Smart TV</h4>
                        <p style="margin: 0.25rem 0 0 0; color: var(--user-text-light); font-size: 0.9rem;">Tizen OS App</p>
                    </div>
                </div>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                    <p style="margin: 0 0 0.5rem 0; color: var(--user-text); font-weight: 600;">Download Link:</p>
                    <a href="https://shorturl.at/nAOUY" target="_blank" style="color: var(--user-primary); word-break: break-all; text-decoration: none; font-weight: 600;">
                        https://shorturl.at/nAOUY
                    </a>
                </div>
                <a href="https://shorturl.at/nAOUY" target="_blank" class="btn btn-primary" style="width: 100%; text-align: center;">
                    <i class="fas fa-download"></i> Download for Samsung TV
                </a>
            </div>

            <!-- Windows Desktop -->
            <div style="background: white; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; border: 1px solid var(--user-border);">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="font-size: 2rem; color: var(--user-primary);">
                        <i class="fab fa-windows"></i>
                    </div>
                    <div style="flex: 1;">
                        <h4 style="margin: 0; color: var(--user-text);">Windows Desktop</h4>
                        <p style="margin: 0.25rem 0 0 0; color: var(--user-text-light); font-size: 0.9rem;">Desktop Application</p>
                    </div>
                </div>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                    <p style="margin: 0 0 0.5rem 0; color: var(--user-text); font-weight: 600;">Download Link:</p>
                    <a href="https://rb.gy/8beuu" target="_blank" style="color: var(--user-primary); word-break: break-all; text-decoration: none; font-weight: 600;">
                        https://rb.gy/8beuu
                    </a>
                </div>
                <a href="https://rb.gy/8beuu" target="_blank" class="btn btn-primary" style="width: 100%; text-align: center;">
                    <i class="fas fa-download"></i> Download for Windows
                </a>
            </div>

            <!-- ZEASN/Whale OS -->
            <div style="background: white; border-radius: 8px; padding: 1.5rem; border: 1px solid var(--user-border);">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="font-size: 2rem; color: var(--user-primary);">
                        <i class="fas fa-tv"></i>
                    </div>
                    <div style="flex: 1;">
                        <h4 style="margin: 0; color: var(--user-text);">ZEASN/Whale OS</h4>
                        <p style="margin: 0.25rem 0 0 0; color: var(--user-text-light); font-size: 0.9rem;">Available on ZEASN Platform</p>
                    </div>
                </div>
                <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 1rem; border-radius: 6px;">
                    <p style="margin: 0; color: #0c5460; text-align: center;">
                        <i class="fas fa-check-circle"></i> Our app is available on ZEASN/Whale OS compatible devices
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Setup Instructions -->
<div class="user-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-question-circle"></i>
            Setup Instructions
        </h2>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <!-- Android/Fire TV Instructions -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                <h4 style="margin: 0 0 1rem 0; color: var(--user-text);">
                    <i class="fab fa-android"></i> Android & Fire TV
                </h4>
                <ol style="margin: 0; padding-left: 1.5rem; color: var(--user-text-light);">
                    <li>Download the APK file</li>
                    <li>Enable "Unknown Sources" in settings</li>
                    <li>Install the APK</li>
                    <li>Open Ibo Player Pro</li>
                    <li>Enter your playlist URL</li>
                    <li>Start streaming!</li>
                </ol>
            </div>

            <!-- iOS Instructions -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                <h4 style="margin: 0 0 1rem 0; color: var(--user-text);">
                    <i class="fab fa-apple"></i> iPhone & iPad
                </h4>
                <ol style="margin: 0; padding-left: 1.5rem; color: var(--user-text-light);">
                    <li>Open App Store</li>
                    <li>Search "Ibo Player Pro"</li>
                    <li>Download and install</li>
                    <li>Open the app</li>
                    <li>Configure your playlist</li>
                    <li>Enjoy streaming!</li>
                </ol>
            </div>

            <!-- Smart TV Instructions -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                <h4 style="margin: 0 0 1rem 0; color: var(--user-text);">
                    <i class="fas fa-tv"></i> Smart TVs
                </h4>
                <ol style="margin: 0; padding-left: 1.5rem; color: var(--user-text-light);">
                    <li>Open your TV's app store</li>
                    <li>Search "Ibo Player Pro"</li>
                    <li>Install the app</li>
                    <li>Launch Ibo Player Pro</li>
                    <li>Add playlist URL</li>
                    <li>Start watching!</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Need Help -->
<div style="text-align: center; margin-top: 2rem; padding: 2rem; background: linear-gradient(135deg, #8B0000, #660000); border-radius: 12px; color: white;">
    <h3 style="margin: 0 0 1rem 0; font-size: 1.5rem;">Need Help Getting Started?</h3>
    <p style="margin: 0 0 1.5rem 0; opacity: 0.9;">Our support team is ready to assist you with installation and setup</p>
    <a href="/user/support.php" class="btn" style="background: white; color: #8B0000; padding: 1rem 2rem; text-decoration: none; display: inline-block; border-radius: 8px; font-weight: 600;">
        <i class="fas fa-headset"></i> Contact Support
    </a>
</div>

<style>
/* Mobile Responsive for Downloads Page */
@media (max-width: 768px) {
    .card-body > div {
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

