// Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function () {
    // Initialize dashboard functionality
    initDashboard();
    initSetupTabs();
    initModals();
});

// Initialize dashboard
function initDashboard() {
    // Add smooth animations to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        card.style.transitionDelay = `${index * 0.1}s`;

        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });

    // Add hover effects to subscription cards
    const subscriptionCards = document.querySelectorAll('.subscription-card');
    subscriptionCards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
    });
}

// Setup tabs functionality
function initSetupTabs() {
    const setupTabs = document.querySelectorAll('.setup-tab');
    const setupInstructions = document.querySelectorAll('.setup-instruction');

    setupTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const deviceName = this.dataset.device;

            // Update tab buttons
            setupTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Update instructions
            setupInstructions.forEach(instruction => {
                instruction.classList.remove('active');
            });
            document.getElementById(deviceName).classList.add('active');
        });
    });
}

// Modal functionality
function initModals() {
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.modal-close');

    // Close modal when clicking close button
    closeButtons.forEach(button => {
        button.addEventListener('click', function () {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
    });

    // Close modal when clicking outside
    modals.forEach(modal => {
        modal.addEventListener('click', function (event) {
            if (event.target === this) {
                closeModal(this);
            }
        });
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            const openModal = document.querySelector('.modal[style*="block"]');
            if (openModal) {
                closeModal(openModal);
            }
        }
    });
}

// Show renewal modal
function showRenewalModal() {
    const modal = document.getElementById('renewalModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';

        // Animate in
        setTimeout(() => {
            modal.querySelector('.modal-content').style.transform = 'scale(1)';
        }, 10);
    }
}

// Close renewal modal
function closeRenewalModal() {
    const modal = document.getElementById('renewalModal');
    if (modal) {
        closeModal(modal);
    }
}

// Close modal helper
function closeModal(modal) {
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Copy credential to clipboard
function copyCredential(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.select();
        input.setSelectionRange(0, 99999); // For mobile devices

        navigator.clipboard.writeText(input.value).then(() => {
            showNotification('Copied to clipboard!', 'success');
        }).catch(() => {
            // Fallback for older browsers
            document.execCommand('copy');
            showNotification('Copied to clipboard!', 'success');
        });
    }
}

// Copy all credentials
function copyAllCredentials() {
    const streamingUrl = document.getElementById('streaming-url')?.value || '';
    const username = document.getElementById('streaming-username')?.value || '';
    const password = document.getElementById('streaming-password')?.value || '';

    const credentials = `Streaming URL: ${streamingUrl}\nUsername: ${username}\nPassword: ${password}`;

    navigator.clipboard.writeText(credentials).then(() => {
        showNotification('All credentials copied to clipboard!', 'success');
    }).catch(() => {
        // Fallback
        const textArea = document.createElement('textarea');
        textArea.value = credentials;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('All credentials copied to clipboard!', 'success');
    });
}

// Toggle password visibility
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;

    if (input.type === 'password') {
        input.type = 'text';
        button.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        input.type = 'password';
        button.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

// Show subscription details
function showSubscriptionDetails(subscriptionId) {
    // In a real implementation, this would fetch and display detailed subscription information
    showNotification('Subscription details feature coming soon!', 'info');
}

// Renew subscription
function renewSubscription(subscriptionId) {
    showRenewalModal();
}

// Proceed with renewal
function proceedWithRenewal() {
    const selectedMethod = document.querySelector('input[name="renewal_method"]:checked').value;

    if (selectedMethod === 'same') {
        // Redirect to payment for same package renewal
        window.location.href = 'subscribe.php?package=1&action=renew'; // Default package
    } else if (selectedMethod === 'upgrade') {
        // Redirect to packages page for upgrade
        window.location.href = 'index.php#packages';
    }

    closeRenewalModal();
}

// Renewal method selection
document.addEventListener('change', function (event) {
    if (event.target.name === 'renewal_method') {
        const renewalMethods = document.querySelectorAll('.renewal-method');
        renewalMethods.forEach(method => {
            method.classList.remove('active');
        });

        const selectedMethod = document.querySelector(`.renewal-method[data-method="${event.target.value}"]`);
        if (selectedMethod) {
            selectedMethod.classList.add('active');
        }
    }
});

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${getNotificationColor(type)};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 3000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 400px;
    `;

    // Add to page
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function getNotificationColor(type) {
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    return colors[type] || '#3b82f6';
}

// Auto-refresh subscription status
function refreshSubscriptionStatus() {
    // Check if any subscriptions are expiring soon
    const expiryWarnings = document.querySelectorAll('.expiry-warning');
    expiryWarnings.forEach(warning => {
        const daysText = warning.querySelector('span').textContent;
        const days = parseInt(daysText.match(/\d+/)[0]);

        if (days <= 3) {
            warning.style.background = 'rgba(239, 68, 68, 0.1)';
            warning.style.borderColor = 'var(--error-color)';
            warning.style.color = 'var(--error-color)';
        }
    });
}

// Initialize auto-refresh
setInterval(refreshSubscriptionStatus, 60000); // Check every minute

// Add notification styles
const notificationStyles = `
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 0;
        margin-left: auto;
    }
    
    .notification-close:hover {
        opacity: 0.8;
    }
`;

// Inject notification styles
const bingetvDashboardStyleSheet = document.createElement('style');
bingetvDashboardStyleSheet.textContent = notificationStyles;
document.head.appendChild(bingetvDashboardStyleSheet);
