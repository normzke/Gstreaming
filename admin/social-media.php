<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Initialize message variables
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
        switch ($action) {
            case 'update_social_media':
            $facebook_url = $_POST['facebook_url'] ?? '';
            $twitter_url = $_POST['twitter_url'] ?? '';
            $instagram_url = $_POST['instagram_url'] ?? '';
            $youtube_url = $_POST['youtube_url'] ?? '';
            $linkedin_url = $_POST['linkedin_url'] ?? '';
            $tiktok_url = $_POST['tiktok_url'] ?? '';
            $whatsapp_number = $_POST['whatsapp_number'] ?? '';
            $telegram_url = $_POST['telegram_url'] ?? '';
            $display_in_footer = isset($_POST['display_in_footer']) ? 1 : 0;
            $display_in_header = isset($_POST['display_in_header']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Update or insert social media configuration
            $checkQuery = "SELECT id FROM social_media_config WHERE id = 1";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute();
            
            if ($checkStmt->fetch()) {
                // Update existing config
                $updateQuery = "UPDATE social_media_config SET 
                               facebook_url = ?, 
                               twitter_url = ?, 
                               instagram_url = ?, 
                               youtube_url = ?, 
                               linkedin_url = ?, 
                               tiktok_url = ?, 
                               whatsapp_number = ?, 
                               telegram_url = ?, 
                               display_in_footer = ?, 
                               display_in_header = ?, 
                               is_active = ?, 
                               updated_at = NOW() 
                               WHERE id = 1";
                    $updateStmt = $conn->prepare($updateQuery);
                $success = $updateStmt->execute([
                    $facebook_url, $twitter_url, $instagram_url, $youtube_url,
                    $linkedin_url, $tiktok_url, $whatsapp_number, $telegram_url,
                    $display_in_footer, $display_in_header, $is_active
                ]);
            } else {
                // Insert new config
                $insertQuery = "INSERT INTO social_media_config 
                               (facebook_url, twitter_url, instagram_url, youtube_url, 
                                linkedin_url, tiktok_url, whatsapp_number, telegram_url, 
                                display_in_footer, display_in_header, is_active, created_at, updated_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $insertStmt = $conn->prepare($insertQuery);
                $success = $insertStmt->execute([
                    $facebook_url, $twitter_url, $instagram_url, $youtube_url,
                    $linkedin_url, $tiktok_url, $whatsapp_number, $telegram_url,
                    $display_in_footer, $display_in_header, $is_active
                ]);
            }
            
            if ($success) {
                $message = 'Social media configuration updated successfully';
                $messageType = 'success';
            } else {
                $message = 'Error updating social media configuration';
                $messageType = 'error';
            }
                break;
                
        case 'test_social_links':
            // Test social media links
            $testQuery = "SELECT * FROM social_media_config WHERE id = 1";
            $testStmt = $conn->prepare($testQuery);
            $testStmt->execute();
            $config = $testStmt->fetch();
            
            if ($config) {
                $test_results = [];
                $social_platforms = [
                    'facebook' => $config['facebook_url'],
                    'twitter' => $config['twitter_url'],
                    'instagram' => $config['instagram_url'],
                    'youtube' => $config['youtube_url'],
                    'linkedin' => $config['linkedin_url'],
                    'tiktok' => $config['tiktok_url'],
                    'telegram' => $config['telegram_url']
                ];
                
                foreach ($social_platforms as $platform => $url) {
                    if (!empty($url)) {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_NOBODY, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        
                        curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        
                        $test_results[] = ucfirst($platform) . ': ' . ($http_code === 200 ? 'OK' : 'Failed (HTTP ' . $http_code . ')');
                    }
                }
                
                if (!empty($test_results)) {
                    $message = 'Social media link test results:<br>' . implode('<br>', $test_results);
                    $messageType = 'info';
                } else {
                    $message = 'No social media links configured to test';
                    $messageType = 'warning';
                }
            } else {
                $message = 'No social media configuration found';
        $messageType = 'error';
            }
            break;
    }
}

// Get current social media configuration
$social_config = null;
try {
    $configQuery = "SELECT * FROM social_media_config WHERE id = 1";
    $configStmt = $conn->prepare($configQuery);
    $configStmt->execute();
    $social_config = $configStmt->fetch();
} catch (PDOException $e) {
    // Table doesn't exist yet, use default values
    $social_config = null;
}

// Get social media engagement statistics (mock data for now)
$social_stats = [
    'total_followers' => 12500,
    'facebook_likes' => 3200,
    'twitter_followers' => 2800,
    'instagram_followers' => 4500,
    'youtube_subscribers' => 2000,
    'total_posts' => 156,
    'engagement_rate' => 4.2
];

$page_title = 'Social Media';
include 'includes/header.php';
?>

<!-- Messages -->
<?php if ($message): ?>
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <div class="alert alert-<?php echo $messageType; ?>" style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border-radius: var(--admin-radius); background: <?php echo $messageType === 'success' ? '#D1FAE5' : ($messageType === 'info' ? '#DBEAFE' : ($messageType === 'warning' ? '#FEF3C7' : '#FEE2E2')); ?>; color: <?php echo $messageType === 'success' ? '#065F46' : ($messageType === 'info' ? '#1E40AF' : ($messageType === 'warning' ? '#92400E' : '#991B1B')); ?>;">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'info' ? 'info-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'exclamation-circle')); ?>"></i>
            <div><?php echo $message; ?></div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Social Media Statistics -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Social Media Statistics</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="stat-item">
                <h4><?php echo number_format($social_stats['total_followers']); ?></h4>
                <p>Total Followers</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($social_stats['facebook_likes']); ?></h4>
                <p>Facebook Likes</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($social_stats['twitter_followers']); ?></h4>
                <p>Twitter Followers</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($social_stats['instagram_followers']); ?></h4>
                <p>Instagram Followers</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($social_stats['youtube_subscribers']); ?></h4>
                <p>YouTube Subscribers</p>
        </div>
            <div class="stat-item">
                <h4><?php echo number_format($social_stats['total_posts']); ?></h4>
                <p>Total Posts</p>
            </div>
            <div class="stat-item">
                <h4><?php echo $social_stats['engagement_rate']; ?>%</h4>
                <p>Engagement Rate</p>
                </div>
                            </div>
                            </div>
                        </div>

<!-- Social Media Configuration Form -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">Social Media Links Configuration</h3>
        <div class="card-actions">
            <button class="btn btn-secondary" onclick="testSocialLinks()">
                <i class="fas fa-external-link-alt"></i>
                Test Links
            </button>
                            </div>
                        </div>
    <div class="card-body">
        <form method="POST" id="socialMediaForm">
            <input type="hidden" name="action" value="update_social_media">
            
            <div class="form-section">
                <h4 class="form-section-title">Social Media Platforms</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="facebook_url">Facebook URL</label>
                        <input type="url" id="facebook_url" name="facebook_url" 
                               value="<?php echo htmlspecialchars($social_config['facebook_url'] ?? ''); ?>"
                               placeholder="https://facebook.com/yourpage">
                        <small class="form-help">Your Facebook page URL</small>
                            </div>
                    <div class="form-group">
                        <label for="twitter_url">Twitter URL</label>
                        <input type="url" id="twitter_url" name="twitter_url" 
                               value="<?php echo htmlspecialchars($social_config['twitter_url'] ?? ''); ?>"
                               placeholder="https://twitter.com/yourhandle">
                        <small class="form-help">Your Twitter profile URL</small>
                            </div>
                        </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="instagram_url">Instagram URL</label>
                        <input type="url" id="instagram_url" name="instagram_url" 
                               value="<?php echo htmlspecialchars($social_config['instagram_url'] ?? ''); ?>"
                               placeholder="https://instagram.com/yourhandle">
                        <small class="form-help">Your Instagram profile URL</small>
                            </div>
                    <div class="form-group">
                        <label for="youtube_url">YouTube URL</label>
                        <input type="url" id="youtube_url" name="youtube_url" 
                               value="<?php echo htmlspecialchars($social_config['youtube_url'] ?? ''); ?>"
                               placeholder="https://youtube.com/c/yourchannel">
                        <small class="form-help">Your YouTube channel URL</small>
                            </div>
                        </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="linkedin_url">LinkedIn URL</label>
                        <input type="url" id="linkedin_url" name="linkedin_url" 
                               value="<?php echo htmlspecialchars($social_config['linkedin_url'] ?? ''); ?>"
                               placeholder="https://linkedin.com/company/yourcompany">
                        <small class="form-help">Your LinkedIn company page URL</small>
                    </div>
                    <div class="form-group">
                        <label for="tiktok_url">TikTok URL</label>
                        <input type="url" id="tiktok_url" name="tiktok_url" 
                               value="<?php echo htmlspecialchars($social_config['tiktok_url'] ?? ''); ?>"
                               placeholder="https://tiktok.com/@yourhandle">
                        <small class="form-help">Your TikTok profile URL</small>
                            </div>
                            </div>
                        </div>

            <div class="form-section">
                <h4 class="form-section-title">Messaging Platforms</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="whatsapp_number">WhatsApp Number</label>
                        <input type="tel" id="whatsapp_number" name="whatsapp_number" 
                               value="<?php echo htmlspecialchars($social_config['whatsapp_number'] ?? ''); ?>"
                               placeholder="+254700000000">
                        <small class="form-help">WhatsApp business number (include country code)</small>
                    </div>
                    <div class="form-group">
                        <label for="telegram_url">Telegram URL</label>
                        <input type="url" id="telegram_url" name="telegram_url" 
                               value="<?php echo htmlspecialchars($social_config['telegram_url'] ?? ''); ?>"
                               placeholder="https://t.me/yourchannel">
                        <small class="form-help">Your Telegram channel or group URL</small>
                            </div>
                            </div>
                        </div>

            <div class="form-section">
                <h4 class="form-section-title">Display Settings</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="display_in_footer" value="1" 
                                   <?php echo ($social_config['display_in_footer'] ?? 1) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Display in Footer
                                </label>
                        <small class="form-help">Show social media links in website footer</small>
                            </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="display_in_header" value="1" 
                                   <?php echo ($social_config['display_in_header'] ?? 0) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Display in Header
                        </label>
                        <small class="form-help">Show social media links in website header</small>
                            </div>
                        </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" value="1" 
                                   <?php echo ($social_config['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Enable Social Media Integration
                                </label>
                        <small class="form-help">Enable or disable all social media features</small>
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

            <!-- Social Media Preview -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">Social Media Preview</h3>
        <p>Preview how your social media links will appear on the website</p>
    </div>
    <div class="card-body">
            <div class="social-preview">
            <div class="preview-section">
                <h4>Footer Preview</h4>
                <div class="social-links-preview" id="footerPreview">
                    <!-- Social links will be populated by JavaScript -->
                </div>
            </div>
            
            <div class="preview-section">
                <h4>Header Preview</h4>
                <div class="social-links-preview" id="headerPreview">
                    <!-- Social links will be populated by JavaScript -->
                </div>
            </div>
        </div>
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

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius);
    font-size: 1rem;
}

.form-group input:focus {
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

        .social-preview {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.preview-section h4 {
    margin: 0 0 1rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--admin-text);
        }

        .social-links-preview {
            display: flex;
    gap: 0.5rem;
            flex-wrap: wrap;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: var(--admin-radius);
    border: 1px solid var(--admin-border);
        }

        .social-link-preview {
            display: flex;
            align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border-radius: var(--admin-radius);
    border: 1px solid var(--admin-border);
            text-decoration: none;
    color: var(--admin-text);
    font-size: 0.875rem;
    transition: all 0.2s ease;
        }

        .social-link-preview:hover {
    background: var(--admin-primary);
    color: white;
    transform: translateY(-1px);
        }

        .social-link-preview i {
    font-size: 1rem;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: var(--admin-text-light);
}

.empty-state i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: var(--admin-text-light);
}

.empty-state p {
    margin: 0;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .social-preview {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function testSocialLinks() {
    if (confirm('This will test all configured social media links. Continue?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="test_social_links">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function resetForm() {
    if (confirm('Are you sure you want to reset the form? All changes will be lost.')) {
        document.getElementById('socialMediaForm').reset();
        updatePreview();
    }
}

function updatePreview() {
    const socialPlatforms = [
        { name: 'Facebook', icon: 'fab fa-facebook', color: '#1877F2' },
        { name: 'Twitter', icon: 'fab fa-twitter', color: '#1DA1F2' },
        { name: 'Instagram', icon: 'fab fa-instagram', color: '#E4405F' },
        { name: 'YouTube', icon: 'fab fa-youtube', color: '#FF0000' },
        { name: 'LinkedIn', icon: 'fab fa-linkedin', color: '#0077B5' },
        { name: 'TikTok', icon: 'fab fa-tiktok', color: '#000000' },
        { name: 'WhatsApp', icon: 'fab fa-whatsapp', color: '#25D366' },
        { name: 'Telegram', icon: 'fab fa-telegram', color: '#0088CC' }
    ];
    
    const footerPreview = document.getElementById('footerPreview');
    const headerPreview = document.getElementById('headerPreview');
    const displayInFooter = document.querySelector('input[name="display_in_footer"]').checked;
    const displayInHeader = document.querySelector('input[name="display_in_header"]').checked;
    
    // Clear existing previews
    footerPreview.innerHTML = '';
    headerPreview.innerHTML = '';
    
    // Add social links to previews
    socialPlatforms.forEach(platform => {
        const inputField = document.querySelector(`input[name="${platform.name.toLowerCase()}_url"], input[name="whatsapp_number"]`);
        if (inputField && inputField.value.trim()) {
            const linkElement = document.createElement('a');
            linkElement.href = inputField.value;
            linkElement.target = '_blank';
            linkElement.className = 'social-link-preview';
            linkElement.innerHTML = `<i class="${platform.icon}"></i> ${platform.name}`;
            linkElement.style.borderColor = platform.color;
            
            if (displayInFooter) {
                footerPreview.appendChild(linkElement.cloneNode(true));
            }
            if (displayInHeader) {
                headerPreview.appendChild(linkElement.cloneNode(true));
            }
        }
    });
    
    // Show empty state if no links
    if (footerPreview.children.length === 0) {
        footerPreview.innerHTML = '<div class="empty-state"><i class="fas fa-share-alt"></i><p>No social links configured</p></div>';
    }
    if (headerPreview.children.length === 0) {
        headerPreview.innerHTML = '<div class="empty-state"><i class="fas fa-share-alt"></i><p>No social links configured</p></div>';
    }
}

// Update preview when form changes
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('socialMediaForm');
    const inputs = form.querySelectorAll('input');
    
    inputs.forEach(input => {
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
    });
    
    // Initial preview update
    updatePreview();
});
</script>

<?php include 'includes/footer.php'; ?>