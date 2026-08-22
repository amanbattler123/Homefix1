document.addEventListener('DOMContentLoaded', function() {
    console.log('Contact page JavaScript loaded');
    
    // Form elements
    const contactForm = document.querySelector('.contact-form form');
    const formControls = document.querySelectorAll('.form-control');
    const submitBtn = contactForm?.querySelector('button[type="submit"]');
    
    if (contactForm) {
        // Real-time form validation
        formControls.forEach(control => {
            control.addEventListener('blur', validateField);
            control.addEventListener('input', clearFieldValidation);
        });
        
        // Form submission
        contactForm.addEventListener('submit', handleFormSubmit);
        
        // Auto-fill subject if service parameter exists
        autoFillSubject();
    }
    
    // Add animations on scroll
    animateOnScroll();
    
    function validateField(e) {
        const field = e.target;
        const value = field.value.trim();
        const fieldName = field.name;
        
        removeFieldValidation(field);
        
        let isValid = true;
        let message = '';
        
        switch (fieldName) {
            case 'name':
                if (value.length < 2) {
                    isValid = false;
                    message = 'Name must be at least 2 characters long';
                }
                break;
                
            case 'email':
                if (!isValidEmail(value)) {
                    isValid = false;
                    message = 'Please enter a valid email address';
                }
                break;
                
            case 'subject':
                if (value.length < 5) {
                    isValid = false;
                    message = 'Subject must be at least 5 characters long';
                }
                break;
                
            case 'message':
                if (value.length < 10) {
                    isValid = false;
                    message = 'Message must be at least 10 characters long';
                }
                break;
        }
        
        if (!isValid) {
            showFieldError(field, message);
        } else {
            showFieldSuccess(field);
        }
        
        return isValid;
    }
    
    function clearFieldValidation(e) {
        const field = e.target;
        removeFieldValidation(field);
    }
    
    function removeFieldValidation(field) {
        field.classList.remove('invalid', 'valid');
        
        // Remove existing validation message
        const existingMessage = field.parentNode.querySelector('.validation-message');
        if (existingMessage) {
            existingMessage.remove();
        }
    }
    
    function showFieldError(field, message) {
        field.classList.add('invalid');
        field.classList.remove('valid');
        
        const messageElement = document.createElement('div');
        messageElement.className = 'validation-message error';
        messageElement.textContent = message;
        field.parentNode.appendChild(messageElement);
    }
    
    function showFieldSuccess(field) {
        field.classList.add('valid');
        field.classList.remove('invalid');
        
        const messageElement = document.createElement('div');
        messageElement.className = 'validation-message success';
        messageElement.textContent = 'Looks good!';
        field.parentNode.appendChild(messageElement);
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }
    
    function handleFormSubmit(e) {
        e.preventDefault();
        console.log('Form submission started');
        
        // Validate all fields
        let isFormValid = true;
        formControls.forEach(control => {
            const event = new Event('blur');
            control.dispatchEvent(event);
            if (control.classList.contains('invalid')) {
                isFormValid = false;
            }
        });
        
        if (!isFormValid) {
            showNotification('Please fix the errors in the form before submitting.', 'error');
            return;
        }
        
        // Show loading state
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }
        
        // Simulate form submission (in real application, this would be an AJAX call)
        setTimeout(() => {
            // This is where you would typically make an AJAX call
            // For now, we'll just show a success message
            showNotification('Message sent successfully! We will get back to you soon.', 'success');
            
            // Reset form
            contactForm.reset();
            
            // Remove loading state
            if (submitBtn) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
            
            // Remove validation classes
            formControls.forEach(control => {
                removeFieldValidation(control);
            });
            
        }, 2000);
    }
    
    function autoFillSubject() {
        const urlParams = new URLSearchParams(window.location.search);
        const service = urlParams.get('service');
        
        if (service) {
            const subjectField = document.querySelector('input[name="subject"]');
            if (subjectField && !subjectField.value) {
                subjectField.value = `Service Inquiry: ${service}`;
            }
        }
    }
    
    function animateOnScroll() {
        const contactSections = document.querySelectorAll('.contact-info, .contact-form');
        
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver(function(entries, observer) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (entry.target.classList.contains('contact-info')) {
                        entry.target.classList.add('slide-in-left');
                    } else {
                        entry.target.classList.add('slide-in-right');
                    }
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        contactSections.forEach(section => {
            observer.observe(section);
        });
    }
    
    function showNotification(message, type) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.custom-notification');
        existingNotifications.forEach(notification => {
            notification.remove();
        });
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `custom-notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 2rem;
            right: 2rem;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        `;
        
        if (type === 'success') {
            notification.style.background = 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)';
        } else {
            notification.style.background = 'linear-gradient(135deg, #f44336 0%, #d32f2f 100%)';
        }
        
        // Add to page
        document.body.appendChild(notification);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }
    
    // Add interactive effects to contact items
    const contactItems = document.querySelectorAll('.contact-item');
    contactItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});