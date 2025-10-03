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

$page_title = 'Channels';
include 'includes/header.php';
?>

<!-- Messages -->
<?php if ($message): ?>
<div class="admin-card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <div class="alert alert-<?php echo $messageType; ?>" style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border-radius: var(--admin-radius); background: <?php echo $messageType === 'success' ? '#D1FAE5' : '#FEE2E2'; ?>; color: <?php echo $messageType === 'success' ? '#065F46' : '#991B1B'; ?>;">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Add Channel Button -->
<div style="margin-bottom: 1.5rem;">
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Add New Channel
    </button>
</div>

<!-- Channels Table -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">All Channels</h3>
    </div>
    <div class="card-body">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Stream URL</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($channels as $channel): ?>
                <tr>
                    <td>
                        <div>
                            <strong><?php echo htmlspecialchars($channel['name']); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($channel['description']); ?></small>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($channel['category']); ?></td>
                    <td>
                        <code style="font-size: 0.75rem; background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px;">
                            <?php echo htmlspecialchars(substr($channel['stream_url'], 0, 50)) . '...'; ?>
                        </code>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $channel['is_active'] ? 'success' : 'danger'; ?>">
                            <?php echo $channel['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-secondary" onclick="editChannel(<?php echo $channel['id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-delete" onclick="deleteChannel(<?php echo $channel['id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Channel Modal -->
<div id="channelModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Channel</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="channelForm" method="POST">
            <input type="hidden" name="action" id="formAction" value="add_channel">
            <input type="hidden" name="channel_id" id="channelId">
            
            <div class="form-group">
                <label for="name">Channel Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="News">News</option>
                    <option value="Sports">Sports</option>
                    <option value="Entertainment">Entertainment</option>
                    <option value="Movies">Movies</option>
                    <option value="Kids">Kids</option>
                    <option value="Documentary">Documentary</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="stream_url">Stream URL</label>
                <input type="url" id="stream_url" name="stream_url" required>
            </div>
            
            <div class="form-group">
                <label for="logo_url">Logo URL</label>
                <input type="url" id="logo_url" name="logo_url">
            </div>
            
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" id="sort_order" name="sort_order" value="0">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="is_active" name="is_active" checked>
                    Active Channel
                </label>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Channel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this channel? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: var(--admin-radius);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--admin-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--admin-text-light);
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid var(--admin-border);
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
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

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius);
    font-size: 1rem;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
}

.text-muted {
    color: var(--admin-text-light);
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.badge-success {
    background: #D1FAE5;
    color: #065F46;
}

.badge-danger {
    background: #FEE2E2;
    color: #991B1B;
}
</style>

<script>
let currentChannelId = null;

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Channel';
    document.getElementById('formAction').value = 'add_channel';
    document.getElementById('channelForm').reset();
    document.getElementById('channelId').value = '';
    document.getElementById('channelModal').style.display = 'flex';
}

function editChannel(id) {
    document.getElementById('modalTitle').textContent = 'Edit Channel';
    document.getElementById('formAction').value = 'edit_channel';
    document.getElementById('channelId').value = id;
    document.getElementById('channelModal').style.display = 'flex';
}

function deleteChannel(id) {
    currentChannelId = id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('channelModal').style.display = 'none';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    currentChannelId = null;
}

function confirmDelete() {
    if (currentChannelId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_channel">
            <input type="hidden" name="channel_id" value="${currentChannelId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
