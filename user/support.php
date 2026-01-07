<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../lib/functions.php';

// Check user authentication
if (!isLoggedIn()) {
    header('Location: /login');
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Get user's support tickets
$ticketsQuery = "SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$ticketsStmt = $conn->prepare($ticketsQuery);
$ticketsStmt->execute([$_SESSION['user_id']]);
$tickets = $ticketsStmt->fetchAll();

$page_title = 'Support';
include 'includes/header.php';
?>

<!-- Support Tickets -->
<div class="user-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Support Tickets</h3>
        <button class="btn btn-primary" onclick="openNewTicketModal()">
            <i class="fas fa-plus"></i>
            New Ticket
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($tickets)): ?>
            <div class="empty-state">
                <i class="fas fa-ticket-alt"></i>
                <h3>No support tickets</h3>
                <p>Create a new ticket to get help with your account.</p>
            </div>
        <?php else: ?>
            <div class="tickets-list">
                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket-item">
                        <div class="ticket-header">
                            <h4 class="ticket-title"><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                            <span class="ticket-status status-<?php echo $ticket['status']; ?>">
                                <?php echo ucfirst($ticket['status']); ?>
                            </span>
                        </div>
                        <div class="ticket-meta">
                            <span class="ticket-id">#<?php echo $ticket['id']; ?></span>
                            <span class="ticket-date"><?php echo date('M j, Y H:i', strtotime($ticket['created_at'])); ?></span>
                            <span class="ticket-priority priority-<?php echo $ticket['priority']; ?>">
                                <?php echo ucfirst($ticket['priority']); ?>
                            </span>
                        </div>
                        <div class="ticket-actions">
                            <button class="btn btn-secondary" onclick="viewTicket(<?php echo $ticket['id']; ?>)">
                                <i class="fas fa-eye"></i>
                                View
                            </button>
                            <?php if ($ticket['status'] !== 'closed'): ?>
                                <button class="btn btn-primary" onclick="replyTicket(<?php echo $ticket['id']; ?>)">
                                    <i class="fas fa-reply"></i>
                                    Reply
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- FAQ Section -->
<div class="user-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Frequently Asked Questions</h3>
    </div>
    <div class="card-body">
        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h4>How do I subscribe to a package?</h4>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>To subscribe to a package, go to the Packages page, select your desired package, and follow the
                        payment process. You can pay securely using <strong>M-PESA</strong> or <strong>Card (Visa,
                            Mastercard, Apple Pay)</strong>.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h4>How do I watch channels?</h4>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Once you have an active subscription, download the <strong>BingeTV Native App</strong> for your
                        Android TV, Samsung Tizen, or LG WebOS from our <a href="/apps">apps page</a>. Alternatively,
                        you can use <strong>TiviMate</strong> on Firestick and other devices.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h4>How do I cancel my subscription?</h4>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>You can cancel your subscription from your Dashboard. Go to the Subscriptions section and click
                        on "Cancel Subscription". Your access will continue until the end of your current billing
                        period.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h4>What payment methods do you accept?</h4>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>We accept M-PESA, Airtel Money, and other mobile money services. We also accept credit/debit
                        cards for international payments.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h4>How many devices can I use?</h4>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>The number of devices depends on your package. Basic packages allow 1 device, while premium
                        packages allow up to 5 devices simultaneously.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Information -->
<div class="user-card">
    <div class="card-header">
        <h3 class="card-title">Contact Information</h3>
    </div>
    <div class="card-body">
        <div class="contact-grid">
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="contact-info">
                    <h4>Email Support</h4>
                    <p>support@bingetv.co.ke</p>
                    <small>We respond within 24 hours</small>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="contact-info">
                    <h4>Phone Support</h4>
                    <p>+254 768 704 834</p>
                    <small>Mon-Fri 8AM-6PM EAT</small>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div class="contact-info">
                    <h4>WhatsApp</h4>
                    <p>+254 768 704 834</p>
                    <small>Quick support via WhatsApp</small>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="contact-info">
                    <h4>Response Time</h4>
                    <p>Within 24 hours</p>
                    <small>For email and ticket support</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Ticket Modal -->
<div id="newTicketModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Support Ticket</h3>
            <span class="close" onclick="closeNewTicketModal()">&times;</span>
        </div>
        <form id="newTicketForm" method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <input type="text" id="subject" name="subject" required>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="general">General</option>
                        <option value="billing">Billing</option>
                        <option value="technical">Technical</option>
                        <option value="account">Account</option>
                        <option value="streaming">Streaming</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" rows="5" required
                        placeholder="Describe your issue in detail..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeNewTicketModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Ticket</button>
            </div>
        </form>
    </div>
</div>

<style>
    .tickets-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .ticket-item {
        background: white;
        border: 1px solid var(--user-border);
        border-radius: var(--user-radius);
        padding: 1.5rem;
        transition: box-shadow 0.2s ease;
    }

    .ticket-item:hover {
        box-shadow: var(--user-shadow);
    }

    .ticket-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .ticket-title {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--user-text);
    }

    .ticket-status {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-open {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .status-pending {
        background: #FEF3C7;
        color: #92400E;
    }

    .status-resolved {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-closed {
        background: #F3F4F6;
        color: #6B7280;
    }

    .ticket-meta {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        color: var(--user-text-light);
    }

    .ticket-priority {
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .priority-low {
        background: #E5E7EB;
        color: #6B7280;
    }

    .priority-medium {
        background: #FEF3C7;
        color: #92400E;
    }

    .priority-high {
        background: #FEE2E2;
        color: #991B1B;
    }

    .priority-urgent {
        background: #FEE2E2;
        color: #991B1B;
        font-weight: 600;
    }

    .ticket-actions {
        display: flex;
        gap: 0.5rem;
    }

    .faq-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .faq-item {
        border: 1px solid var(--user-border);
        border-radius: var(--user-radius);
        overflow: hidden;
    }

    .faq-question {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: #f8f9fa;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .faq-question:hover {
        background: #e9ecef;
    }

    .faq-question h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 500;
        color: var(--user-text);
    }

    .faq-question i {
        transition: transform 0.2s ease;
        color: var(--user-text-light);
    }

    .faq-item.active .faq-question i {
        transform: rotate(180deg);
    }

    .faq-answer {
        padding: 0 1.5rem;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
    }

    .faq-item.active .faq-answer {
        padding: 1rem 1.5rem;
        max-height: 200px;
    }

    .faq-answer p {
        margin: 0;
        color: var(--user-text-light);
        line-height: 1.6;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: var(--user-radius);
        border: 1px solid var(--user-border);
    }

    .contact-icon {
        width: 50px;
        height: 50px;
        background: var(--user-primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .contact-info h4 {
        margin: 0 0 0.25rem 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--user-text);
    }

    .contact-info p {
        margin: 0 0 0.25rem 0;
        font-weight: 500;
        color: var(--user-text);
    }

    .contact-info small {
        color: var(--user-text-light);
        font-size: 0.75rem;
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
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
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

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--user-border);
        border-radius: var(--user-radius);
        font-size: 1rem;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--user-primary);
        box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }

        .ticket-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .ticket-meta {
            flex-direction: column;
            gap: 0.25rem;
        }
    }
</style>

<script>
    function openNewTicketModal() {
        document.getElementById('newTicketModal').style.display = 'flex';
    }

    function closeNewTicketModal() {
        document.getElementById('newTicketModal').style.display = 'none';
        document.getElementById('newTicketForm').reset();
    }

    function toggleFAQ(element) {
        const faqItem = element.closest('.faq-item');
        faqItem.classList.toggle('active');
    }

    function viewTicket(ticketId) {
        window.location.href = `ticket-details?id=${ticketId}`;
    }

    function replyTicket(ticketId) {
        window.location.href = `ticket-details?id=${ticketId}&reply=1`;
    }

    // New ticket form submission
    document.getElementById('newTicketForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'create_ticket');

        fetch('create-ticket', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Ticket created successfully!');
                    closeNewTicketModal();
                    location.reload();
                } else {
                    alert('Error creating ticket: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating ticket');
            });
    });
</script>

<?php include 'includes/footer.php'; ?>