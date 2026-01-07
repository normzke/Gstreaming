// Subscription Page JavaScript

document.addEventListener('DOMContentLoaded', function () {
    // Initialize subscription page
    initSubscriptionFlow();
    initAuthTabs();
    initDeviceTabs();
    initModals();
});

// Global variables
let currentStep = 1;
let currentTransaction = null;
let paymentCheckInterval = null;

// Initialize subscription flow
function initSubscriptionFlow() {
    // Set initial step based on user login status
    if (userData) {
        currentStep = 2;
        updateStepIndicator();
        showStep(2);
    }
}

// Step Navigation Functions
function proceedToRegister() {
    showStep(2);
    document.getElementById('register-form').classList.add('active');
    document.getElementById('login-form').classList.remove('active');
    document.querySelector('[data-tab="register"]').classList.add('active');
    document.querySelector('[data-tab="login"]').classList.remove('active');
}

function proceedToLogin() {
    showStep(2);
    document.getElementById('register-form').classList.remove('active');
    document.getElementById('login-form').classList.add('active');
    document.querySelector('[data-tab="login"]').classList.add('active');
    document.querySelector('[data-tab="register"]').classList.remove('active');
}

function proceedToPayment() {
    currentStep = 2;
    updateStepIndicator();
    showStep(2);
}

function goBack() {
    if (currentStep > 1) {
        currentStep--;
        updateStepIndicator();
        showStep(currentStep);
    }
}

function showStep(stepNumber) {
    // Hide all steps
    const steps = document.querySelectorAll('.step-content');
    steps.forEach(step => step.classList.remove('active'));

    // Show current step
    const currentStepElement = document.getElementById(`step-${stepNumber}`);
    if (currentStepElement) {
        currentStepElement.classList.add('active');
        currentStepElement.scrollIntoView({ behavior: 'smooth' });
    }
}

function updateStepIndicator() {
    const steps = document.querySelectorAll('.step');
    steps.forEach((step, index) => {
        step.classList.remove('active', 'completed');
        if (index + 1 < currentStep) {
            step.classList.add('completed');
        } else if (index + 1 === currentStep) {
            step.classList.add('active');
        }
    });
}

// Auth Tabs
function initAuthTabs() {
    const authTabs = document.querySelectorAll('.auth-tab');
    authTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const tabName = this.dataset.tab;

            // Update tab buttons
            authTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Update forms
            document.querySelectorAll('.auth-form').forEach(form => {
                form.classList.remove('active');
            });
            document.getElementById(`${tabName}-form`).classList.add('active');
        });
    });

    // Handle form submissions
    document.getElementById('registrationForm').addEventListener('submit', handleRegistration);
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
}

// Registration Handler
async function handleRegistration(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    // Validate form
    if (data.password !== data.confirm_password) {
        showNotification('Passwords do not match', 'error');
        return;
    }

    try {
        showLoading(e.target.querySelector('button[type="submit"]'));

        const response = await fetch('api/auth/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Registration successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(result.message || 'Registration failed', 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
    } finally {
        hideLoading(e.target.querySelector('button[type="submit"]'));
    }
}

// Login Handler
async function handleLogin(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    try {
        showLoading(e.target.querySelector('button[type="submit"]'));

        const response = await fetch('api/auth/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Login successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(result.message || 'Login failed', 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
    } finally {
        hideLoading(e.target.querySelector('button[type="submit"]'));
    }
}

// Payment Method Selection
document.addEventListener('click', function (e) {
    if (e.target.closest('.payment-option')) {
        const option = e.target.closest('.payment-option');
        const method = option.dataset.method;

        // Update selection UI
        document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('active'));
        option.classList.add('active');

        // Update radio button
        option.querySelector('input[type="radio"]').checked = true;

        // Show/Hide forms
        document.querySelectorAll('.payment-form').forEach(form => form.style.display = 'none');
        const targetForm = document.getElementById(`${method}-form`);
        if (targetForm) targetForm.style.display = 'block';

        // Update pay button
        const payBtn = document.getElementById('pay-button');
        const payIcon = document.getElementById('pay-icon');
        const payText = document.getElementById('pay-text');

        if (method === 'mpesa') {
            payIcon.className = 'fas fa-mobile-alt';
        } else {
            payIcon.className = 'fas fa-credit-card';
        }
    }
});

function handlePayment() {
    const method = document.querySelector('input[name="payment_method"]:checked').value;
    if (method === 'mpesa') {
        initiateMpesaPayment();
    } else if (method === 'paystack') {
        initiatePaystackPayment();
    }
}

// Payment Functions
async function initiateMpesaPayment() {
    const phoneNumber = document.getElementById('phone_number').value;

    if (!phoneNumber || phoneNumber.length < 10) {
        showNotification('Please enter a valid phone number', 'error');
        return;
    }

    try {
        const payBtn = document.getElementById('pay-button');
        showLoading(payBtn);

        const response = await fetch('api/payment/initiate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                package_id: packageData.id,
                phone_number: phoneNumber,
                amount: packageData.price,
                devices: document.querySelector('input[name="devices"]') ? document.querySelector('input[name="devices"]').value : 1
            })
        });

        const result = await response.json();

        if (result.success) {
            currentTransaction = result.transaction;
            document.getElementById('account-number').textContent = result.account_number;
            document.getElementById('transaction-id').textContent = result.transaction_id;
            document.getElementById('payment-phone').textContent = phoneNumber;

            currentStep = 3;
            updateStepIndicator();
            showStep(3);

            // Start checking payment status
            startPaymentStatusCheck();

            showNotification('Payment initiated! Please complete on your phone.', 'success');
        } else {
            showNotification(result.message || 'Payment initiation failed', 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
    } finally {
        hideLoading(document.getElementById('pay-button'));
    }
}

async function initiatePaystackPayment() {
    try {
        const payBtn = document.getElementById('pay-button');
        showLoading(payBtn);

        // Use current package settings
        const devices = document.querySelector('input[name="devices"]') ? document.querySelector('input[name="devices"]').value : 1;

        // Initialize on server first to get a reference
        const response = await fetch('api/payment/initiate_paystack.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                package_id: packageData.id,
                email: userData.email,
                amount: packageData.price,
                devices: devices
            })
        });

        const result = await response.json();

        if (!result.success) {
            showNotification(result.message || 'Failed to initialize Paystack payment', 'error');
            hideLoading(payBtn);
            return;
        }

        const handler = PaystackPop.setup({
            key: result.public_key,
            email: userData.email,
            amount: packageData.price * 100, // In kobo
            currency: 'KES',
            ref: result.reference,
            callback: function (response) {
                // Payment was successful
                showNotification('Payment successful! Verifying...', 'success');
                window.location.href = '../payments/paystack-success.php?reference=' + response.reference;
            },
            onClose: function () {
                showNotification('Payment window closed', 'info');
                hideLoading(payBtn);
            }
        });

        handler.openIframe();

    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
        hideLoading(document.getElementById('pay-button'));
    }
}

function startPaymentStatusCheck() {
    if (paymentCheckInterval) {
        clearInterval(paymentCheckInterval);
    }

    paymentCheckInterval = setInterval(checkPaymentStatus, 5000); // Check every 5 seconds
}

async function checkPaymentStatus() {
    if (!currentTransaction) return;

    try {
        const response = await fetch(`api/payment/status.php?transaction_id=${currentTransaction.transaction_id}`);
        const result = await response.json();

        if (result.success && result.payment_status === 'completed') {
            clearInterval(paymentCheckInterval);
            showNotification('Payment confirmed! Generating your streaming access...', 'success');

            setTimeout(() => {
                generateStreamingAccess();
            }, 2000);
        }
    } catch (error) {
        console.error('Payment status check failed:', error);
    }
}

async function generateStreamingAccess() {
    try {
        const response = await fetch('api/subscription/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                package_id: packageData.id,
                transaction_id: currentTransaction.transaction_id
            })
        });

        const result = await response.json();

        if (result.success) {
            // Populate streaming details
            document.getElementById('streaming-url').value = result.streaming_url;
            document.getElementById('streaming-username').value = result.username;
            document.getElementById('streaming-password').value = result.password;

            currentStep = 4;
            updateStepIndicator();
            showStep(4);

            showNotification('Welcome to BingeTV! Your subscription is now active.', 'success');
        } else {
            showNotification(result.message || 'Failed to create subscription', 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please contact support.', 'error');
    }
}

function cancelPayment() {
    if (paymentCheckInterval) {
        clearInterval(paymentCheckInterval);
    }

    currentTransaction = null;
    currentStep = 2;
    updateStepIndicator();
    showStep(2);

    showNotification('Payment cancelled', 'info');
}

// Device Tabs
function initDeviceTabs() {
    const deviceTabs = document.querySelectorAll('.device-tab');
    deviceTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const deviceName = this.dataset.device;

            // Update tab buttons
            deviceTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Update instructions
            document.querySelectorAll('.device-instruction').forEach(instruction => {
                instruction.classList.remove('active');
            });
            document.getElementById(deviceName).classList.add('active');
        });
    });
}

// Modal Functions
function initModals() {
    const modal = document.getElementById('allChannelsModal');
    const closeBtn = document.querySelector('.modal-close');

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });
}

function showAllChannels() {
    document.getElementById('allChannelsModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('allChannelsModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Streaming Access Functions
function copyStreamingUrl() {
    const urlInput = document.getElementById('streaming-url');
    urlInput.select();
    urlInput.setSelectionRange(0, 99999); // For mobile devices

    navigator.clipboard.writeText(urlInput.value).then(() => {
        showNotification('Streaming URL copied to clipboard!', 'success');
    }).catch(() => {
        // Fallback for older browsers
        document.execCommand('copy');
        showNotification('Streaming URL copied!', 'success');
    });
}

function copyUsername() {
    const usernameInput = document.getElementById('streaming-username');
    usernameInput.select();
    usernameInput.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(usernameInput.value).then(() => {
        showNotification('Username copied to clipboard!', 'success');
    }).catch(() => {
        document.execCommand('copy');
        showNotification('Username copied!', 'success');
    });
}

function copyPassword() {
    const passwordInput = document.getElementById('streaming-password');
    passwordInput.select();
    passwordInput.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(passwordInput.value).then(() => {
        showNotification('Password copied to clipboard!', 'success');
    }).catch(() => {
        document.execCommand('copy');
        showNotification('Password copied!', 'success');
    });
}

function togglePassword() {
    const passwordInput = document.getElementById('streaming-password');
    const toggleBtn = passwordInput.nextElementSibling;

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        passwordInput.type = 'password';
        toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

// Utility Functions
function showLoading(button) {
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }
}

function hideLoading(button) {
    if (button) {
        button.disabled = false;
        // Restore original button content based on context
        if (button.closest('#step-2')) {
            const method = document.querySelector('input[name="payment_method"]:checked').value;
            const icon = method === 'mpesa' ? 'mobile-alt' : 'credit-card';
            button.innerHTML = `<i class="fas fa-${icon}"></i> Pay KES ${packageData.price.toLocaleString()}`;
        } else if (button.closest('#register-form')) {
            button.innerHTML = '<i class="fas fa-user-plus"></i> Create Account & Continue';
        } else if (button.closest('#login-form')) {
            button.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login & Continue';
        }
    }
}

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
const styleSheet = document.createElement('style');
styleSheet.textContent = notificationStyles;
document.head.appendChild(styleSheet);
