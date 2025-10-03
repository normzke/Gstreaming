/**
 * BingeTV Animations
 * Handles page animations and interactive effects
 */

// Counter animation for hero stats
function animateCounters() {
    const counters = document.querySelectorAll('.counter, .stat-number');
    
    counters.forEach(counter => {
        const target = parseFloat(counter.getAttribute('data-target'));
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            // Handle decimal numbers
            if (target % 1 !== 0) {
                counter.textContent = current.toFixed(1);
            } else {
                counter.textContent = Math.floor(current);
            }
        }, 16);
    });
}

// Intersection Observer for scroll animations
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                
                // Trigger counter animation for stats section
                if (entry.target.classList.contains('stats-section')) {
                    animateCounters();
                }
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.package-card, .gallery-item, .stat-item, .feature-card').forEach(el => {
        observer.observe(el);
    });
}

// Smooth scroll for anchor links
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                const offsetTop = target.offsetTop - 80; // Account for fixed navbar
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Parallax effect for hero section
function initParallax() {
    const hero = document.querySelector('.hero');
    if (!hero) return;
    
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;
        
        hero.style.transform = `translateY(${rate}px)`;
    });
}

// Typing animation
function initTypingAnimation() {
    const elements = document.querySelectorAll('[data-typing]');
    
    elements.forEach(element => {
        const text = element.textContent;
        element.textContent = '';
        element.style.borderRight = '2px solid var(--primary-color)';
        
        let i = 0;
        const typeWriter = () => {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 100);
            } else {
                element.style.borderRight = 'none';
            }
        };
        
        // Start animation when element is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    typeWriter();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(element);
    });
}

// Package hover effects
function initPackageHover() {
    const packages = document.querySelectorAll('.package-card');
    
    packages.forEach(package => {
        package.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        package.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// Button ripple effect
function initRippleEffect() {
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

// Loading animation
function initLoadingAnimation() {
    const loadingElements = document.querySelectorAll('.loading');
    
    loadingElements.forEach(element => {
        const spinner = document.createElement('div');
        spinner.className = 'spinner';
        spinner.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        `;
        
        element.style.position = 'relative';
        element.appendChild(spinner);
    });
}

// Initialize all animations
function initAnimations() {
    initScrollAnimations();
    initSmoothScroll();
    initParallax();
    initTypingAnimation();
    initPackageHover();
    initRippleEffect();
    initLoadingAnimation();
}

// Add CSS for animations
const animationStyles = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .animate-in {
        animation: fadeInUp 0.6s ease-out;
    }
    
    .animate-in-left {
        animation: fadeInLeft 0.6s ease-out;
    }
    
    .animate-in-right {
        animation: fadeInRight 0.6s ease-out;
    }
    
    .animate-in-scale {
        animation: scaleIn 0.6s ease-out;
    }
    
    .package-card {
        transition: all 0.3s ease;
    }
    
    .gallery-item {
        transition: all 0.3s ease;
    }
    
    .stat-item {
        transition: all 0.3s ease;
    }
`;

// Inject animation styles
const styleSheet = document.createElement('style');
styleSheet.textContent = animationStyles;
document.head.appendChild(styleSheet);

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAnimations);
} else {
    initAnimations();
}

// Export functions for global use
window.BingeTVAnimations = {
    animateCounters,
    initScrollAnimations,
    initSmoothScroll,
    initParallax,
    initTypingAnimation,
    initPackageHover,
    initRippleEffect,
    initLoadingAnimation
};
