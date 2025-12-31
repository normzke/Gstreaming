<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';
require_once '../lib/cache.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();
CachedQueries::init($db);

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'add_package':
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                $price = (float) $_POST['price'];
                $currency = sanitizeInput($_POST['currency']);
                $duration_days = (int) $_POST['duration_days'];
                $max_devices = (int) $_POST['max_devices'];
                $features = json_encode($_POST['features'] ?? []);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $sort_order = (int) $_POST['sort_order'];

                $insertQuery = "INSERT INTO packages (name, description, price, currency, duration_days, max_devices, features, is_active, sort_order) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->execute([$name, $description, $price, $currency, $duration_days, $max_devices, $features, $is_active, $sort_order]);

                // Invalidate cache
                CachedQueries::invalidateContentCache();

                $message = 'Package added successfully!';
                $messageType = 'success';
                break;

            case 'edit_package':
                $package_id = (int) $_POST['package_id'];
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                $price = (float) $_POST['price'];
                $currency = sanitizeInput($_POST['currency']);
                $duration_days = (int) $_POST['duration_days'];
                $max_devices = (int) $_POST['max_devices'];
                $features = json_encode($_POST['features'] ?? []);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $sort_order = (int) $_POST['sort_order'];

                $updateQuery = "UPDATE packages SET name = ?, description = ?, price = ?, currency = ?, duration_days = ?, max_devices = ?, features = ?, is_active = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([$name, $description, $price, $currency, $duration_days, $max_devices, $features, $is_active, $sort_order, $package_id]);

                // Invalidate cache
                CachedQueries::invalidateContentCache();

                $message = 'Package updated successfully!';
                $messageType = 'success';
                break;

            case 'delete_package':
                $package_id = (int) $_POST['package_id'];

                // Check if package has active subscriptions
                $subQuery = "SELECT COUNT(*) as count FROM user_subscriptions WHERE package_id = ? AND status = 'active' AND end_date > NOW()";
                $subStmt = $conn->prepare($subQuery);
                $subStmt->execute([$package_id]);
                $activeSubs = $subStmt->fetch()['count'];

                if ($activeSubs > 0) {
                    $message = 'Cannot delete package with active subscriptions!';
                    $messageType = 'error';
                } else {
                    $deleteQuery = "DELETE FROM packages WHERE id = ?";
                    $deleteStmt = $conn->prepare($deleteQuery);
                    $deleteStmt->execute([$package_id]);

                    // Invalidate cache
                    CachedQueries::invalidateContentCache();

                    $message = 'Package deleted successfully!';
                    $messageType = 'success';
                }
                break;
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get packages
$packagesQuery = "SELECT p.*, 
                  (SELECT COUNT(*) FROM user_subscriptions us WHERE us.package_id = p.id) as subscription_count,
                  (SELECT COUNT(*) FROM user_subscriptions us WHERE us.package_id = p.id AND us.status = 'active' AND us.end_date > NOW()) as active_subscriptions
                  FROM packages p 
                  ORDER BY p.sort_order ASC, p.price ASC";
$packagesStmt = $conn->prepare($packagesQuery);
$packagesStmt->execute();
$packages = $packagesStmt->fetchAll();

$page_title = 'Packages';
include 'includes/header.php';
?>

<!-- Messages -->
<?php if ($message): ?>
    <div class="admin-card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <div class="alert alert-<?php echo $messageType; ?>"
                style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border-radius: var(--admin-radius); background: <?php echo $messageType === 'success' ? '#D1FAE5' : '#FEE2E2'; ?>; color: <?php echo $messageType === 'success' ? '#065F46' : '#991B1B'; ?>;">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Add Package Button -->
<div style="margin-bottom: 1.5rem;">
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Add New Package
    </button>
</div>

<!-- Packages Table -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">All Packages</h3>
    </div>
    <div class="card-body">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Max Devices</th>
                    <th>Subscriptions</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $package): ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?php echo htmlspecialchars($package['name']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($package['description']); ?></small>
                            </div>
                        </td>
                        <td>
                            <strong><?php echo $package['currency']; ?>
                                <?php echo number_format($package['price'], 2); ?></strong>
                        </td>
                        <td><?php echo $package['duration_days']; ?> days</td>
                        <td><?php echo $package['max_devices']; ?></td>
                        <td>
                            <span class="badge badge-info"><?php echo $package['active_subscriptions']; ?> active</span>
                            <br>
                            <small class="text-muted"><?php echo $package['subscription_count']; ?> total</small>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $package['is_active'] ? 'success' : 'danger'; ?>">
                                <?php echo $package['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-secondary" onclick="editPackage(this)"
                                data-id="<?php echo $package['id']; ?>"
                                data-name="<?php echo htmlspecialchars($package['name']); ?>"
                                data-description="<?php echo htmlspecialchars($package['description']); ?>"
                                data-price="<?php echo $package['price']; ?>"
                                data-currency="<?php echo $package['currency']; ?>"
                                data-duration="<?php echo $package['duration_days']; ?>"
                                data-max_devices="<?php echo $package['max_devices']; ?>"
                                data-sort_order="<?php echo $package['sort_order']; ?>"
                                data-is_active="<?php echo $package['is_active']; ?>"
                                data-features='<?php echo htmlspecialchars($package['features'] ?? "[]", ENT_QUOTES); ?>'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-delete"
                                onclick="deletePackage(<?php echo $package['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Package Modal -->
<div id="packageModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Package</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="packageForm" method="POST">
            <input type="hidden" name="action" id="formAction" value="add_package">
            <input type="hidden" name="package_id" id="packageId">

            <div class="form-group">
                <label for="name">Package Name</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" required></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="currency">Currency</label>
                    <select id="currency" name="currency" required>
                        <option value="KSh">KSh</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="duration_days">Duration (Days)</label>
                    <input type="number" id="duration_days" name="duration_days" required>
                </div>

                <div class="form-group">
                    <label for="max_devices">Max Devices</label>
                    <input type="number" id="max_devices" name="max_devices" required>
                </div>
            </div>

            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" id="sort_order" name="sort_order" value="0">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="is_active" name="is_active" checked>
                    Active Package
                </label>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Package</button>
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
            <p>Are you sure you want to delete this package? This action cannot be undone.</p>
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
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
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

    .badge-info {
        background: #DBEAFE;
        color: #1E40AF;
    }
</style>

<script>
    let currentPackageId = null;

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add Package';
        document.getElementById('formAction').value = 'add_package';
        document.getElementById('packageForm').reset();
        document.getElementById('packageId').value = '';
        document.getElementById('packageModal').style.display = 'flex';
    }

    function editPackage(btn) {
        document.getElementById('modalTitle').textContent = 'Edit Package';
        document.getElementById('formAction').value = 'edit_package';

        // Populate form fields from data attributes
        const dataset = btn.dataset;
        document.getElementById('packageId').value = dataset.id;
        document.getElementById('name').value = dataset.name;
        document.getElementById('description').value = dataset.description;
        document.getElementById('price').value = dataset.price;
        document.getElementById('currency').value = dataset.currency;
        document.getElementById('duration_days').value = dataset.duration;
        document.getElementById('max_devices').value = dataset.max_devices;
        document.getElementById('sort_order').value = dataset.sort_order;
        document.getElementById('is_active').checked = dataset.is_active == '1';

        document.getElementById('packageModal').style.display = 'flex';
    }

    function deletePackage(id) {
        currentPackageId = id;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('packageModal').style.display = 'none';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        currentPackageId = null;
    }

    function confirmDelete() {
        if (currentPackageId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete_package">
            <input type="hidden" name="package_id" value="${currentPackageId}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php include 'includes/footer.php'; ?>