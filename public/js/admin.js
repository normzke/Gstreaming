/**
 * BingeTV Admin JavaScript
 * Handles admin panel interactions and UI enhancements
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 1024 && !event.target.closest('.admin-sidebar') && !event.target.closest('.mobile-menu-toggle')) {
            sidebar.classList.remove('active');
        }
    });
    
    // Modal handling
    document.querySelectorAll('[data-modal]').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Focus first input in modal if exists
                const input = modal.querySelector('input, select, textarea');
                if (input) input.focus();
            }
        });
    });
    
    // Close modal when clicking close button or overlay
    document.querySelectorAll('.modal, .modal-close').forEach(element => {
        element.addEventListener('click', function(e) {
            if (e.target === this || e.target.classList.contains('modal-close') || e.target.closest('.modal-close')) {
                this.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Prevent modal from closing when clicking inside modal content
    document.querySelectorAll('.modal-content').forEach(modalContent => {
        modalContent.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // Handle form submissions with confirmation
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
                return false;
            }
            return true;
        });
    });
    
    // Initialize tooltips
    if (typeof tippy === 'function') {
        tippy('[data-tippy-content]', {
            allowHTML: true,
            animation: 'shift-away',
            delay: [100, 0],
            duration: [200, 150],
            interactive: true,
            placement: 'top',
            theme: 'light',
            touch: ['hold', 500],
        });
    }
    
    // Handle active navigation items
    const currentPath = window.location.pathname.split('/').pop() || 'index';
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href').replace('.php', '');
        if (href === currentPath.replace('.php', '')) {
            link.classList.add('active');
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        });
    }, 5000);
    
    // Handle file upload previews
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const preview = document.querySelector(this.dataset.preview);
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        preview.style.backgroundImage = `url(${e.target.result})`;
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // Initialize select2 if available
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('select.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
    
    // Initialize datepickers if available
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', {
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            time_24hr: true
        });
    }
});

// Helper function to show a toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }, 100);
}

// Helper function to confirm action
function confirmAction(message) {
    return new Promise((resolve) => {
        const confirmed = confirm(message);
        resolve(confirmed);
    });
}

// Helper function to toggle password visibility
togglePasswordVisibility = (inputId, toggleBtnId) => {
    const passwordInput = document.getElementById(inputId);
    const toggleBtn = document.getElementById(toggleBtnId);
    
    if (passwordInput && toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
};

// Initialize any password toggles on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-toggle-password]').forEach(toggle => {
        const inputId = toggle.getAttribute('data-toggle-password');
        togglePasswordVisibility(inputId, toggle.id);
    });
});
