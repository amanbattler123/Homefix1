<?php
require_once '../../includes/config.php';
require_once '../../controllers/TechnicianController.php';

$conn = getDBConnection();
$technicianController = new TechnicianController($conn);
$stats = $technicianController->getDashboardStats();
$unreadNotifications = $technicianController->getUnreadNotificationCount();
$currentPage = $currentPage ?? basename($_SERVER['PHP_SELF']);

// Build sidebar avatar image URL from session profile photo if available
$sidebarProfilePhoto = '';
if (!empty($_SESSION['profile_photo'])) {
    $sidebarProfilePhoto = SITE_URL . '/assets/uploads/profiles/' . rawurlencode($_SESSION['profile_photo']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeFix Pro - Technician Sidebar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Using landing.php color scheme */
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --success: #4cc9f0;
            --card-bg: rgba(255, 255, 255, 0.93);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            
            /* Additional colors for sidebar */
            --sidebar-bg: #16213e;
            --sidebar-dark: #0f172a;
            --sidebar-light: rgba(255, 255, 255, 0.1);
            --sidebar-text: #ffffff;
            --sidebar-text-muted: rgba(255, 255, 255, 0.7);
            --sidebar-border: rgba(255, 255, 255, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--sidebar-dark) 0%, var(--sidebar-bg) 100%);
            color: var(--sidebar-text);
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: var(--transition);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 3px;
        }

        /* User Profile Section */
        .user-profile {
            padding: 30px 25px;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.05);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .user-profile:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .user-profile::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .user-avatar {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 24px;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
            border: 3px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: var(--transition);
        }

        .user-profile:hover .user-avatar {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-info h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--sidebar-text);
        }

        .user-info p {
            margin: 5px 0 0;
            font-size: 13px;
            color: var(--sidebar-text-muted);
            font-weight: 500;
        }

        .user-status {
            width: 14px;
            height: 14px;
            background: linear-gradient(135deg, var(--success), #38f9d7);
            border-radius: 50%;
            border: 2px solid var(--sidebar-bg);
            box-shadow: 0 0 0 2px rgba(76, 201, 240, 0.4);
            position: absolute;
            bottom: 25px;
            right: 25px;
        }

        /* Quick Stats */
        .quick-stats {
            padding: 20px 25px;
            border-bottom: 1px solid var(--sidebar-border);
            background: rgba(255, 255, 255, 0.03);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 18px 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--sidebar-border);
        }

        .stat-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border-color: var(--primary);
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .stat-item:hover::before {
            transform: scaleX(1);
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--success);
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }

        .stat-label {
            font-size: 11px;
            color: var(--sidebar-text-muted);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        /* Navigation Sections */
        .nav-section {
            margin: 20px 0;
        }

        .nav-section-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--sidebar-text-muted);
            padding: 0 25px 15px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, var(--sidebar-border), transparent);
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
            padding: 15px 25px;
            color: var(--sidebar-text-muted);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            position: relative;
            margin: 0 10px;
            border-radius: 10px;
            font-size: 14px;
            overflow: hidden;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--sidebar-text);
            transform: translateX(5px);
        }

        .nav-item.active {
            background: rgba(67, 97, 238, 0.15);
            color: var(--sidebar-text);
            border-left: 4px solid var(--primary);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(67, 97, 238, 0.2), transparent);
        }

        .nav-icon {
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: var(--transition);
            position: relative;
            z-index: 1;
        }

        .nav-item:hover .nav-icon,
        .nav-item.active .nav-icon {
            transform: scale(1.1);
            color: var(--success);
        }

        .nav-text {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
            z-index: 1;
            font-weight: 500;
        }

        .notification-badge {
            background: linear-gradient(135deg, var(--accent), #b5179e);
            min-width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-left: auto;
            box-shadow: 0 3px 8px rgba(247, 37, 133, 0.3);
            animation: pulse 2s infinite;
            font-size: 0;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Logout Section */
        .logout-section {
            margin-top: auto;
            padding: 20px 0;
            border-top: 1px solid var(--sidebar-border);
            background: rgba(255, 255, 255, 0.03);
        }

        .nav-item.logout {
            background: rgba(244, 67, 54, 0.1);
            color: #ff6b6b;
        }

        .nav-item.logout:hover {
            background: rgba(244, 67, 54, 0.2);
            color: #ff5252;
        }

        .nav-item.logout .nav-icon {
            color: #ff6b6b;
        }

        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 260px;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.3);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .mobile-toggle {
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                background: linear-gradient(135deg, var(--primary), var(--secondary));
                color: white;
                border: none;
                width: 50px;
                height: 50px;
                border-radius: 12px;
                font-size: 18px;
                cursor: pointer;
                box-shadow: var(--shadow);
                display: flex;
                align-items: center;
                justify-content: center;
                transition: var(--transition);
            }

            .mobile-toggle:hover {
                transform: scale(1.1);
                box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
            }
        }

        @media (min-width: 993px) {
            .mobile-toggle {
                display: none;
            }
        }

        /* Floating animation (from landing.php) */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <button class="mobile-toggle" id="mobileToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <!-- User Profile -->
        <div class="user-profile" onclick="window.location.href='profile.php'">
            <div class="user-avatar floating">
                <?php if(!empty($sidebarProfilePhoto)): ?>
                    <img src="<?php echo htmlspecialchars($sidebarProfilePhoto); ?>" alt="Profile Photo">
                <?php else: ?>
                    <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'T', 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <h4><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Technician'); ?></h4>
                <p>Technician Account</p>
            </div>
            <div class="user-status" title="Online"></div>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-item" onclick="window.location.href='pending_tasks.php'">
                <div class="stat-value"><?php echo htmlspecialchars($stats['pending_jobs'] ?? '0'); ?></div>
                <div class="stat-label">Pending Jobs</div>
            </div>
            <div class="stat-item" onclick="window.location.href='my_tasks.php'">
                <div class="stat-value"><?php echo htmlspecialchars($stats['active_jobs'] ?? '0'); ?></div>
                <div class="stat-label">Active Jobs</div>
            </div>
        </div>

        <!-- Main Menu -->
        <div class="nav-section">
            <h3 class="nav-section-title">Main Menu</h3>
            <div class="sidebar-nav">
                <a href="dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-chart-line"></i></div>
                    <span class="nav-text">Dashboard</span>
                    <?php if(!empty($unreadNotifications)): ?>
                        <span class="notification-badge"></span>
                    <?php endif; ?>
                </a>
                <a href="profile.php" class="nav-item <?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-user-cog"></i></div>
                    <span class="nav-text">My Profile</span>
                </a>
                <a href="pending_tasks.php" class="nav-item <?php echo $currentPage === 'pending_tasks.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-inbox"></i></div>
                    <span class="nav-text">Pending Tasks</span>
                    <?php if(!empty($stats['pending_jobs'])): ?>
                        <span class="notification-badge"></span>
                    <?php endif; ?>
                </a>
                <a href="my_tasks.php" class="nav-item <?php echo $currentPage === 'my_tasks.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-tools"></i></div>
                    <span class="nav-text">My Tasks</span>
                    <?php if(!empty($stats['active_jobs'])): ?>
                        <span class="notification-badge"></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Services -->
        <div class="nav-section">
            <h3 class="nav-section-title">Services</h3>
            <div class="sidebar-nav">
                <a href="payments.php" class="nav-item <?php echo $currentPage === 'payments.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-credit-card"></i></div>
                    <span class="nav-text">Payments</span>
                    <?php if(!empty($stats['pending_payments'])): ?>
                        <span class="notification-badge"></span>
                    <?php endif; ?>
                </a>
                <a href="reviews.php" class="nav-item <?php echo $currentPage === 'reviews.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-star"></i></div>
                    <span class="nav-text">Reviews</span>
                    <?php if(!empty($stats['unseen_reviews'])): ?>
                        <span class="notification-badge"></span>
                    <?php endif; ?>
                </a>
                <a href="messages.php" class="nav-item <?php echo $currentPage === 'messages.php' ? 'active' : ''; ?>">
                    <div class="nav-icon"><i class="fas fa-comments"></i></div>
                    <span class="nav-text">Messages</span>
                    <?php if(!empty($stats['unread_messages'])): ?>
                        <span class="notification-badge"></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Logout -->
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

            // Mobile toggle functionality
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
                this.classList.toggle('active');

                if (sidebar.classList.contains('mobile-open')) {
                    this.innerHTML = '<i class="fas fa-times"></i>';
                    this.style.background = 'linear-gradient(135deg, var(--accent), #b5179e)';
                } else {
                    this.innerHTML = '<i class="fas fa-bars"></i>';
                    this.style.background = 'linear-gradient(135deg, var(--primary), var(--secondary))';
                }
            });

            // Check screen size and adjust sidebar
            function checkScreenSize() {
                if (window.innerWidth <= 992) {
                    mobileToggle.style.display = 'flex';
                    sidebar.classList.remove('mobile-open');
                    mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    mobileToggle.style.background = 'linear-gradient(135deg, var(--primary), var(--secondary))';
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
                    mobileToggle.style.background = 'linear-gradient(135deg, var(--primary), var(--secondary))';
                }
            });

            // Add ripple effect to nav items
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const ripple = document.createElement('span');
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.3);
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        pointer-events: none;
                        width: 100px;
                        height: 100px;
                        left: ${x - 50}px;
                        top: ${y - 50}px;
                    `;
                    
                    this.appendChild(ripple);
                    setTimeout(() => ripple.remove(), 600);
                });
            });

            // Add ripple animation style
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>