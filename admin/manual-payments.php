<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

$message = '';
$messageType = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $submissionId = (int)($_POST['submission_id'] ?? 0);
    $adminNotes = trim($_POST['admin_notes'] ?? '');
    
    if ($action === 'approve' && $submissionId) {
        try {
            $conn->beginTransaction();
            
            // Get submission details
            $subQuery = "SELECT * FROM manual_payment_submissions WHERE id = ?";
            $subStmt = $conn->prepare($subQuery);
            $subStmt->execute([$submissionId]);
            $submission = $subStmt->fetch();
            
            if ($submission && $submission['status'] === 'pending') {
                // Update payment status
                $updatePayment = "UPDATE payments SET status = 'completed', updated_at = NOW() WHERE id = ?";
                $conn->prepare($updatePayment)->execute([$submission['payment_id']]);
                
                // Update submission status
                $updateSub = "UPDATE manual_payment_submissions 
                             SET status = 'approved', admin_id = ?, admin_notes = ?, reviewed_at = NOW() 
                             WHERE id = ?";
                $conn->prepare($updateSub)->execute([$_SESSION['admin_id'], $adminNotes, $submissionId]);
                
                // Create subscription
                $packageQuery = "SELECT * FROM packages WHERE id = ?";
                $packageStmt = $conn->prepare($packageQuery);
                $packageStmt->execute([$submission['package_id']]);
                $package = $packageStmt->fetch();
                
                if ($package) {
                    $startDate = date('Y-m-d H:i:s');
                    $endDate = date('Y-m-d H:i:s', strtotime("+{$package['duration_days']} days"));
                    
                    $subInsert = "INSERT INTO user_subscriptions (user_id, package_id, status, start_date, end_date)
                                 VALUES (?, ?, 'active', ?, ?)";
                    $conn->prepare($subInsert)->execute([$submission['user_id'], $package['id'], $startDate, $endDate]);
                }
                
                $conn->commit();
                $message = 'Payment approved and subscription activated!';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = 'Error approving payment: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'reject' && $submissionId) {
        try {
            $updateSub = "UPDATE manual_payment_submissions 
                         SET status = 'rejected', admin_id = ?, admin_notes = ?, reviewed_at = NOW() 
                         WHERE id = ?";
            $conn->prepare($updateSub)->execute([$_SESSION['admin_id'], $adminNotes, $submissionId]);
            
            $message = 'Payment rejected.';
            $messageType = 'info';
        } catch (Exception $e) {
            $message = 'Error rejecting payment: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get all pending submissions
$pendingQuery = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, p.name as package_name
                FROM manual_payment_submissions s
                JOIN users u ON s.user_id = u.id
                JOIN packages p ON s.package_id = p.id
                WHERE s.status = 'pending'
                ORDER BY s.submitted_at DESC";
$pendingStmt = $conn->query($pendingQuery);
$pendingSubmissions = $pendingStmt->fetchAll();

// Get recent reviewed submissions
$reviewedQuery = "SELECT s.*, u.first_name, u.last_name, u.email, a.username as admin_username
                 FROM manual_payment_submissions s
                 JOIN users u ON s.user_id = u.id
                 LEFT JOIN admin_users a ON s.admin_id = a.id
                 WHERE s.status IN ('approved', 'rejected')
                 ORDER BY s.reviewed_at DESC
                 LIMIT 20";
$reviewedStmt = $conn->query($reviewedQuery);
$reviewedSubmissions = $reviewedStmt->fetchAll();

$page_title = 'Manual M-Pesa Confirmations';
include 'includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-check-circle"></i> Manual M-Pesa Payment Confirmations</h1>
        <p>Review and approve user-submitted M-Pesa confirmations</p>
    </div>
    
    <?php if ($message): ?>
        <div class="admin-card">
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Pending Submissions -->
    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-clock"></i> Pending Confirmations (<?php echo count($pendingSubmissions); ?>)</h2>
        </div>
        <div class="card-body">
            <?php if (empty($pendingSubmissions)): ?>
                <p style="text-align: center; color: #999; padding: 2rem;">
                    <i class="fas fa-check-circle" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                    No pending confirmations
                </p>
            <?php else: ?>
                <?php foreach ($pendingSubmissions as $sub): ?>
                    <div class="submission-card" style="background: #f9f9f9; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 8px; border-left: 4px solid #ffc107;">
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                            <div>
                                <h3 style="margin: 0 0 1rem 0;">
                                    <?php echo htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']); ?>
                                    <span style="background: #ffc107; color: #000; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; margin-left: 0.5rem;">PENDING</span>
                                </h3>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($sub['email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($sub['phone']); ?></p>
                                <p><strong>Package:</strong> <?php echo htmlspecialchars($sub['package_name']); ?></p>
                                <p><strong>Amount:</strong> <span style="color: #8B0000; font-weight: 700; font-size: 1.2rem;">KSh <?php echo number_format($sub['amount'], 2); ?></span></p>
                                <p><strong>M-Pesa Code:</strong> <code style="background: #e1e1e1; padding: 0.25rem 0.5rem; border-radius: 4px;"><?php echo htmlspecialchars($sub['mpesa_code']); ?></code></p>
                                <p><strong>Submitted:</strong> <?php echo date('M d, Y h:i A', strtotime($sub['submitted_at'])); ?></p>
                                
                                <div style="background: white; padding: 1rem; border-radius: 4px; margin-top: 1rem;">
                                    <strong>M-Pesa Message:</strong>
                                    <pre style="white-space: pre-wrap; font-family: monospace; font-size: 0.9rem; margin: 0.5rem 0;"><?php echo htmlspecialchars($sub['mpesa_message']); ?></pre>
                                </div>
                            </div>
                            
                            <div>
                                <form method="POST" style="margin-bottom: 1rem;">
                                    <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                    <div class="form-group">
                                        <label>Admin Notes:</label>
                                        <textarea name="admin_notes" style="width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid #ddd;" rows="3" placeholder="Optional notes..."></textarea>
                                    </div>
                                    <button type="submit" name="action" value="approve" style="width: 100%; padding: 0.75rem; background: #28a745; color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; margin-bottom: 0.5rem;">
                                        <i class="fas fa-check"></i> Approve & Activate
                                    </button>
                                    <button type="submit" name="action" value="reject" style="width: 100%; padding: 0.75rem; background: #dc3545; color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer;">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recently Reviewed -->
    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-history"></i> Recently Reviewed</h2>
        </div>
        <div class="card-body">
            <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th style="padding: 0.75rem; text-align: left;">User</th>
                        <th style="padding: 0.75rem; text-align: left;">Amount</th>
                        <th style="padding: 0.75rem; text-align: left;">Code</th>
                        <th style="padding: 0.75rem; text-align: left;">Status</th>
                        <th style="padding: 0.75rem; text-align: left;">Reviewed</th>
                        <th style="padding: 0.75rem; text-align: left;">Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviewedSubmissions as $sub): ?>
                        <tr style="border-bottom: 1px solid #e1e1e1;">
                            <td style="padding: 0.75rem;"><?php echo htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']); ?></td>
                            <td style="padding: 0.75rem;">KSh <?php echo number_format($sub['amount'], 2); ?></td>
                            <td style="padding: 0.75rem;"><code><?php echo htmlspecialchars($sub['mpesa_code']); ?></code></td>
                            <td style="padding: 0.75rem;">
                                <span style="background: <?php echo $sub['status'] === 'approved' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                    <?php echo strtoupper($sub['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 0.75rem;"><?php echo date('M d, h:i A', strtotime($sub['reviewed_at'])); ?></td>
                            <td style="padding: 0.75rem;"><?php echo htmlspecialchars($sub['admin_username'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

