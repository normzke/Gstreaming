// Enhanced JavaScript for BingeTV
// Counter animations, pricing tabs, testimonials, FAQ accordion

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all enhanced features
    initCounters();
    initPricingTabs();
    initTestimonials();
    initFAQ();
    initAnimations();
    // Ensure initial pricing reflects default device selection
    try { updatePackageCards(1); } catch (e) {}
});

// Animated Counters
function initCounters() {
    const counters = document.querySelectorAll('.counter');
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
    
    // Intersection Observer for counters
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => {
        counterObserver.observe(counter);
    });
}

// Pricing Tabs Functionality
function initPricingTabs() {
    const deviceTabs = document.querySelectorAll('.device-tab');
    const durationTabs = document.querySelectorAll('.duration-tab');
    const packageCards = document.querySelectorAll('.package-card');
    let selectedDevices = 1;
    let selectedDuration = '1';
    
    // Device tab functionality
    deviceTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const devicesValue = tab.getAttribute('data-devices');
            
            // Handle "Custom" option
            if (devicesValue === 'custom') {
                window.location.href = 'support.php?inquiry=custom_package';
                return;
            }
            
            // Remove active class from all device tabs
            deviceTabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            tab.classList.add('active');
            
            selectedDevices = parseInt(devicesValue, 10);
            
            // Update package cards pricing (duration comes from DB per card)
            updatePackageCards(selectedDevices);
        });
    });
    
    // Duration tab functionality
    durationTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const durationTabGroup = tab.closest('.duration-tabs');
            if (!durationTabGroup) return;
            
            // Remove active class from all duration tabs in this group
            durationTabGroup.querySelectorAll('.duration-tab').forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            tab.classList.add('active');
            
            selectedDevices = parseInt(durationTabGroup.getAttribute('data-devices'), 10);
            selectedDuration = tab.getAttribute('data-duration');
            
            updatePackageCards(selectedDevices);
        });
    });

    // Hook subscribe buttons: if pointing directly to subscribe.php, allow default navigation
    document.querySelectorAll('.subscribe-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const href = btn.getAttribute('href') || '';
            const devicesParam = `devices=${encodeURIComponent(selectedDevices)}`;
            if (href.includes('user/subscriptions/subscribe.php')) {
                const joiner = href.includes('?') ? '&' : '?';
                btn.setAttribute('href', `${href}${joiner}${devicesParam}`);
                return;
            }
            // Legacy fallback
            const pkgId = btn.getAttribute('data-package-id');
            e.preventDefault();
            const url = `packages.php?from_homepage=1&package_id=${encodeURIComponent(pkgId)}&${devicesParam}`;
            window.location.href = url;
        });
    });
}

// Update package cards based on selection
function updatePackageCards(devices) {
    // Fixed pricing table - exact prices based on devices and duration
    const pricingTable = {
        1: { 1: 2500, 6: 14000, 12: 28000 },      // 1 Device
        2: { 1: 4500, 6: 27000, 12: 54000 },      // 2 Devices  
        3: { 1: 6500, 6: 39000, 12: 78000 }       // 3 Devices
    };
    
    // Get price from fixed table
    function getPrice(devices, months) {
        // Normalize months to available tiers (1, 6, or 12)
        let tier = 1;
        if (months >= 12) {
            tier = 12;
        } else if (months >= 6) {
            tier = 6;
        }
        
        // Ensure devices is between 1 and 3
        devices = Math.max(1, Math.min(3, devices));
        
        return pricingTable[devices] && pricingTable[devices][tier] 
            ? pricingTable[devices][tier] 
            : 0;
    }
    
    const packageCards = document.querySelectorAll('.package-card');
    
    packageCards.forEach(card => {
        // Update device count in features
        const deviceCountSpan = card.querySelector('.devices-count');
        if (deviceCountSpan) {
            deviceCountSpan.textContent = `${devices} Device${devices > 1 ? 's' : ''}`;
        }
        
        // Update pricing using fixed pricing table
        const amountElement = card.querySelector('.amount');
        if (amountElement) {
            const months = parseInt(card.getAttribute('data-duration') || '1', 10) || 1;
            const finalPrice = getPrice(devices, months);
            amountElement.textContent = finalPrice.toLocaleString('en-KE');
        }
    });
}

// Testimonials Carousel
function initTestimonials() {
    const slides = document.querySelectorAll('.testimonial-slide');
    const dots = document.querySelectorAll('.dot');
    
    if (slides.length === 0) return; // Exit if no testimonials found
    
    let currentTestimonial = 1;
    const totalTestimonials = slides.length;
    
    // Initialize first testimonial
    showTestimonial(currentTestimonial);
    
    // Auto-advance testimonials every 5 seconds
    setInterval(() => {
        currentTestimonial = currentTestimonial >= totalTestimonials ? 1 : currentTestimonial + 1;
        showTestimonial(currentTestimonial);
    }, 5000);
    
    // Add click handlers for navigation buttons
    const prevBtn = document.querySelector('.nav-btn.prev');
    const nextBtn = document.querySelector('.nav-btn.next');
    
    if (prevBtn) prevBtn.addEventListener('click', () => changeSlide(-1));
    if (nextBtn) nextBtn.addEventListener('click', () => changeSlide(1));
    
    // Add click handlers for dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => currentSlide(index + 1));
    });
    
    // Make functions available globally
    window.changeSlide = changeSlide;
    window.currentSlide = currentSlide;
    window.showTestimonial = showTestimonial;
}

function changeSlide(direction) {
    const slides = document.querySelectorAll('.testimonial-slide');
    const totalTestimonials = slides.length;
    let currentTestimonial = parseInt(document.querySelector('.testimonial-slide.active')?.dataset?.slide || 1);
    
    currentTestimonial += direction;
    if (currentTestimonial > totalTestimonials) currentTestimonial = 1;
    if (currentTestimonial < 1) currentTestimonial = totalTestimonials;
    showTestimonial(currentTestimonial);
}

function currentSlide(n) {
    showTestimonial(n);
}

function showTestimonial(n) {
    const slides = document.querySelectorAll('.testimonial-slide');
    const dots = document.querySelectorAll('.dot');
    
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    if (slides[n - 1]) slides[n - 1].classList.add('active');
    if (dots[n - 1]) dots[n - 1].classList.add('active');
}

// FAQ Accordion
function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            const answer = item.querySelector('.faq-answer');
            const isActive = question.classList.contains('active');
            
            // Close all other FAQ items
            faqItems.forEach(otherItem => {
                otherItem.querySelector('.faq-question').classList.remove('active');
                otherItem.querySelector('.faq-answer').classList.remove('active');
            });
            
            // Toggle current item
            if (!isActive) {
                question.classList.add('active');
                answer.classList.add('active');
            }
        });
    });
}

// AOS (Animate On Scroll) Alternative
function initAnimations() {
    const animatedElements = document.querySelectorAll('[data-aos]');
    
    const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const animationType = element.getAttribute('data-aos');
                const delay = element.getAttribute('data-aos-delay') || 0;
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                    element.style.transition = 'all 0.6s ease-out';
                }, delay);
                
                animationObserver.unobserve(element);
            }
        });
    }, { threshold: 0.1 });
    
    animatedElements.forEach(element => {
        // Set initial state
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        animationObserver.observe(element);
    });
}

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href') || '';
        if (href === '#' || href.trim().length <= 1) return; // ignore invalid target
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Mobile menu toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');

if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
}

// Close mobile menu when clicking on a link
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    });
});

// Navbar scroll effect
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 100) {
        navbar.style.background = 'rgba(255, 255, 255, 0.98)';
        navbar.style.backdropFilter = 'blur(15px)';
    } else {
        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        navbar.style.backdropFilter = 'blur(10px)';
    }
});
