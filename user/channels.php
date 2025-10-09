<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';
require_once '../lib/seo.php';

$db = new Database();
$conn = $db->getConnection();

// Get filter parameters
$category = $_GET['category'] ?? '';
$country = $_GET['country'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$whereConditions = ["c.is_active = true"];
$params = [];

if (!empty($category)) {
    $whereConditions[] = "c.category = :category";
    $params[':category'] = $category;
}

if (!empty($country)) {
    $whereConditions[] = "c.country = :country";
    $params[':country'] = $country;
}

if (!empty($search)) {
    $whereConditions[] = "(c.name ILIKE :search OR c.description ILIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$whereClause = implode(' AND ', $whereConditions);

$channelsQuery = "SELECT c.*, 
                        CASE 
                            WHEN c.logo_url IS NOT NULL AND c.logo_url != '' THEN c.logo_url
                            ELSE 'https://img.icons8.com/color/96/000000/tv.png'
                        END as display_logo
                 FROM channels c 
                 WHERE $whereClause 
                 ORDER BY c.sort_order, c.name";

$channelsStmt = $conn->prepare($channelsQuery);
$channelsStmt->execute($params);
$channels = $channelsStmt->fetchAll();

// Get categories for filter
$categoriesQuery = "SELECT DISTINCT category FROM channels WHERE is_active = true ORDER BY category";
$categoriesStmt = $conn->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Get countries for filter
$countriesQuery = "SELECT DISTINCT country FROM channels WHERE is_active = true ORDER BY country";
$countriesStmt = $conn->prepare($countriesQuery);
$countriesStmt->execute();
$countries = $countriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Get SEO data
$seo_meta = SEO::getMetaTags('channels');
$og_tags = SEO::getOpenGraphTags('channels');

$page_title = 'Channels';
include 'includes/header.php';
?>

<!-- Channels Info Banner -->
<div style="background: linear-gradient(135deg, #8B0000, #660000); color: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; text-align: center;">
    <div style="font-size: 3rem; margin-bottom: 0.5rem; font-weight: bold;">
        16,000+
    </div>
    <h2 style="margin: 0 0 1rem 0; font-size: 1.5rem;">
        Premium Channels Available
    </h2>
    <p style="margin: 0; opacity: 0.9; max-width: 600px; margin: 0 auto;">
        Access thousands of international and local channels including news, sports, movies, and entertainment from around the world.
    </p>
    <div style="display: flex; gap: 1.5rem; justify-content: center; margin-top: 1.5rem; flex-wrap: wrap; font-size: 0.9rem;">
        <div><i class="fas fa-check-circle"></i> International News</div>
        <div><i class="fas fa-check-circle"></i> Premium Sports</div>
        <div><i class="fas fa-check-circle"></i> Movies & Entertainment</div>
        <div><i class="fas fa-check-circle"></i> HD & 4K Quality</div>
    </div>
</div>

<!-- Filter Section -->
<div class="user-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Filter Channels</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-form" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
            <div class="form-group">
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
            <div class="form-group">
                <label for="country">Country</label>
                <select id="country" name="country">
                    <option value="">All Countries</option>
                    <?php foreach ($countries as $country_name): ?>
                    <option value="<?php echo htmlspecialchars($country_name); ?>" <?php echo $country === $country_name ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($country_name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search channels...">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Channels Grid -->
<div class="user-card">
    <div class="card-header">
        <h3 class="card-title">Available Channels (<?php echo count($channels); ?> found)</h3>
        <p>Browse and watch your favorite channels</p>
    </div>
    <div class="card-body">
        <?php if (empty($channels)): ?>
        <div class="empty-state">
            <i class="fas fa-tv"></i>
            <h3>No channels found</h3>
            <p>Try adjusting your search criteria or browse all channels.</p>
            <a href="channels.php" class="btn btn-primary">View All Channels</a>
        </div>
        <?php else: ?>
        <div class="channels-grid">
            <?php foreach ($channels as $channel): ?>
            <div class="channel-card">
                <div class="channel-logo">
                    <img src="<?php echo htmlspecialchars($channel['display_logo']); ?>" 
                         alt="<?php echo htmlspecialchars($channel['name']); ?>"
                         onerror="this.src='https://img.icons8.com/color/96/000000/tv.png'">
                </div>
                <div class="channel-info">
                    <h4 class="channel-name"><?php echo htmlspecialchars($channel['name']); ?></h4>
                    <p class="channel-description"><?php echo htmlspecialchars($channel['description']); ?></p>
                    <div class="channel-meta">
                        <span class="channel-category"><?php echo htmlspecialchars($channel['category']); ?></span>
                        <?php if ($channel['country']): ?>
                        <span class="channel-country"><?php echo htmlspecialchars($channel['country']); ?></span>
                        <?php endif; ?>
                        <?php if ($channel['is_hd']): ?>
                        <span class="channel-quality">HD</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="channel-actions">
                    <button class="btn btn-primary" onclick="watchChannel(<?php echo $channel['id']; ?>)">
                        <i class="fas fa-play"></i>
                        Watch Now
                    </button>
                    <button class="btn btn-secondary" onclick="addToFavorites(<?php echo $channel['id']; ?>)">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
    
    <style>
.filter-form {
            display: flex;
    gap: 1rem;
    align-items: end;
            flex-wrap: wrap;
        }
        
.filter-form .form-group {
    margin-bottom: 0;
    min-width: 150px;
        }
        
        .channels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
        }
        
        .channel-card {
            background: white;
    border-radius: var(--user-radius);
    box-shadow: var(--user-shadow);
    border: 1px solid var(--user-border);
            overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .channel-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .channel-logo {
    text-align: center;
    padding: 1.5rem;
    background: #f8f9fa;
        }
        
        .channel-logo img {
    width: 80px;
    height: 80px;
            object-fit: contain;
    border-radius: 8px;
        }
        
        .channel-info {
    padding: 1rem 1.5rem;
        }
        
        .channel-name {
    margin: 0 0 0.5rem 0;
    font-size: 1.125rem;
            font-weight: 600;
    color: var(--user-text);
        }
        
        .channel-description {
    margin: 0 0 1rem 0;
    color: var(--user-text-light);
    font-size: 0.875rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
        }
        
        .channel-meta {
            display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.channel-category,
.channel-country,
.channel-quality {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
        }
        
        .channel-category {
    background: #E3F2FD;
    color: #1976D2;
        }
        
        .channel-country {
    background: #F3E5F5;
    color: #7B1FA2;
}

.channel-quality {
    background: #E8F5E8;
    color: #2E7D32;
}

.channel-actions {
    padding: 0 1.5rem 1.5rem;
            display: flex;
    gap: 0.5rem;
}

.channel-actions .btn {
    flex: 1;
    justify-content: center;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--user-text-light);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--user-text-light);
}

.empty-state h3 {
    margin: 0 0 0.5rem 0;
    color: var(--user-text);
}

.empty-state p {
    margin: 0 0 1.5rem 0;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--user-text);
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--user-border);
    border-radius: var(--user-radius);
    font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--user-primary);
    box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
        }
        
        @media (max-width: 768px) {
    .channels-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
    .filter-form .form-group {
                min-width: auto;
            }
        }
    </style>

    <script>
        function watchChannel(channelId) {
                // Check if user has active subscription
    fetch('check-subscription.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.hasActiveSubscription) {
                // Redirect to streaming page
                window.location.href = `watch.php?channel=${channelId}`;
                        } else {
                            // Redirect to subscription page
                alert('Please subscribe to watch channels');
                            window.location.href = '/user/subscriptions/subscribe.php';
                        }
                    })
                    .catch(error => {
            console.error('Error checking subscription:', error);
            alert('Please subscribe to watch channels');
            window.location.href = '/user/subscriptions/subscribe.php';
        });
}

function addToFavorites(channelId) {
    fetch('add-to-favorites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ channel_id: channelId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button state
            const button = event.target.closest('button');
            button.innerHTML = '<i class="fas fa-heart" style="color: #e74c3c;"></i>';
            button.disabled = true;
        } else {
            alert('Error adding to favorites');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding to favorites');
    });
        }
    </script>

<?php include 'includes/footer.php'; ?>
