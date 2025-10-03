// Enhanced JavaScript for BingeTV
// Counter animations, pricing tabs, testimonials, FAQ accordion

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all enhanced features
    initCounters();
    initPricingTabs();
    initTestimonials();
    initFAQ();
    initAnimations();
});

// Animated Counters
function initCounters() {
    const counters = document.querySelectorAll('.counter, .stat-number');
    const speed = 200; // Lower is faster
    
    const animateCounter = (counter) => {
        const target = parseFloat(counter.getAttribute('data-target'));
        const count = parseFloat(counter.innerText);
        const increment = target / speed;
        
        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(() => animateCounter(counter), 1);
        } else {
            counter.innerText = target;
        }
    };
    
    // Use Intersection Observer to trigger animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    });
    
    counters.forEach(counter => observer.observe(counter));
}

// Pricing Tabs
function initPricingTabs() {
    const tabButtons = document.querySelectorAll('.pricing-tab');
    const tabContents = document.querySelectorAll('.pricing-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
}

// Testimonials Carousel
function initTestimonials() {
    const testimonialContainer = document.querySelector('.testimonials-container');
    if (!testimonialContainer) return;
    
    const testimonials = document.querySelectorAll('.testimonial-item');
    const prevBtn = document.querySelector('.testimonial-prev');
    const nextBtn = document.querySelector('.testimonial-next');
    const indicators = document.querySelectorAll('.testimonial-indicator');
    
    let currentIndex = 0;
    const totalTestimonials = testimonials.length;
    
    function showTestimonial(index) {
        testimonials.forEach((testimonial, i) => {
            testimonial.classList.toggle('active', i === index);
        });
        
        indicators.forEach((indicator, i) => {
            indicator.classList.toggle('active', i === index);
        });
    }
    
    function nextTestimonial() {
        currentIndex = (currentIndex + 1) % totalTestimonials;
        showTestimonial(currentIndex);
    }
    
    function prevTestimonial() {
        currentIndex = (currentIndex - 1 + totalTestimonials) % totalTestimonials;
        showTestimonial(currentIndex);
    }
    
    // Event listeners
    if (nextBtn) nextBtn.addEventListener('click', nextTestimonial);
    if (prevBtn) prevBtn.addEventListener('click', prevTestimonial);
    
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            currentIndex = index;
            showTestimonial(currentIndex);
        });
    });
    
    // Auto-rotate testimonials
    setInterval(nextTestimonial, 5000);
    
    // Initialize first testimonial
    showTestimonial(0);
}

// FAQ Accordion
function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        const icon = item.querySelector('.faq-icon');
        
        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // Close all other FAQ items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                    const otherAnswer = otherItem.querySelector('.faq-answer');
                    const otherIcon = otherItem.querySelector('.faq-icon');
                    if (otherAnswer) otherAnswer.style.maxHeight = null;
                    if (otherIcon) otherIcon.style.transform = 'rotate(0deg)';
                }
            });
            
            // Toggle current item
            if (isActive) {
                item.classList.remove('active');
                answer.style.maxHeight = null;
                icon.style.transform = 'rotate(0deg)';
            } else {
                item.classList.add('active');
                answer.style.maxHeight = answer.scrollHeight + 'px';
                icon.style.transform = 'rotate(45deg)';
            }
        });
    });
}

// General Animations
function initAnimations() {
    // Fade in elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.package-card, .gallery-item, .feature-card, .testimonial-item').forEach(el => {
        observer.observe(el);
    });
}

// Smooth scrolling for anchor links
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                const offsetTop = target.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
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

// Video modal functionality
function initVideoModals() {
    const videoThumbnails = document.querySelectorAll('.video-thumbnail');
    const videoModals = document.querySelectorAll('.video-modal');
    const closeButtons = document.querySelectorAll('.video-close');
    
    videoThumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            const modal = this.closest('.gallery-item').querySelector('.video-modal');
            if (modal) {
                modal.classList.add('active');
                document.body.classList.add('modal-open');
            }
        });
    });
    
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.video-modal');
            modal.classList.remove('active');
            document.body.classList.remove('modal-open');
            
            // Stop video
            const iframe = modal.querySelector('iframe');
            if (iframe) {
                iframe.src = iframe.src;
            }
        });
    });
    
    // Close modal on backdrop click
    videoModals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.classList.remove('modal-open');
                
                // Stop video
                const iframe = this.querySelector('iframe');
                if (iframe) {
                    iframe.src = iframe.src;
                }
            }
        });
    });
}

// Initialize all features
document.addEventListener('DOMContentLoaded', function() {
    initSmoothScroll();
    initPackageHover();
    initVideoModals();
});

// Add CSS for animations
const enhancedStyles = `
    .testimonial-item {
        display: none;
        opacity: 0;
        transition: opacity 0.5s ease;
    }
    
    .testimonial-item.active {
        display: block;
        opacity: 1;
    }
    
    .testimonial-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--gray-300);
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .testimonial-indicator.active {
        background: var(--primary-color);
    }
    
    .faq-item {
        border-bottom: 1px solid var(--gray-200);
    }
    
    .faq-question {
        padding: 1rem 0;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
    }
    
    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }
    
    .faq-icon {
        transition: transform 0.3s ease;
    }
    
    .pricing-tab {
        padding: 0.5rem 1rem;
        border: 2px solid var(--gray-200);
        background: transparent;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .pricing-tab.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .pricing-content {
        display: none;
    }
    
    .pricing-content.active {
        display: block;
    }
    
    .animate-in {
        animation: fadeInUp 0.6s ease-out;
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
`;

// Inject enhanced styles
const styleSheet = document.createElement('style');
styleSheet.textContent = enhancedStyles;
document.head.appendChild(styleSheet);
