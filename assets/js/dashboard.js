// Dashboard functionality
class Dashboard {
    constructor() {
        this.init();
    }

    init() {
        this.simulateLiveMetrics();
        this.updateGreeting();
        this.simulateLiveActivity();
        this.setupEventListeners();
        this.initializeCharts();
        this.startLiveUpdates();
    }

    // Live metrics simulation
    simulateLiveMetrics() {
        const liveRequests = document.getElementById('liveRequests');
        if (!liveRequests) return;

        let count = 12;
        
        setInterval(() => {
            count += Math.floor(Math.random() * 3) - 1;
            if (count < 8) count = 8;
            if (count > 20) count = 20;
            liveRequests.textContent = count;
            
            // Add animation
            liveRequests.style.transform = 'scale(1.1)';
            setTimeout(() => {
                liveRequests.style.transform = 'scale(1)';
            }, 300);
        }, 3000);
    }

    // Time-based greeting
    updateGreeting() {
        const greetingElement = document.querySelector('.time-based-greeting');
        if (!greetingElement) return;

        const hour = new Date().getHours();
        
        if (hour < 12) {
            greetingElement.textContent = 'morning';
        } else if (hour < 18) {
            greetingElement.textContent = 'afternoon';
        } else {
            greetingElement.textContent = 'evening';
        }
    }

    // Simulate live activity
    simulateLiveActivity() {
        const stream = document.querySelector('.activity-stream');
        if (!stream) return;

        const activities = [
            'New service request: Plumbing emergency',
            'Technician John completed a job',
            'New review received: ⭐⭐⭐⭐⭐',
            'Payment processed: $85.00',
            'New message from customer',
            'Service scheduled for tomorrow',
            'Technician availability updated',
            'New feature released: Real-time tracking'
        ];
        
        setInterval(() => {
            const activity = activities[Math.floor(Math.random() * activities.length)];
            const activityElement = document.createElement('div');
            activityElement.className = 'activity-item';
            activityElement.innerHTML = `
                <div class="activity-icon">
                    <i class="fas fa-circle"></i>
                </div>
                <div class="activity-content">
                    <span>${activity}</span>
                    <span class="activity-time">just now</span>
                </div>
            `;
            
            stream.insertBefore(activityElement, stream.firstChild);
            
            // Remove old activities
            if (stream.children.length > 5) {
                stream.removeChild(stream.lastChild);
            }
        }, 5000);
    }

    // Setup event listeners
    setupEventListeners() {
        // Add hover effects to all interactive elements
        const interactiveElements = document.querySelectorAll('.metric-card, .advanced-card, .service-item, .job-item, .alert-item, .request-item');
        interactiveElements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            element.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Service items click handler
        const serviceItems = document.querySelectorAll('.service-item');
        serviceItems.forEach(item => {
            item.addEventListener('click', function() {
                const service = this.getAttribute('data-service');
                this.handleServiceSelection(service);
            });
        });

        // Action buttons
        const actionButtons = document.querySelectorAll('.action-btn, .modern-btn');
        actionButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                this.handleActionButton(this);
            });
        });

        // Navigation buttons
        const navButtons = document.querySelectorAll('.nav-btn');
        navButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.handleNavButton(this);
            });
        });
    }

    // Handle service selection
    handleServiceSelection(service) {
        console.log(`Selected service: ${service}`);
        // In a real app, this would navigate to the service booking page
        this.showNotification(`Opening ${service} service booking...`, 'info');
    }

    // Handle action buttons
    handleActionButton(button) {
        const buttonText = button.textContent.trim();
        console.log(`Action button clicked: ${buttonText}`);
        
        // Simulate different actions based on button text
        if (buttonText.includes('Refresh')) {
            this.refreshData();
        } else if (buttonText.includes('New Request')) {
            this.showNotification('Opening new service request form...', 'info');
        } else if (buttonText.includes('Set Location')) {
            this.showNotification('Opening location settings...', 'info');
        }
    }

    // Handle navigation buttons
    handleNavButton(button) {
        const icon = button.querySelector('i').className;
        
        if (icon.includes('bell')) {
            this.showNotification('Showing notifications', 'info');
        } else if (icon.includes('envelope')) {
            this.showNotification('Opening messages', 'info');
        }
    }

    // Refresh data simulation
    refreshData() {
        this.showNotification('Refreshing data...', 'info');
        
        // Simulate API call
        setTimeout(() => {
            this.showNotification('Data refreshed successfully!', 'success');
            
            // Update some random metrics
            this.updateRandomMetrics();
        }, 1500);
    }

    // Update random metrics
    updateRandomMetrics() {
        const metrics = document.querySelectorAll('.metric-value');
        metrics.forEach(metric => {
            if (metric.id !== 'liveRequests' && Math.random() > 0.5) {
                const current = parseInt(metric.textContent.replace('$', '').replace(',', '')) || 0;
                const change = Math.floor(Math.random() * 20) - 5;
                const newValue = Math.max(0, current + change);
                
                if (metric.textContent.includes('$')) {
                    metric.textContent = `$${newValue}`;
                } else {
                    metric.textContent = newValue;
                }
                
                // Add animation
                metric.style.color = change >= 0 ? '#10b981' : '#ef4444';
                setTimeout(() => {
                    metric.style.color = '';
                }, 1000);
            }
        });
    }

    // Initialize charts (placeholder for real chart library)
    initializeCharts() {
        // This would be replaced with actual chart library initialization
        const charts = document.querySelectorAll('canvas');
        charts.forEach(chart => {
            chart.style.opacity = '0';
            chart.style.transition = 'opacity 1s ease, transform 1s ease';
            
            setTimeout(() => {
                chart.style.opacity = '1';
                chart.style.transform = 'scale(1)';
            }, 500);
        });
    }

    // Start live updates
    startLiveUpdates() {
        // Update random metrics periodically
        setInterval(() => {
            this.updateRandomMetrics();
        }, 15000);

        // Update time-based elements
        setInterval(() => {
            this.updateGreeting();
        }, 60000); // Update every minute
    }

    // Show notification
    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.dashboard-notification');
        existingNotifications.forEach(notification => {
            notification.remove();
        });

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `dashboard-notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 2rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        `;

        // Set background based on type
        const backgrounds = {
            success: 'linear-gradient(135deg, #10b981, #059669)',
            error: 'linear-gradient(135deg, #ef4444, #dc2626)',
            warning: 'linear-gradient(135deg, #f59e0b, #d97706)',
            info: 'linear-gradient(135deg, #6366f1, #4f46e5)'
        };

        notification.style.background = backgrounds[type] || backgrounds.info;

        // Add to page
        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const dashboard = new Dashboard();
    
    // Make dashboard globally available for debugging
    window.dashboard = dashboard;
});

// Additional utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatNumber(number) {
    return new Intl.NumberFormat('en-US').format(number);
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Dashboard;
}
