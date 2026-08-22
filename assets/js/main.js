// Main JavaScript for all pages
document.addEventListener('DOMContentLoaded', function() {
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

    // Back to top button functionality
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });
        
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.main-header');
        if (header) {
            if (window.scrollY > 100) {
                header.style.background = 'rgba(99, 102, 241, 0.95)';
                header.style.backdropFilter = 'blur(20px)';
            } else {
                header.style.background = 'linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%)';
                header.style.backdropFilter = 'blur(10px)';
            }
        }
    });

    // Add hover effects to interactive elements
    const interactiveElements = document.querySelectorAll('.nav-link, .btn, .social-link, .footer-link');
    interactiveElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    console.log('HomeFix Pro - Main JavaScript loaded successfully');
});