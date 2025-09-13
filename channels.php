<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get channels from database
$db = new Database();
$conn = $db->getConnection();

// Handle search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$country = isset($_GET['country']) ? trim($_GET['country']) : '';
$quality = isset($_GET['quality']) ? trim($_GET['quality']) : '';

// Build query with filters
$whereConditions = ['c.is_active = true'];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(c.name ILIKE ? OR c.description ILIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($category)) {
    $whereConditions[] = "c.category = ?";
    $params[] = $category;
}

if (!empty($country)) {
    $whereConditions[] = "c.country = ?";
    $params[] = $country;
}

if (!empty($quality)) {
    if ($quality === 'HD') {
        $whereConditions[] = "c.is_hd = true";
    } elseif ($quality === 'SD') {
        $whereConditions[] = "c.is_hd = false";
    }
}

$whereClause = implode(' AND ', $whereConditions);

// Get channels
$channelsQuery = "SELECT * FROM channels c WHERE $whereClause ORDER BY c.sort_order, c.name ASC";
$channelsStmt = $conn->prepare($channelsQuery);
$channelsStmt->execute($params);
$channels = $channelsStmt->fetchAll();

// Get unique categories, countries for filter dropdowns
$categoriesQuery = "SELECT DISTINCT category FROM channels WHERE category IS NOT NULL AND is_active = true ORDER BY category";
$categoriesStmt = $conn->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

$countriesQuery = "SELECT DISTINCT country FROM channels WHERE country IS NOT NULL AND is_active = true ORDER BY country";
$countriesStmt = $conn->prepare($countriesQuery);
$countriesStmt->execute();
$countries = $countriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Group channels by category for display
$channelsByCategory = [];
foreach ($channels as $channel) {
    $cat = $channel['category'] ?: 'Other';
    if (!isset($channelsByCategory[$cat])) {
        $channelsByCategory[$cat] = [];
    }
    $channelsByCategory[$cat][] = $channel;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Channel List - GStreaming</title>
    <meta name="description" content="Browse our complete list of TV channels available on GStreaming. Find your favorite local and international channels.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/channels.css">
    
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
                    <a href="channels.php" class="nav-link active">Channels</a>
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
                <li class="nav-item">
                    <a href="login.php" class="nav-link btn-login">Login</a>
                </li>
                <li class="nav-item">
                    <a href="register.php" class="nav-link btn-register">Get Started</a>
                </li>
            </ul>
            
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <nav class="breadcrumb">
                    <a href="index.php">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current">Channels</span>
                </nav>
                <h1 class="page-title">Channel List</h1>
                <p class="page-subtitle">Browse our complete collection of local and international TV channels</p>
            </div>
        </div>
    </section>

    <!-- Channels Section -->
    <section class="channels-section">
        <div class="container">
            <!-- Search and Filter Bar -->
            <div class="channels-filters">
                <form method="GET" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="search">Search Channels</label>
                            <div class="search-input">
                                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by channel name...">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label for="category">Category</label>
                            <select id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="country">Country</label>
                            <select id="country" name="country">
                                <option value="">All Countries</option>
                                <?php foreach ($countries as $cntry): ?>
                                    <option value="<?php echo htmlspecialchars($cntry); ?>" <?php echo $country === $cntry ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cntry); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="quality">Quality</label>
                            <select id="quality" name="quality">
                                <option value="">All Quality</option>
                                <option value="HD" <?php echo $quality === 'HD' ? 'selected' : ''; ?>>HD</option>
                                <option value="SD" <?php echo $quality === 'SD' ? 'selected' : ''; ?>>SD</option>
                            </select>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i>
                                Filter
                            </button>
                            <a href="channels.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
                
                <div class="channels-stats">
                    <span class="stat">
                        <i class="fas fa-tv"></i>
                        <?php echo count($channels); ?> Channels Available
                    </span>
                    <?php if (!empty($search) || !empty($category) || !empty($country) || !empty($quality)): ?>
                        <span class="filter-indicator">
                            <i class="fas fa-filter"></i>
                            Filters Applied
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Channels Grid -->
            <?php if (!empty($channelsByCategory)): ?>
                <?php foreach ($channelsByCategory as $categoryName => $categoryChannels): ?>
                    <div class="channels-category">
                        <div class="category-header">
                            <h2 class="category-title">
                                <i class="fas fa-folder"></i>
                                <?php echo htmlspecialchars($categoryName); ?>
                                <span class="channel-count">(<?php echo count($categoryChannels); ?>)</span>
                            </h2>
                        </div>
                        
                        <div class="channels-grid">
                            <?php foreach ($categoryChannels as $channel): ?>
                                <div class="channel-card" data-aos="fadeInUp">
                                    <div class="channel-logo">
                                        <?php if ($channel['logo_url']): ?>
                                            <img src="<?php echo htmlspecialchars($channel['logo_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($channel['name']); ?>"
                                                 onerror="this.src='assets/images/default-channel.png'">
                                        <?php else: ?>
                                            <div class="default-logo">
                                                <i class="fas fa-tv"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($channel['is_hd']): ?>
                                            <div class="quality-badge hd">
                                                <i class="fas fa-hd-video"></i>
                                                HD
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="channel-info">
                                        <h3 class="channel-name"><?php echo htmlspecialchars($channel['name']); ?></h3>
                                        
                                        <?php if ($channel['description']): ?>
                                            <p class="channel-description">
                                                <?php echo htmlspecialchars(substr($channel['description'], 0, 100)); ?>
                                                <?php if (strlen($channel['description']) > 100): ?>...<?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="channel-meta">
                                            <?php if ($channel['country']): ?>
                                                <span class="meta-item">
                                                    <i class="fas fa-globe"></i>
                                                    <?php echo htmlspecialchars($channel['country']); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($channel['language']): ?>
                                                <span class="meta-item">
                                                    <i class="fas fa-language"></i>
                                                    <?php echo htmlspecialchars($channel['language']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="channel-actions">
                                        <button class="btn btn-primary btn-sm" onclick="previewChannel(<?php echo $channel['id']; ?>)">
                                            <i class="fas fa-play"></i>
                                            Preview
                                        </button>
                                        <button class="btn btn-secondary btn-sm" onclick="addToFavorites(<?php echo $channel['id']; ?>)">
                                            <i class="fas fa-heart"></i>
                                            Favorite
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-channels">
                    <div class="no-channels-content">
                        <i class="fas fa-search"></i>
                        <h3>No Channels Found</h3>
                        <p>Try adjusting your search criteria or filters to find the channels you're looking for.</p>
                        <a href="channels.php" class="btn btn-primary">View All Channels</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Channel Preview Modal -->
    <div id="channelPreviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalChannelName">Channel Preview</h3>
                <span class="modal-close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="preview-container">
                    <div class="preview-placeholder">
                        <i class="fas fa-tv"></i>
                        <p>Channel preview will be available after subscription</p>
                        <a href="register.php" class="btn btn-primary">Subscribe Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating WhatsApp Button -->
    <div class="whatsapp-float">
        <a href="https://wa.me/254768704834?text=Hello%2C%20I%20need%20help%20with%20GStreaming" target="_blank" class="whatsapp-btn">
            <i class="fab fa-whatsapp"></i>
            <span class="whatsapp-text">Chat with us</span>
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
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="billing.php">Billing</a></li>
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
    <script src="assets/js/enhanced.js"></script>
    <script src="assets/js/channels.js"></script>
</body>
</html>
