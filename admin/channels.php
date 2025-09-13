<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add':
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $logo_url = trim($_POST['logo_url']);
                $stream_url = trim($_POST['stream_url']);
                $category = trim($_POST['category']);
                $country = trim($_POST['country']);
                $language = trim($_POST['language']);
                $is_hd = isset($_POST['is_hd']) ? 1 : 0;
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $sort_order = (int)$_POST['sort_order'];
                
                $insertQuery = "INSERT INTO channels (name, description, logo_url, stream_url, category, country, language, is_hd, is_active, sort_order) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->execute([$name, $description, $logo_url, $stream_url, $category, $country, $language, $is_hd, $is_active, $sort_order]);
                
                $message = 'Channel added successfully!';
                $messageType = 'success';
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $logo_url = trim($_POST['logo_url']);
                $stream_url = trim($_POST['stream_url']);
                $category = trim($_POST['category']);
                $country = trim($_POST['country']);
                $language = trim($_POST['language']);
                $is_hd = isset($_POST['is_hd']) ? 1 : 0;
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $sort_order = (int)$_POST['sort_order'];
                
                $updateQuery = "UPDATE channels SET name = ?, description = ?, logo_url = ?, stream_url = ?, category = ?, country = ?, language = ?, is_hd = ?, is_active = ?, sort_order = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([$name, $description, $logo_url, $stream_url, $category, $country, $language, $is_hd, $is_active, $sort_order, $id]);
                
                $message = 'Channel updated successfully!';
                $messageType = 'success';
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                $deleteQuery = "DELETE FROM channels WHERE id = ?";
                $deleteStmt = $conn->prepare($deleteQuery);
                $deleteStmt->execute([$id]);
                
                $message = 'Channel deleted successfully!';
                $messageType = 'success';
                break;
                
            case 'toggle_status':
                $id = (int)$_POST['id'];
                
                $toggleQuery = "UPDATE channels SET is_active = NOT is_active WHERE id = ?";
                $toggleStmt = $conn->prepare($toggleQuery);
                $toggleStmt->execute([$id]);
                
                $message = 'Channel status updated successfully!';
                $messageType = 'success';
                break;
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get channels with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(name ILIKE ? OR description ILIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($category)) {
    $whereConditions[] = "category = ?";
    $params[] = $category;
}

if (!empty($status)) {
    $whereConditions[] = "is_active = ?";
    $params[] = ($status === 'active') ? 1 : 0;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM channels $whereClause";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($params);
$totalChannels = $countStmt->fetch()['total'];
$totalPages = ceil($totalChannels / $limit);

// Get channels
$channelsQuery = "SELECT * FROM channels $whereClause ORDER BY sort_order, name ASC LIMIT $limit OFFSET $offset";
$channelsStmt = $conn->prepare($channelsQuery);
$channelsStmt->execute($params);
$channels = $channelsStmt->fetchAll();

// Get unique categories
$categoriesQuery = "SELECT DISTINCT category FROM channels WHERE category IS NOT NULL ORDER BY category";
$categoriesStmt = $conn->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Get channel for editing
$editChannel = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editQuery = "SELECT * FROM channels WHERE id = ?";
    $editStmt = $conn->prepare($editQuery);
    $editStmt->execute([$editId]);
    $editChannel = $editStmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Channels Management - GStreaming Admin</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-page">
    <!-- Admin Navigation -->
    <nav class="admin-navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-satellite-dish"></i>
                <span class="logo-text">GStreaming Admin</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link">Users</a>
                </li>
                <li class="nav-item">
                    <a href="packages.php" class="nav-link">Packages</a>
                </li>
                <li class="nav-item">
                    <a href="payments.php" class="nav-link">Payments</a>
                </li>
                <li class="nav-item">
                    <a href="channels.php" class="nav-link active">Channels</a>
                </li>
                <li class="nav-item">
                    <a href="gallery.php" class="nav-link">Gallery</a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">Settings</a>
                </li>
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Admin Main -->
    <main class="admin-main">
        <div class="container">
            <div class="admin-header">
                <h1>Channels Management</h1>
                <p>Manage your streaming channels</p>
                <button class="btn btn-primary" onclick="toggleAddForm()">
                    <i class="fas fa-plus"></i>
                    Add New Channel
                </button>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Add/Edit Channel Form -->
            <div id="channelForm" class="admin-card" style="display: none;">
                <div class="card-header">
                    <h3><?php echo $editChannel ? 'Edit Channel' : 'Add New Channel'; ?></h3>
                    <button class="btn btn-secondary btn-sm" onclick="toggleAddForm()">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                </div>
                
                <form method="POST" class="form-grid">
                    <input type="hidden" name="action" value="<?php echo $editChannel ? 'edit' : 'add'; ?>">
                    <?php if ($editChannel): ?>
                        <input type="hidden" name="id" value="<?php echo $editChannel['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Channel Name *</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($editChannel['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($editChannel['category'] ?? ''); ?>" list="categories">
                        <datalist id="categories">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($editChannel['country'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="language">Language</label>
                        <input type="text" id="language" name="language" value="<?php echo htmlspecialchars($editChannel['language'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="logo_url">Logo URL</label>
                        <input type="url" id="logo_url" name="logo_url" value="<?php echo htmlspecialchars($editChannel['logo_url'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="stream_url">Stream URL</label>
                        <input type="url" id="stream_url" name="stream_url" value="<?php echo htmlspecialchars($editChannel['stream_url'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?php echo $editChannel['sort_order'] ?? 0; ?>" min="0">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($editChannel['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_hd" <?php echo ($editChannel['is_hd'] ?? false) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            HD Quality
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" <?php echo ($editChannel['is_active'] ?? true) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Active
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $editChannel ? 'Update Channel' : 'Add Channel'; ?>
                        </button>
                        <?php if ($editChannel): ?>
                            <a href="channels.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Search and Filter -->
            <div class="admin-card">
                <div class="card-header">
                    <h3>Search & Filter Channels</h3>
                </div>
                
                <form method="GET" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search channels...">
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
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                            <a href="channels.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Channels List -->
            <div class="admin-card">
                <div class="card-header">
                    <h3>Channels List (<?php echo number_format($totalChannels); ?> total)</h3>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Logo</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Country</th>
                                <th>Quality</th>
                                <th>Status</th>
                                <th>Sort</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($channels)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No channels found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($channels as $channel): ?>
                                    <tr>
                                        <td>
                                            <?php if ($channel['logo_url']): ?>
                                                <img src="<?php echo htmlspecialchars($channel['logo_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($channel['name']); ?>"
                                                     class="channel-logo-small"
                                                     onerror="this.src='../assets/images/default-channel.png'">
                                            <?php else: ?>
                                                <div class="channel-logo-small default">
                                                    <i class="fas fa-tv"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="channel-info">
                                                <strong><?php echo htmlspecialchars($channel['name']); ?></strong>
                                                <?php if ($channel['description']): ?>
                                                    <small><?php echo htmlspecialchars(substr($channel['description'], 0, 50)); ?>...</small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($channel['category'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($channel['country'] ?: '-'); ?></td>
                                        <td>
                                            <?php if ($channel['is_hd']): ?>
                                                <span class="badge badge-success">HD</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">SD</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $channel['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $channel['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $channel['sort_order']; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="channels.php?edit=<?php echo $channel['id']; ?>" class="btn btn-sm btn-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to toggle this channel status?')">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?php echo $channel['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-<?php echo $channel['is_active'] ? 'warning' : 'success'; ?>" title="<?php echo $channel['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="fas fa-<?php echo $channel['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this channel? This action cannot be undone.')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $channel['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>" class="btn btn-secondary">
                                <i class="fas fa-chevron-left"></i>
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <span class="pagination-info">
                            Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                        </span>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>" class="btn btn-secondary">
                                Next
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
    <script src="assets/js/admin.js"></script>
    <script>
        function toggleAddForm() {
            const form = document.getElementById('channelForm');
            if (form.style.display === 'none') {
                form.style.display = 'block';
                form.scrollIntoView({ behavior: 'smooth' });
            } else {
                form.style.display = 'none';
            }
        }
        
        // Auto-show form if editing
        <?php if ($editChannel): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('channelForm').style.display = 'block';
            });
        <?php endif; ?>
    </script>
</body>
</html>
