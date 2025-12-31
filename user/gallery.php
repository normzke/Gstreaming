<?php
require_once '../config/config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check user authentication
if (!isLoggedIn()) {
    header('Location: /login');
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Get user's photos with pagination
$page = (int) ($_GET['page'] ?? 1);
$limit = 12;
$offset = ($page - 1) * $limit;

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM user_photos WHERE user_id = ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute([$_SESSION['user_id']]);
$total_photos = $countStmt->fetch()['total'];
$total_pages = ceil($total_photos / $limit);

// Get photos
$photosQuery = "SELECT * FROM user_photos WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$photosStmt = $conn->prepare($photosQuery);
$photosStmt->execute([$_SESSION['user_id'], $limit, $offset]);
$photos = $photosStmt->fetchAll();

// Get statistics
$statsQuery = "SELECT 
               COUNT(*) as total_photos,
               COALESCE(SUM(file_size), 0) as total_size,
               COUNT(CASE WHEN created_at >= NOW() - INTERVAL '1 month' THEN 1 END) as recent_uploads
               FROM user_photos WHERE user_id = ?";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->execute([$_SESSION['user_id']]);
$stats = $statsStmt->fetch();

$total_photos = $stats['total_photos'];
$total_size = round($stats['total_size'] / 1024 / 1024, 1); // Convert to MB
$recent_uploads = $stats['recent_uploads'];

$page_title = 'Gallery';
include 'includes/header.php';
?>

<!-- Upload Section -->
<div class="user-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Upload Photos</h3>
        <p>Add new photos to your gallery</p>
    </div>
    <div class="card-body">
        <form id="uploadForm" enctype="multipart/form-data" style="display: flex; gap: 1rem; align-items: end;">
            <div class="form-group" style="flex: 1;">
                <label for="photos">Select Photos</label>
                <input type="file" id="photos" name="photos[]" multiple accept="image/*" required>
                <small class="text-muted">You can select multiple photos at once (Max 10MB each)</small>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i>
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Gallery Stats -->
<div class="user-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Gallery Statistics</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            <div class="stat-item">
                <h4><?php echo number_format($total_photos); ?></h4>
                <p>Total Photos</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($total_size); ?> MB</h4>
                <p>Total Size</p>
            </div>
            <div class="stat-item">
                <h4><?php echo number_format($recent_uploads); ?></h4>
                <p>This Month</p>
            </div>
        </div>
    </div>
</div>

<!-- Gallery Grid -->
<div class="user-card">
    <div class="card-header">
        <h3 class="card-title">Your Photos (<?php echo count($photos); ?> total)</h3>
        <div class="card-actions">
            <button class="btn btn-secondary" onclick="toggleView()">
                <i class="fas fa-th" id="viewIcon"></i>
                <span id="viewText">Grid View</span>
            </button>
            <button class="btn btn-secondary" onclick="selectAll()">
                <i class="fas fa-check-square"></i>
                Select All
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($photos)): ?>
            <div class="empty-state">
                <i class="fas fa-images"></i>
                <h3>No photos yet</h3>
                <p>Upload your first photo to get started!</p>
            </div>
        <?php else: ?>
            <div class="gallery-grid" id="galleryGrid">
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-card" data-id="<?php echo $photo['id']; ?>">
                        <div class="photo-checkbox">
                            <input type="checkbox" class="photo-select" value="<?php echo $photo['id']; ?>">
                        </div>
                        <div class="photo-image">
                            <img src="<?php echo htmlspecialchars($photo['file_path']); ?>"
                                alt="<?php echo htmlspecialchars($photo['title']); ?>"
                                onclick="viewPhoto(<?php echo $photo['id']; ?>)">
                            <div class="photo-overlay">
                                <button class="btn btn-sm btn-primary" onclick="viewPhoto(<?php echo $photo['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary" onclick="downloadPhoto(<?php echo $photo['id']; ?>)">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deletePhoto(<?php echo $photo['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="photo-info">
                            <h4 class="photo-title"><?php echo htmlspecialchars($photo['title']); ?></h4>
                            <p class="photo-meta">
                                <?php echo date('M j, Y', strtotime($photo['created_at'])); ?> •
                                <?php echo number_format($photo['file_size'] / 1024, 1); ?> KB
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Bulk Actions -->
            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <div class="bulk-actions-content">
                    <span id="selectedCount">0 photos selected</span>
                    <div class="bulk-buttons">
                        <button class="btn btn-danger" onclick="deleteSelected()">
                            <i class="fas fa-trash"></i>
                            Delete Selected
                        </button>
                        <button class="btn btn-secondary" onclick="downloadSelected()">
                            <i class="fas fa-download"></i>
                            Download Selected
                        </button>
                        <button class="btn btn-secondary" onclick="clearSelection()">
                            <i class="fas fa-times"></i>
                            Clear Selection
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Photo Modal -->
<div id="photoModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Photo Viewer</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <img id="modalImage" src="" alt="" style="max-width: 100%; height: auto;">
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="downloadCurrentPhoto()">
                <i class="fas fa-download"></i>
                Download
            </button>
            <button class="btn btn-danger" onclick="deleteCurrentPhoto()">
                <i class="fas fa-trash"></i>
                Delete
            </button>
        </div>
    </div>
</div>

<style>
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .photo-card {
        position: relative;
        background: white;
        border-radius: var(--user-radius);
        box-shadow: var(--user-shadow);
        border: 1px solid var(--user-border);
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .photo-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .photo-checkbox {
        position: absolute;
        top: 0.5rem;
        left: 0.5rem;
        z-index: 2;
    }

    .photo-checkbox input {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .photo-image {
        position: relative;
        aspect-ratio: 1;
        overflow: hidden;
    }

    .photo-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        cursor: pointer;
    }

    .photo-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .photo-card:hover .photo-overlay {
        opacity: 1;
    }

    .photo-info {
        padding: 1rem;
    }

    .photo-title {
        margin: 0 0 0.5rem 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--user-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .photo-meta {
        margin: 0;
        font-size: 0.75rem;
        color: var(--user-text-light);
    }

    .stat-item {
        text-align: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: var(--user-radius);
    }

    .stat-item h4 {
        margin: 0 0 0.5rem 0;
        font-size: 1.5rem;
        color: var(--user-primary);
    }

    .stat-item p {
        margin: 0;
        color: var(--user-text-light);
        font-size: 0.875rem;
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
        margin: 0;
    }

    .bulk-actions {
        position: fixed;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        border-radius: var(--user-radius);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border: 1px solid var(--user-border);
        z-index: 1000;
    }

    .bulk-actions-content {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.5rem;
    }

    .bulk-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .card-actions {
        display: flex;
        gap: 0.5rem;
    }

    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        border-radius: var(--user-radius);
        max-width: 90vw;
        max-height: 90vh;
        overflow: hidden;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--user-border);
    }

    .modal-body {
        padding: 1.5rem;
        text-align: center;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--user-border);
    }

    .close {
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--user-text-light);
    }

    .close:hover {
        color: var(--user-text);
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

    .form-group input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--user-border);
        border-radius: var(--user-radius);
        font-size: 1rem;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--user-primary);
        box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
    }

    .text-muted {
        color: var(--user-text-light);
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .gallery-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .bulk-actions-content {
            flex-direction: column;
            align-items: stretch;
        }

        .bulk-buttons {
            justify-content: center;
        }
    }
</style>

<script>
    let currentPhotoId = null;
    let isGridView = true;

    function toggleView() {
        isGridView = !isGridView;
        const grid = document.getElementById('galleryGrid');
        const icon = document.getElementById('viewIcon');
        const text = document.getElementById('viewText');

        if (isGridView) {
            grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(200px, 1fr))';
            icon.className = 'fas fa-th';
            text.textContent = 'Grid View';
        } else {
            grid.style.gridTemplateColumns = '1fr';
            icon.className = 'fas fa-list';
            text.textContent = 'List View';
        }
    }

    function selectAll() {
        const checkboxes = document.querySelectorAll('.photo-select');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);

        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
        });

        updateBulkActions();
    }

    function clearSelection() {
        const checkboxes = document.querySelectorAll('.photo-select');
        checkboxes.forEach(cb => {
            cb.checked = false;
        });
        updateBulkActions();
    }

    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.photo-select:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');

        if (checkboxes.length > 0) {
            bulkActions.style.display = 'block';
            selectedCount.textContent = `${checkboxes.length} photo${checkboxes.length > 1 ? 's' : ''} selected`;
        } else {
            bulkActions.style.display = 'none';
        }
    }

    function viewPhoto(photoId) {
        currentPhotoId = photoId;
        const photo = document.querySelector(`[data-id="${photoId}"]`);
        const img = photo.querySelector('img');
        const title = photo.querySelector('.photo-title').textContent;

        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalImage').src = img.src;
        document.getElementById('modalImage').alt = img.alt;
        document.getElementById('photoModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('photoModal').style.display = 'none';
        currentPhotoId = null;
    }

    function downloadPhoto(photoId) {
        window.location.href = `download-photo?id=${photoId}`;
    }

    function downloadCurrentPhoto() {
        if (currentPhotoId) {
            downloadPhoto(currentPhotoId);
        }
    }

    function deletePhoto(photoId) {
        if (confirm('Are you sure you want to delete this photo?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete_photo">
            <input type="hidden" name="photo_id" value="${photoId}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deleteCurrentPhoto() {
        if (currentPhotoId) {
            deletePhoto(currentPhotoId);
            closeModal();
        }
    }

    function deleteSelected() {
        const checkboxes = document.querySelectorAll('.photo-select:checked');
        if (checkboxes.length === 0) return;

        if (confirm(`Are you sure you want to delete ${checkboxes.length} photo${checkboxes.length > 1 ? 's' : ''}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete_selected">
            ${Array.from(checkboxes).map(cb => `<input type="hidden" name="photo_ids[]" value="${cb.value}">`).join('')}
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function downloadSelected() {
        const checkboxes = document.querySelectorAll('.photo-select:checked');
        if (checkboxes.length === 0) return;

        const ids = Array.from(checkboxes).map(cb => cb.value);
        window.location.href = `download-photos?ids=${ids.join(',')}`;
    }

    // Add event listeners
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.photo-select');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });

        // Upload form
        document.getElementById('uploadForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'upload_photos');

            fetch('upload-photos', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error uploading photos: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error uploading photos');
                });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>