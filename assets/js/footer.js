document.addEventListener('DOMContentLoaded', function() {
    console.log('Footer JavaScript loaded'); // Debug log
    
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
    
    // Animate footer sections on scroll
    const footerSections = document.querySelectorAll('.footer-section');
    console.log('Found footer sections:', footerSections.length); // Debug log
    
    if (footerSections.length > 0) {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver(function(entries, observer) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        footerSections.forEach(section => {
            observer.observe(section);
        });
    }
    
    // Newsletter form submission
    const newsletterForm = document.querySelector('.newsletter-form');
    console.log('Newsletter form found:', newsletterForm !== null); // Debug log
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Newsletter form submitted'); // Debug log
            
            const emailInput = this.querySelector('.newsletter-input');
            const email = emailInput.value.trim();
            
            // Simple validation
            if (email && isValidEmail(email)) {
                // In a real application, you would send this to your server
                console.log('Newsletter subscription:', email);
                
                // Show success message
                showNotification('Successfully subscribed to newsletter!', 'success');
                
                // Reset form
                emailInput.value = '';
            } else {
                showNotification('Please enter a valid email address', 'error');
                emailInput.focus();
            }
        });
        
        // Add real-time validation
        const emailInput = newsletterForm.querySelector('.newsletter-input');
        emailInput.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.style.borderColor = isValidEmail(this.value.trim()) ? '#4CAF50' : '#f44336';
            } else {
                this.style.borderColor = '';
            }
        });
    }
    
    // Helper function to validate email
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Show notification function
    function showNotification(message, type) {
        // Remove any existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        });
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(-50%) translateY(20px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
    
    // Add hover effects to social links
    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) rotate(5deg)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) rotate(0)';
        });
    });
});