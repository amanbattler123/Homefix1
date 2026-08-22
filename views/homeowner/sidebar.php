<?php
require_once '../../includes/config.php';

$homeSidebarProfilePhoto = '';
if (!empty($_SESSION['profile_photo'])) {
    $homeSidebarProfilePhoto = SITE_URL . '/assets/uploads/profiles/' . rawurlencode($_SESSION['profile_photo']);
}
$homeSidebarUserName = $_SESSION['user_name'] ?? 'Homeowner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeFix Pro - Sidebar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Modern CSS Variables */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --dark-bg: #0f172a;
            --darker-bg: #0a0f1c;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.1);
            --shadow-lg: 0 10px 25px -3px rgba(0, 0, 0, 0.3);
            --shadow-xl: 0 20px 40px -10px rgba(0, 0, 0, 0.4);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: var(--dark-bg);
            color: var(--text-primary);
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: var(--shadow-xl);
            transition: var(--transition);
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--accent-gradient);
        }

        /* User Profile Section */
        .user-profile {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 15px;
            background: var(--card-bg);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .user-profile::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.05), transparent);
            transition: left 0.7s ease;
        }

        .user-profile:hover::before {
            left: 100%;
        }

        .user-profile:hover {
            background: rgba(30, 41, 59, 0.9);
        }

        .user-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: var(--transition);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-avatar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.1) 100%);
            pointer-events: none;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-info h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-info p {
            margin: 3px 0 0;
            font-size: 13px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-status {
            width: 12px;
            height: 12px;
            background: var(--success-gradient);
            border-radius: 50%;
            border: 2px solid var(--dark-bg);
            box-shadow: 0 0 0 2px rgba(67, 233, 123, 0.4);
            animation: statusPulse 2s infinite;
        }

        @keyframes statusPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(67, 233, 123, 0.4); }
            50% { box-shadow: 0 0 0 4px rgba(67, 233, 123, 0); }
        }

        /* Navigation Sections */
        .nav-section {
            margin: 20px 0;
        }

        .nav-section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            padding: 0 25px 12px;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .nav-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
            margin-left: 10px;
        }

        .sidebar-nav {
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 25px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            position: relative;
            margin: 0 10px;
            border-radius: 12px;
            font-size: 15px;
            overflow: hidden;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--primary-gradient);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            transform: translateX(5px);
        }

        .nav-item:hover::before {
            transform: scaleY(1);
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .nav-item.active::before {
            transform: scaleY(1);
        }

        .nav-icon {
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: var(--transition);
            position: relative;
            z-index: 1;
        }

        .nav-item:hover .nav-icon,
        .nav-item.active .nav-icon {
            transform: scale(1.15);
        }

        .nav-text {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
            z-index: 1;
        }

        /* Notification Badge */
        .notification-badge {
            background: var(--warning-gradient);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            min-width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            box-shadow: 0 3px 12px rgba(250, 112, 154, 0.5);
            animation: bounce 2s infinite;
            position: relative;
            z-index: 2;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { 
                transform: translateY(0) scale(1); 
            }
            40% { 
                transform: translateY(-4px) scale(1.15); 
            }
            60% { 
                transform: translateY(-2px) scale(1.05); 
            }
        }

        /* Quick Stats */
        .quick-stats {
            padding: 20px 25px;
            border-top: 1px solid var(--border-color);
            background: var(--card-bg);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 15px 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-item:hover::before {
            opacity: 0.1;
        }

        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: white;
            margin-bottom: 4px;
            position: relative;
            z-index: 1;
        }

        .stat-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }

        /* Logout Button Special Styling */
        .logout-section {
            margin-top: auto;
            padding: 20px 0;
            border-top: 1px solid var(--border-color);
        }

        .nav-item.logout {
            background: rgba(244, 67, 54, 0.1) !important;
            margin: 0 10px;
        }

        .nav-item.logout:hover {
            background: rgba(244, 67, 54, 0.2) !important;
        }

        .nav-item.logout:hover .nav-icon {
            transform: rotate(-15deg) scale(1.2);
        }

        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 240px;
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
                box-shadow: 8px 0 40px rgba(0, 0, 0, 0.5);
            }
            
            .nav-item {
                padding: 14px 20px;
                margin: 0 8px;
            }
            
            .quick-stats {
                padding: 15px 20px;
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .quick-stats {
                padding: 15px 20px;
                grid-template-columns: 1fr;
            }
        }

        /* Focus states for accessibility */
        .nav-item:focus {
            outline: 2px solid rgba(76, 201, 240, 0.6);
            outline-offset: 2px;
        }

        /* Active state glow effect */
        .nav-item.active::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255,255,255,0.1), 
                transparent);
            transition: left 0.6s ease;
        }

        .nav-item.active:hover::after {
            left: 100%;
        }

        /* Mobile Toggle Button */
        .mobile-toggle {
            position: fixed;
            top: 25px;
            left: 25px;
            z-index: 1001;
            background: var(--primary-gradient);
            color: white;
            border: none;
            width: 55px;
            height: 55px;
            border-radius: 16px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            display: none;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" id="mobileToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <!-- User Profile Section -->
        <div class="user-profile" onclick="window.location.href='profile.php'">
            <div class="user-avatar">
                <?php if(!empty($homeSidebarProfilePhoto)): ?>
                    <img src="<?php echo htmlspecialchars($homeSidebarProfilePhoto); ?>" alt="Profile Photo">
                <?php else: ?>
                    <?php echo strtoupper(substr($homeSidebarUserName, 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <h4><?php echo htmlspecialchars($homeSidebarUserName); ?></h4>
                <h4><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Homeowner'); ?></h4>
                <p>Homeowner Account</p>
            </div>
            <div class="user-status" title="Online"></div>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-item" onclick="window.location.href='bookings.php'">
                <div class="stat-value"><?php echo $stats['total_requests'] ?? '0'; ?></div>
                <div class="stat-label">Requests</div>
            </div>
            <div class="stat-item" onclick="window.location.href='messages.php'">
                <div class="stat-value"><?php echo $stats['unread_messages'] ?? '0'; ?></div>
                <div class="stat-label">Unread</div>
            </div>
        </div>

        <!-- Main Navigation -->
        <div class="nav-section">
            <h3 class="nav-section-title">Main Menu</h3>
            <div class="sidebar-nav">
                <a href="dashboard.php" class="nav-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-chart-pie"></i></div>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="profile.php" class="nav-item <?php echo $currentPage == 'profile.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-user"></i></div>
                    <span class="nav-text">My Profile</span>
                </a>
                <a href="request_service.php" class="nav-item <?php echo $currentPage == 'request_service.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-tools"></i></div>
                    <span class="nav-text">Request Service</span>
                </a>
                <a href="technicians.php" class="nav-item <?php echo $currentPage == 'technicians.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-user-cog"></i></div>
                    <span class="nav-text">Technicians</span>
                </a>
            </div>
        </div>

        <!-- Services Navigation -->
        <div class="nav-section">
            <h3 class="nav-section-title">Services</h3>
            <div class="sidebar-nav">
                <a href="bookings.php" class="nav-item <?php echo $currentPage == 'bookings.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-calendar-alt"></i></div>
                    <span class="nav-text">My Bookings</span>
                </a>
                <a href="messages.php" class="nav-item <?php echo $currentPage == 'messages.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-comments"></i></div>
                    <span class="nav-text">Messages</span>
                    <?php if(isset($stats['unread_messages']) && $stats['unread_messages'] > 0): ?>
                        <span class="notification-badge">
                            <?php echo $stats['unread_messages']; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="payments.php" class="nav-item <?php echo $currentPage == 'payments.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-credit-card"></i></div>
                    <span class="nav-text">Payments</span>
                </a>
                <a href="feedback.php" class="nav-item <?php echo $currentPage == 'feedback.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-star"></i></div>
                    <span class="nav-text">Give Feedback</span>
                </a>
            </div>
        </div>

        <!-- Logout Section -->
        <div class="logout-section">
            <div class="sidebar-nav">
                <a href="../../logout.php" class="nav-item logout">
                    <div class="nav-icon"><i class="fas fa-sign-out-alt"></i></div>
                    <span class="nav-text">Logout</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.getElementById('mobileToggle');
            const navItems = document.querySelectorAll('.nav-item');
            
            // Mobile menu toggle
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
                this.classList.toggle('active');
                
                if (sidebar.classList.contains('mobile-open')) {
                    this.innerHTML = '<i class="fas fa-times"></i>';
                    this.style.background = 'var(--accent-gradient)';
                } else {
                    this.innerHTML = '<i class="fas fa-bars"></i>';
                    this.style.background = 'var(--primary-gradient)';
                }
            });
            
            // Show/hide mobile toggle based on screen size
            function checkScreenSize() {
                if (window.innerWidth <= 992) {
                    mobileToggle.style.display = 'flex';
                    sidebar.classList.remove('mobile-open');
                    mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    mobileToggle.style.background = 'var(--primary-gradient)';
                } else {
                    mobileToggle.style.display = 'none';
                    sidebar.classList.remove('mobile-open');
                }
            }
            
            checkScreenSize();
            window.addEventListener('resize', checkScreenSize);
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 992 && 
                    !sidebar.contains(e.target) && 
                    !mobileToggle.contains(e.target) && 
                    sidebar.classList.contains('mobile-open')) {
                    sidebar.classList.remove('mobile-open');
                    mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    mobileToggle.style.background = 'var(--primary-gradient)';
                }
            });
            
            // Add ripple effect to nav items
            navItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Remove active class from all items
                    navItems.forEach(nav => nav.classList.remove('active'));
                    
                    // Add active class to clicked item
                    this.classList.add('active');
                    
                    // Create ripple effect
                    createRippleEffect(this, e);
                });
            });
            
            function createRippleEffect(element, event) {
                const ripple = document.createElement('span');
                const rect = element.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = event.clientX - rect.left - size / 2;
                const y = event.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    pointer-events: none;
                    z-index: 0;
                `;
                
                element.style.position = 'relative';
                element.style.overflow = 'hidden';
                element.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            }
            
            // Add the ripple animation to the style
            const rippleStyle = document.createElement('style');
            rippleStyle.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(rippleStyle);
        });
    </script>
</body>
</html>