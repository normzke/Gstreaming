/**
 * BingeTV - Main JavaScript
 */

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initNavigation();
    initAnimations();
    initForms();
    initModals();
    initTooltips();
});

/**
 * Navigation functionality
 */
function initNavigation() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
        
        // Close menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Navbar background on scroll
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }
}

/**
 * Animation functionality
 */
function initAnimations() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.package-card, .device-card, .support-card, .gallery-item').forEach(el => {
        observer.observe(el);
    });
    
    // Counter animation
    const counters = document.querySelectorAll('.stat-number');
    counters.forEach(counter => {
        const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
        if (target > 0) {
            animateCounter(counter, 0, target, 2000);
        }
    });
}

/**
 * Animate counter numbers
 */
function animateCounter(element, start, end, duration) {
    const startTime = performance.now();
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const current = Math.floor(start + (end - start) * progress);
        element.textContent = current.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }
    
    requestAnimationFrame(updateCounter);
}

/**
 * Form functionality
 */
function initForms() {
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Real-time validation
    const inputs = document.querySelectorAll('input[data-validate]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            clearFieldError(this);
        });
    });
    
    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            formatPhoneNumber(this);
        });
    });
    
    // Password strength indicator
    const passwordInputs = document.querySelectorAll('input[type="password"][data-strength]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            checkPasswordStrength(this);
        });
    });
}

/**
 * Validate form
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required validation
    if (required && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Type-specific validation
    if (value) {
        switch (type) {
            case 'email':
                if (!isValidEmail(value)) {
                    showFieldError(field, 'Please enter a valid email address');
                    return false;
                }
                break;
                
            case 'tel':
                if (!isValidPhone(value)) {
                    showFieldError(field, 'Please enter a valid phone number');
                    return false;
                }
                break;
                
            case 'password':
                if (value.length < 8) {
                    showFieldError(field, 'Password must be at least 8 characters');
                    return false;
                }
                break;
        }
    }
    
    // Custom validation
    const pattern = field.getAttribute('pattern');
    if (pattern && value) {
        const regex = new RegExp(pattern);
        if (!regex.test(value)) {
            showFieldError(field, field.getAttribute('data-error') || 'Invalid format');
            return false;
        }
    }
    
    return true;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('error');
    
    let errorElement = field.parentNode.querySelector('.field-error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        field.parentNode.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    field.classList.remove('error');
    const errorElement = field.parentNode.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

/**
 * Format phone number
 */
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    
    // Auto-format Kenyan phone numbers
    if (value.startsWith('254')) {
        input.value = value;
    } else if (value.startsWith('0')) {
        if (value.length <= 10) {
            input.value = value;
        } else {
            input.value = '254' + value.substring(1);
        }
    } else if (value.startsWith('7')) {
        input.value = '254' + value;
    } else {
        input.value = value;
    }
}

/**
 * Check password strength
 */
function checkPasswordStrength(input) {
    const value = input.value;
    const strengthIndicator = input.parentNode.querySelector('.password-strength');
    
    if (!strengthIndicator) return;
    
    let strength = 0;
    let feedback = [];
    
    if (value.length >= 8) strength++;
    else feedback.push('At least 8 characters');
    
    if (/[a-z]/.test(value)) strength++;
    else feedback.push('Lowercase letter');
    
    if (/[A-Z]/.test(value)) strength++;
    else feedback.push('Uppercase letter');
    
    if (/[0-9]/.test(value)) strength++;
    else feedback.push('Number');
    
    if (/[^A-Za-z0-9]/.test(value)) strength++;
    else feedback.push('Special character');
    
    strengthIndicator.className = `password-strength strength-${strength}`;
    
    if (strength < 3) {
        strengthIndicator.textContent = 'Weak';
    } else if (strength < 5) {
        strengthIndicator.textContent = 'Medium';
    } else {
        strengthIndicator.textContent = 'Strong';
    }
}

/**
 * Modal functionality
 */
function initModals() {
    // Open modal
    document.querySelectorAll('[data-modal]').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                openModal(modal);
            }
        });
    });
    
    // Close modal
    document.querySelectorAll('.modal-close, .modal-backdrop').forEach(close => {
        close.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal);
            }
        });
    });
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.active');
            if (openModal) {
                closeModal(openModal);
            }
        }
    });
}

/**
 * Open modal
 */
function openModal(modal) {
    modal.classList.add('active');
    document.body.classList.add('modal-open');
}

/**
 * Close modal
 */
function closeModal(modal) {
    modal.classList.remove('active');
    document.body.classList.remove('modal-open');
}

/**
 * Tooltip functionality
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            showTooltip(this);
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip(this);
        });
    });
}

/**
 * Show tooltip
 */
function showTooltip(element) {
    const text = element.getAttribute('data-tooltip');
    const position = element.getAttribute('data-tooltip-position') || 'top';
    
    const tooltip = document.createElement('div');
    tooltip.className = `tooltip tooltip-${position}`;
    tooltip.textContent = text;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    const tooltipRect = tooltip.getBoundingClientRect();
    
    let top, left;
    
    switch (position) {
        case 'top':
            top = rect.top - tooltipRect.height - 10;
            left = rect.left + (rect.width - tooltipRect.width) / 2;
            break;
        case 'bottom':
            top = rect.bottom + 10;
            left = rect.left + (rect.width - tooltipRect.width) / 2;
            break;
        case 'left':
            top = rect.top + (rect.height - tooltipRect.height) / 2;
            left = rect.left - tooltipRect.width - 10;
            break;
        case 'right':
            top = rect.top + (rect.height - tooltipRect.height) / 2;
            left = rect.right + 10;
            break;
    }
    
    tooltip.style.top = `${top + window.scrollY}px`;
    tooltip.style.left = `${left}px`;
    
    element._tooltip = tooltip;
    
    setTimeout(() => {
        tooltip.classList.add('active');
    }, 10);
}

/**
 * Hide tooltip
 */
function hideTooltip(element) {
    const tooltip = element._tooltip;
    if (tooltip) {
        tooltip.classList.remove('active');
        setTimeout(() => {
            if (tooltip.parentNode) {
                tooltip.parentNode.removeChild(tooltip);
            }
        }, 200);
        delete element._tooltip;
    }
}

/**
 * Utility functions
 */

// Email validation
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Phone validation (Kenyan format)
function isValidPhone(phone) {
    const cleanPhone = phone.replace(/\D/g, '');
    return /^(254|0)(7|1)[0-9]{8}$/.test(cleanPhone);
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Format currency
function formatCurrency(amount, currency = 'KES') {
    return `${currency} ${parseFloat(amount).toLocaleString('en-KE')}`;
}

// Format date
function formatDate(date, format = 'YYYY-MM-DD') {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    
    return format
        .replace('YYYY', year)
        .replace('MM', month)
        .replace('DD', day)
        .replace('HH', hours)
        .replace('mm', minutes);
}

// Show notification
function showNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove
    setTimeout(() => {
        notification.classList.add('remove');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
    
    // Manual close
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.classList.add('remove');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    });
}

// Loading state
function setLoading(element, loading = true) {
    if (loading) {
        element.classList.add('loading');
        element.disabled = true;
    } else {
        element.classList.remove('loading');
        element.disabled = false;
    }
}

// AJAX helper
function ajaxRequest(url, options = {}) {
    const defaults = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    const config = { ...defaults, ...options };
    
    if (config.data && config.method !== 'GET') {
        config.body = JSON.stringify(config.data);
    }
    
    return fetch(url, config)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('AJAX Error:', error);
            throw error;
        });
}

// Export functions for global use
window.BingeTV = {
    showNotification,
    formatCurrency,
    formatDate,
    setLoading,
    ajaxRequest,
    debounce,
    throttle
};
