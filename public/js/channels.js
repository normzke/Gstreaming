// Channels Page JavaScript

document.addEventListener('DOMContentLoaded', function () {
    // Initialize channels page functionality
    initChannelPreview();
    initChannelFavorites();
    initSearchFilters();
    initChannelCards();
});

// Channel Preview Modal
function initChannelPreview() {
    const modal = document.getElementById('channelPreviewModal');
    const closeBtn = document.querySelector('.modal-close');
    const modalChannelName = document.getElementById('modalChannelName');

    // Close modal when clicking outside
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Close modal with close button
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Make functions globally available
    window.previewChannel = function (channelId) {
        // In a real implementation, you would fetch channel data via AJAX
        // For now, we'll show a generic preview
        modalChannelName.textContent = 'Channel Preview';
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';

        // Add some animation
        setTimeout(() => {
            modal.querySelector('.modal-content').style.transform = 'scale(1)';
        }, 10);
    };
}

// Channel Favorites
function initChannelFavorites() {
    // Load saved favorites from localStorage
    const savedFavorites = JSON.parse(localStorage.getItem('channelFavorites') || '[]');

    // Update favorite buttons based on saved state
    savedFavorites.forEach(channelId => {
        const button = document.querySelector(`button[onclick="addToFavorites(${channelId})"]`);
        if (button) {
            button.classList.add('favorited');
            button.innerHTML = '<i class="fas fa-heart"></i> Favorited';
        }
    });

    // Make function globally available
    window.addToFavorites = function (channelId) {
        const button = document.querySelector(`button[onclick="addToFavorites(${channelId})"]`);
        const savedFavorites = JSON.parse(localStorage.getItem('channelFavorites') || '[]');

        if (savedFavorites.includes(channelId)) {
            // Remove from favorites
            const index = savedFavorites.indexOf(channelId);
            savedFavorites.splice(index, 1);
            button.classList.remove('favorited');
            button.innerHTML = '<i class="fas fa-heart"></i> Favorite';
            showNotification('Channel removed from favorites', 'info');
        } else {
            // Add to favorites
            savedFavorites.push(channelId);
            button.classList.add('favorited');
            button.innerHTML = '<i class="fas fa-heart"></i> Favorited';
            showNotification('Channel added to favorites', 'success');
        }

        localStorage.setItem('channelFavorites', JSON.stringify(savedFavorites));
    };
}

// Search and Filter Functionality
function initSearchFilters() {
    const searchInput = document.getElementById('search');
    const filterForm = document.querySelector('.filter-form');

    // Auto-submit form on filter changes (with debounce for search)
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (searchInput.value.length >= 3 || searchInput.value.length === 0) {
                    filterForm.submit();
                }
            }, 500);
        });
    }

    // Auto-submit form on select changes
    const selectElements = filterForm.querySelectorAll('select');
    selectElements.forEach(select => {
        select.addEventListener('change', function () {
            filterForm.submit();
        });
    });

    // Add loading state to form submission
    filterForm.addEventListener('submit', function () {
        const submitBtn = filterForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
            submitBtn.disabled = true;
        }
    });
}

// Channel Cards Animation and Interactions
function initChannelCards() {
    const channelCards = document.querySelectorAll('.channel-card');

    // Add hover effects and animations
    channelCards.forEach(card => {
        // Add click animation
        card.addEventListener('click', function (e) {
            // Don't trigger if clicking on buttons
            if (e.target.closest('.channel-actions')) return;

            card.style.transform = 'scale(0.98)';
            setTimeout(() => {
                card.style.transform = '';
            }, 150);
        });

        // Add intersection observer for animation
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        // Set initial state for animation
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';

        observer.observe(card);
    });

    // Add staggered animation delay
    channelCards.forEach((card, index) => {
        card.style.transitionDelay = `${index * 0.1}s`;
    });
}

// Notification System
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

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
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

// Utility Functions
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

// Channel Search with Highlighting
function highlightSearchTerm(text, searchTerm) {
    if (!searchTerm) return text;

    const regex = new RegExp(`(${searchTerm})`, 'gi');
    return text.replace(regex, '<mark>$1</mark>');
}

// Add CSS for notification styles
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
    
    .favorited {
        background: var(--error-color) !important;
        color: white !important;
    }
    
    .favorited:hover {
        background: var(--error-color) !important;
        opacity: 0.9;
    }
    
    mark {
        background: rgba(255, 235, 59, 0.3);
        padding: 0.1em 0.2em;
        border-radius: 0.2em;
    }
`;

// Inject notification styles
const bingetvChannelStyleSheet = document.createElement('style');
bingetvChannelStyleSheet.textContent = notificationStyles;
document.head.appendChild(bingetvChannelStyleSheet);
