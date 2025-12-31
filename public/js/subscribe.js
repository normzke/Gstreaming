(function () {
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
        if (typeof userData !== 'undefined' && userData) {
            currentStep = 2;
            updateStepIndicator();
            showStep(2);
        }
    }

    // Step Navigation Functions
    function proceedToRegister() {
        showStep(2);
        const registerForm = document.getElementById('register-form');
        const loginForm = document.getElementById('login-form');
        const registerTab = document.querySelector('[data-tab="register"]');
        const loginTab = document.querySelector('[data-tab="login"]');

        if (registerForm) registerForm.classList.add('active');
        if (loginForm) loginForm.classList.remove('active');
        if (registerTab) registerTab.classList.add('active');
        if (loginTab) loginTab.classList.remove('active');
    }

    function proceedToLogin() {
        showStep(2);
        const registerForm = document.getElementById('register-form');
        const loginForm = document.getElementById('login-form');
        const registerTab = document.querySelector('[data-tab="register"]');
        const loginTab = document.querySelector('[data-tab="login"]');

        if (registerForm) registerForm.classList.remove('active');
        if (loginForm) loginForm.classList.add('active');
        if (loginTab) loginTab.classList.add('active');
        if (registerTab) registerTab.classList.remove('active');
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
                const targetForm = document.getElementById(`${tabName}-form`);
                if (targetForm) targetForm.classList.add('active');
            });
        });

        // Handle form submissions
        const registrationForm = document.getElementById('registrationForm');
        const loginForm = document.getElementById('loginForm');
        if (registrationForm) registrationForm.addEventListener('submit', handleRegistration);
        if (loginForm) loginForm.addEventListener('submit', handleLogin);
    }

    // Registration Handler
    async function handleRegistration(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);

        // Validate form
        if (data.password !== data.confirm_password) {
            if (typeof showNotification === 'function') showNotification('Passwords do not match', 'error');
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
                if (typeof showNotification === 'function') showNotification('Registration successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                if (typeof showNotification === 'function') showNotification(result.message || 'Registration failed', 'error');
            }
        } catch (error) {
            if (typeof showNotification === 'function') showNotification('An error occurred. Please try again.', 'error');
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
                if (typeof showNotification === 'function') showNotification('Login successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                if (typeof showNotification === 'function') showNotification(result.message || 'Login failed', 'error');
            }
        } catch (error) {
            if (typeof showNotification === 'function') showNotification('An error occurred. Please try again.', 'error');
        } finally {
            hideLoading(e.target.querySelector('button[type="submit"]'));
        }
    }

    // Payment Functions
    async function initiatePayment() {
        const phoneNumber = document.getElementById('phone_number').value;

        if (!phoneNumber || phoneNumber.length < 12) {
            if (typeof showNotification === 'function') showNotification('Please enter a valid phone number', 'error');
            return;
        }

        try {
            showLoading(document.querySelector('#step-2 .btn-primary'));

            const response = await fetch('api/payment/initiate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    package_id: typeof packageData !== 'undefined' ? packageData.id : null,
                    phone_number: phoneNumber,
                    amount: typeof packageData !== 'undefined' ? packageData.price : 0
                })
            });

            const result = await response.json();

            if (result.success) {
                currentTransaction = result.transaction;
                const accNum = document.getElementById('account-number');
                const transId = document.getElementById('transaction-id');
                const payPhone = document.getElementById('payment-phone');

                if (accNum) accNum.textContent = result.account_number;
                if (transId) transId.textContent = result.transaction_id;
                if (payPhone) payPhone.textContent = phoneNumber;

                currentStep = 3;
                updateStepIndicator();
                showStep(3);

                // Start checking payment status
                startPaymentStatusCheck();

                if (typeof showNotification === 'function') showNotification('Payment initiated! Please complete on your phone.', 'success');
            } else {
                if (typeof showNotification === 'function') showNotification(result.message || 'Payment initiation failed', 'error');
            }
        } catch (error) {
            if (typeof showNotification === 'function') showNotification('An error occurred. Please try again.', 'error');
        } finally {
            hideLoading(document.querySelector('#step-2 .btn-primary'));
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
                if (typeof showNotification === 'function') showNotification('Payment confirmed! Generating your streaming access...', 'success');

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
                    package_id: typeof packageData !== 'undefined' ? packageData.id : null,
                    transaction_id: currentTransaction.transaction_id
                })
            });

            const result = await response.json();

            if (result.success) {
                // Populate streaming details
                const sUrl = document.getElementById('streaming-url');
                const sUser = document.getElementById('streaming-username');
                const sPass = document.getElementById('streaming-password');

                if (sUrl) sUrl.value = result.streaming_url;
                if (sUser) sUser.value = result.username;
                if (sPass) sPass.value = result.password;

                currentStep = 4;
                updateStepIndicator();
                showStep(4);

                if (typeof showNotification === 'function') showNotification('Welcome to BingeTV! Your subscription is now active.', 'success');
            } else {
                if (typeof showNotification === 'function') showNotification(result.message || 'Failed to create subscription', 'error');
            }
        } catch (error) {
            if (typeof showNotification === 'function') showNotification('An error occurred. Please contact support.', 'error');
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

        if (typeof showNotification === 'function') showNotification('Payment cancelled', 'info');
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
                const targetInstruction = document.getElementById(deviceName);
                if (targetInstruction) targetInstruction.classList.add('active');
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
        const modal = document.getElementById('allChannelsModal');
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal() {
        const modal = document.getElementById('allChannelsModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // Streaming Access Functions
    function copyStreamingUrl() {
        const urlInput = document.getElementById('streaming-url');
        if (!urlInput) return;
        urlInput.select();
        urlInput.setSelectionRange(0, 99999); // For mobile devices

        navigator.clipboard.writeText(urlInput.value).then(() => {
            if (typeof showNotification === 'function') showNotification('Streaming URL copied to clipboard!', 'success');
        }).catch(() => {
            // Fallback for older browsers
            document.execCommand('copy');
            if (typeof showNotification === 'function') showNotification('Streaming URL copied!', 'success');
        });
    }

    function copyUsername() {
        const usernameInput = document.getElementById('streaming-username');
        if (!usernameInput) return;
        usernameInput.select();
        usernameInput.setSelectionRange(0, 99999);

        navigator.clipboard.writeText(usernameInput.value).then(() => {
            if (typeof showNotification === 'function') showNotification('Username copied to clipboard!', 'success');
        }).catch(() => {
            document.execCommand('copy');
            if (typeof showNotification === 'function') showNotification('Username copied!', 'success');
        });
    }

    function copyPassword() {
        const passwordInput = document.getElementById('streaming-password');
        if (!passwordInput) return;
        passwordInput.select();
        passwordInput.setSelectionRange(0, 99999);

        navigator.clipboard.writeText(passwordInput.value).then(() => {
            if (typeof showNotification === 'function') showNotification('Password copied to clipboard!', 'success');
        }).catch(() => {
            document.execCommand('copy');
            if (typeof showNotification === 'function') showNotification('Password copied!', 'success');
        });
    }

    function togglePassword() {
        const passwordInput = document.getElementById('streaming-password');
        if (!passwordInput) return;
        const toggleBtn = passwordInput.nextElementSibling;

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            if (toggleBtn) toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            passwordInput.type = 'password';
            if (toggleBtn) toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
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
                button.innerHTML = '<i class="fas fa-mobile-alt"></i> Pay with M-PESA';
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

})();
