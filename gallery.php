<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

$db = new Database();
$conn = $db->getConnection();

// Get gallery items with pagination
$page = $_GET['page'] ?? 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM gallery WHERE type IN ('image', 'video')";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute();
$totalItems = $countStmt->fetch()['total'];
$totalPages = ceil($totalItems / $limit);

// Get gallery items
$galleryQuery = "SELECT * FROM gallery WHERE type IN ('image', 'video') ORDER BY sort_order, created_at DESC LIMIT :limit OFFSET :offset";
$galleryStmt = $conn->prepare($galleryQuery);
$galleryStmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$galleryStmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$galleryStmt->execute();
$galleryItems = $galleryStmt->fetchAll();

// Get categories
$categoriesQuery = "SELECT DISTINCT category FROM gallery WHERE category IS NOT NULL ORDER BY category";
$categoriesStmt = $conn->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - GStreaming</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/components.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Lightbox CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
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
                    <a href="index.php#packages" class="nav-link">Packages</a>
                </li>
                <li class="nav-item">
                    <a href="index.php#devices" class="nav-link">Devices</a>
                </li>
                <li class="nav-item">
                    <a href="gallery.php" class="nav-link active">Gallery</a>
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
        </div>
    </nav>

    <!-- Gallery Section -->
    <section class="gallery-page">
        <div class="container">
            <div class="gallery-header">
                <h1>Content Gallery</h1>
                <p>Explore our featured channels and content previews</p>
            </div>
            
            <!-- Gallery Filters -->
            <div class="gallery-filters">
                <div class="filter-group">
                    <button class="filter-btn active" data-filter="all">All Content</button>
                    <?php foreach ($categories as $category): ?>
                        <button class="filter-btn" data-filter="<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $category))); ?>">
                            <?php echo htmlspecialchars($category); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                
                <div class="view-toggle">
                    <button class="view-btn active" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-btn" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
            <!-- Gallery Grid -->
            <div class="gallery-container">
                <?php if (!empty($galleryItems)): ?>
                    <div class="gallery-grid" id="gallery-grid">
                        <?php foreach ($galleryItems as $item): ?>
                            <div class="gallery-item" 
                                 data-category="<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $item['category'] ?? 'uncategorized'))); ?>"
                                 data-type="<?php echo $item['type']; ?>">
                                
                                <?php if ($item['type'] === 'video'): ?>
                                    <div class="media-container video-container">
                                        <div class="video-thumbnail">
                                            <?php if ($item['image_url']): ?>
                                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['title']); ?>">
                                            <?php else: ?>
                                                <div class="video-placeholder">
                                                    <i class="fas fa-play"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="play-overlay">
                                                <i class="fas fa-play"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="video-modal" data-video="<?php echo htmlspecialchars($item['video_url']); ?>">
                                            <div class="video-content">
                                                <iframe src="<?php echo htmlspecialchars($item['video_url']); ?>" 
                                                        frameborder="0" 
                                                        allowfullscreen></iframe>
                                                <button class="video-close">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="media-container image-container">
                                        <a href="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                           data-lightbox="gallery" 
                                           data-title="<?php echo htmlspecialchars($item['title']); ?>">
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>">
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="gallery-content">
                                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                                    <div class="gallery-meta">
                                        <span class="category"><?php echo htmlspecialchars($item['category'] ?? 'Uncategorized'); ?></span>
                                        <span class="type"><?php echo ucfirst($item['type']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="page-btn">
                                    <i class="fas fa-chevron-left"></i>
                                    Previous
                                </a>
                            <?php endif; ?>
                            
                            <div class="page-numbers">
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>" 
                                       class="page-number <?php echo $i == $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="page-btn">
                                    Next
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="gallery-empty">
                        <div class="empty-icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <h3>No Content Available</h3>
                        <p>We're working on adding amazing content previews. Check back soon!</p>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Home
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

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
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php#packages">Packages</a></li>
                        <li><a href="index.php#devices">Supported Devices</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="support.php">Support</a></li>
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
                            <span>+254 700 000 000</span>
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
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    <script>
        // Gallery filters
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active filter
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                const items = document.querySelectorAll('.gallery-item');
                
                items.forEach(item => {
                    if (filter === 'all' || item.getAttribute('data-category') === filter) {
                        item.style.display = 'block';
                        item.classList.add('animate-in');
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
        
        // View toggle
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const view = this.getAttribute('data-view');
                const grid = document.getElementById('gallery-grid');
                
                if (view === 'list') {
                    grid.classList.add('list-view');
                } else {
                    grid.classList.remove('list-view');
                }
            });
        });
        
        // Video modal
        document.querySelectorAll('.play-overlay').forEach(overlay => {
            overlay.addEventListener('click', function() {
                const modal = this.closest('.gallery-item').querySelector('.video-modal');
                modal.classList.add('active');
                document.body.classList.add('modal-open');
            });
        });
        
        document.querySelectorAll('.video-close').forEach(close => {
            close.addEventListener('click', function() {
                const modal = this.closest('.video-modal');
                modal.classList.remove('active');
                document.body.classList.remove('modal-open');
                
                // Stop video
                const iframe = modal.querySelector('iframe');
                iframe.src = iframe.src;
            });
        });
        
        // Close modal on backdrop click
        document.querySelectorAll('.video-modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                    document.body.classList.remove('modal-open');
                    
                    // Stop video
                    const iframe = this.querySelector('iframe');
                    iframe.src = iframe.src;
                }
            });
        });
    </script>
</body>
</html>
