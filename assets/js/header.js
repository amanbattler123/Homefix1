// Enhanced Header JavaScript with Premium Functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Premium Header loaded successfully');
    
    // Initialize AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    }

    // Initialize GSAP and ScrollTrigger if available
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
    }

    // Navbar scroll effect
    const premiumNav = document.querySelector('.premium-nav');
    if (premiumNav) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                premiumNav.style.background = 'rgba(255, 255, 255, 0.98)';
                premiumNav.style.boxShadow = 'var(--shadow-lg)';
            } else {
                premiumNav.style.background = 'rgba(255, 255, 255, 0.95)';
                premiumNav.style.boxShadow = 'none';
            }
        });
    }

    // Mobile menu functionality
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        });
    }

    // Close mobile menu when clicking on a link
    const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
    mobileNavLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (mobileMenu) mobileMenu.classList.remove('active');
            if (mobileMenuBtn) mobileMenuBtn.classList.remove('active');
            document.body.style.overflow = '';
        });
    });

    // Smooth scrolling for anchor links
    const navLinks = document.querySelectorAll('.nav-link[href^="#"], .mobile-nav-link[href^="#"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                const targetId = href;
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    // Use GSAP if available, otherwise use native smooth scroll
                    if (typeof gsap !== 'undefined') {
                        gsap.to(window, {
                            duration: 1,
                            scrollTo: {
                                y: targetElement,
                                offsetY: 100
                            },
                            ease: "power2.inOut"
                        });
                    } else {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                    
                    // Close mobile menu if open
                    if (mobileMenu && mobileMenu.classList.contains('active')) {
                        mobileMenu.classList.remove('active');
                        if (mobileMenuBtn) mobileMenuBtn.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                }
            }
        });
    });

    // Parallax effect for background
    const parallaxBg = document.querySelector('.bg-image-parallax');
    if (parallaxBg) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            parallaxBg.style.transform = `translateY(${scrolled * 0.5}px)`;
        });
    }

    // User profile dropdown functionality
    const userProfileDropdowns = document.querySelectorAll('.user-profile-dropdown');
    userProfileDropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.user-profile-trigger');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (trigger && menu) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('active');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                menu.classList.remove('active');
            });
            
            // Prevent dropdown from closing when clicking inside
            menu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });

    // Add hover effects to navigation items
    const navItems = document.querySelectorAll('.nav-link, .mobile-nav-link');
    navItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Notification system
    window.showNotification = function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Add styles for notification
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow-xl);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            max-width: 400px;
            z-index: 1300;
            border-left: 4px solid ${type === 'success' ? 'var(--success)' : 'var(--accent)'};
            transform: translateX(150%);
            transition: transform 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Close button functionality
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', function() {
            notification.style.transform = 'translateX(150%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        });
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.transform = 'translateX(150%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }
        }, 5000);
    };

    // Add loading state to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (this.getAttribute('href') && !this.classList.contains('btn-text')) {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                this.style.pointerEvents = 'none';
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.style.pointerEvents = 'auto';
                }, 2000);
            }
        });
    });

    // Enhanced scroll to top functionality
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > 500) {
            // You can add a scroll to top button here if needed
        }
    });

    console.log('Premium Header initialization complete');
});