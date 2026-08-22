<?php
session_start();
require_once '../../includes/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'homeowner') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../controllers/HomeownerController.php';

$conn = getDBConnection();
$homeownerController = new HomeownerController($conn);

$stats = $homeownerController->getDashboardStats();
$recentRequests = $homeownerController->getRecentServiceRequests();
$detailedStats = $homeownerController->getDetailedStats();
$detailServices = $detailedStats['services'];
$detailPayments = $detailedStats['payments'];
$detailMessages = $detailedStats['messages'];
$notifications = $homeownerController->getNotifications(5);
$unreadNotifications = $homeownerController->getUnreadNotificationCount();

$pageTitle = 'Dashboard Overview';
$pageDescription = 'Keep track of your service activity, progress, and recent technician updates.';
$headerActions = [
    [
        'label' => 'Request Service',
        'href' => 'request_service.php',
        'variant' => 'primary',
        'icon' => 'fa-solid fa-plus'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Homefix Pro</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --dark: #1e1e2c;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            --gradient-secondary: linear-gradient(135deg, #7209b7 0%, #3a0ca3 100%);
            --gradient-success: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);
            --gradient-warning: linear-gradient(135deg, #f8961e 0%, #f3722c 100%);
            --gradient-dark: linear-gradient(135deg, #1e1e2c 0%, #2d2d44 100%);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 15px 50px rgba(0, 0, 0, 0.12);
            --radius: 16px;
            --radius-lg: 24px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: var(--dark);
            min-height: 100vh;
            line-height: 1.6;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            overflow-y: auto;
        }

        /* Header Styles */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 25px 30px;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
        }

        .welcome-section h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-section p {
            color: var(--gray);
            font-size: 16px;
            max-width: 500px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 14px 28px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            box-shadow: var(--shadow);
            text-decoration: none;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .notification-bell {
            position: relative;
            background: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            cursor: pointer;
            transition: var(--transition);
        }

        .notification-bell:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .notification-bell i {
            font-size: 20px;
            color: var(--gray);
        }

        .notification-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: var(--danger);
            color: white;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-left: 6px solid var(--primary);
        }

        .stat-card:nth-child(2) {
            border-left-color: var(--warning);
        }

        .stat-card:nth-child(3) {
            border-left-color: var(--success);
        }

        .stat-card:nth-child(4) {
            border-left-color: var(--secondary);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .stat-number {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .stat-label {
            font-size: 16px;
            color: var(--gray);
            font-weight: 500;
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .stat-card:nth-child(4) .stat-icon {
            background: rgba(63, 55, 201, 0.1);
            color: var(--secondary);
        }

        .stat-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--gray-light);
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            font-weight: 600;
        }

        .trend-up {
            color: #10b981;
        }

        .trend-down {
            color: var(--danger);
        }

        /* Recent Requests Card */
        .card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 35px;
            transition: var(--transition);
            margin-bottom: 30px;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .card-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
        }

        .card-subtitle {
            color: var(--gray);
            font-size: 16px;
        }

        .btn-secondary {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            border: none;
            padding: 12px 24px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .btn-secondary:hover {
            background: rgba(67, 97, 238, 0.2);
            transform: translateY(-2px);
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            border-radius: var(--radius);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: rgba(67, 97, 238, 0.05);
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            font-size: 15px;
            border-bottom: 2px solid var(--gray-light);
        }

        td {
            padding: 18px 15px;
            border-bottom: 1px solid var(--gray-light);
            font-size: 15px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(67, 97, 238, 0.03);
        }

        .status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
            display: inline-block;
        }

        .status-pending {
            background: rgba(248, 150, 30, 0.15);
            color: #f8961e;
        }

        .status-completed {
            background: rgba(76, 201, 240, 0.15);
            color: #4cc9f0;
        }

        .status-in-progress {
            background: rgba(67, 97, 238, 0.15);
            color: #4361ee;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .action-card {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-align: center;
            cursor: pointer;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .action-card:nth-child(2) .action-icon {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .action-card:nth-child(3) .action-icon {
            background: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }

        .action-card:nth-child(4) .action-icon {
            background: rgba(63, 55, 201, 0.1);
            color: var(--secondary);
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .action-desc {
            font-size: 13px;
            color: var(--gray);
        }

        /* Empty State */
        .alert {
            padding: 25px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(76, 201, 240, 0.1);
            color: var(--dark);
            margin-top: 20px;
        }

        .alert i {
            font-size: 24px;
            color: var(--success);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 280px;
                padding: 25px;
            }
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .btn-primary, .btn-secondary {
                flex: 1;
                justify-content: center;
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Animation for elements */
        [data-aos] {
            opacity: 0;
            transition-property: opacity, transform;
        }

        [data-aos].aos-animate {
            opacity: 1;
        }

        /* Menu Toggle for Mobile */
        .menu-toggle {
            display: none;
            background: var(--gradient-primary);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            box-shadow: var(--shadow);
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1001;
        }

        @media (max-width: 992px) {
            .menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="homeowner-body">
    <div class="dashboard">
        <!-- Include the new sidebar component -->
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <!-- Header -->
            <div class="dashboard-header">
                <div class="welcome-section">
                    <h1>Welcome back, Homeowner!</h1>
                    <p>Keep track of your service activity, progress, and recent technician updates. Everything you need in one place.</p>
                </div>
                <div class="header-actions">
                    <a href="#notifications" class="notification-bell" title="View notifications">
                        <i class="fa-solid fa-bell"></i>
                        <?php if($unreadNotifications > 0): ?>
                        <span class="notification-badge"></span>
                        <?php endif; ?>
                    </a>
                    <a href="download_stats.php" class="btn-primary" style="background: var(--gradient-secondary);">
                        <i class="fa-solid fa-download"></i>
                        Download My Statistics
                    </a>
                    <a href="request_service.php" class="btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        Request Service
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card" data-aos="fade-up" data-aos-delay="50">
                    <div class="action-icon">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>
                    <div class="action-title">Schedule Service</div>
                    <div class="action-desc">Book a technician visit</div>
                </div>
                <div class="action-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="action-icon">
                        <i class="fa-solid fa-message"></i>
                    </div>
                    <div class="action-title">Messages</div>
                    <div class="action-desc">Chat with technicians</div>
                </div>
                <div class="action-card" data-aos="fade-up" data-aos-delay="150">
                    <div class="action-icon">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div class="action-title">Payments</div>
                    <div class="action-desc">View billing history</div>
                </div>
                <div class="action-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="action-icon">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <div class="action-title">Reviews</div>
                    <div class="action-desc">Rate your services</div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid" data-searchable="dashboard stats total pending completed messages">
                <div class="stat-card" data-aos="fade-up" data-aos-delay="50">
                    <div class="stat-header">
                        <div>
                            <div class="stat-number"><?php echo isset($stats['total_requests']) ? $stats['total_requests'] : 0; ?></div>
                            <div class="stat-label">Total Requests</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <div class="stat-more">
                            <i class="fa-solid fa-ellipsis"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-header">
                        <div>
                            <div class="stat-number"><?php echo isset($stats['pending_requests']) ? $stats['pending_requests'] : 0; ?></div>
                            <div class="stat-label">Pending Requests</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <div class="stat-more">
                            <i class="fa-solid fa-ellipsis"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card" data-aos="fade-up" data-aos-delay="150">
                    <div class="stat-header">
                        <div>
                            <div class="stat-number"><?php echo isset($stats['completed_requests']) ? $stats['completed_requests'] : 0; ?></div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <div class="stat-more">
                            <i class="fa-solid fa-ellipsis"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-header">
                        <div>
                            <div class="stat-number"><?php echo isset($stats['unread_messages']) ? $stats['unread_messages'] : 0; ?></div>
                            <div class="stat-label">Unread Messages</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <div class="stat-more">
                            <i class="fa-solid fa-ellipsis"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Service Requests -->
            <div class="card" data-searchable="recent service requests">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Recent Service Requests</h3>
                        <p class="card-subtitle">Monitor the latest activity with your technicians.</p>
                    </div>
                    <button class="btn-secondary" onclick="document.querySelector('.stats-grid').scrollIntoView({ behavior: 'smooth' })">
                        <i class="fa-solid fa-chart-line"></i>
                        View Stats
                    </button>
                </div>
                <?php if(isset($recentRequests) && is_array($recentRequests) && count($recentRequests) > 0): ?>
                    <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Service Type</th>
                                <th>Technician</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentRequests as $request): ?>
                                <tr data-aos="fade-up">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <i class="fa-solid fa-toolbox text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold"><?php echo htmlspecialchars($request['title']); ?></div>
                                                <div class="text-muted small">ID: #<?php echo $request['id'] ?? 'N/A'; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($request['service_type']); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm mr-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                <?php 
                                                    $technicianName = $request['technician_name'] ?? 'Not assigned';
                                                    echo substr($technicianName, 0, 1); 
                                                ?>
                                            </div>
                                            <span><?php echo $technicianName; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status status-<?php echo $request['status']; ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($request['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php else: ?>
                    <div class="alert">
                        <i class="fa-solid fa-inbox"></i>
                        <div>
                            <h4>No service requests yet</h4>
                            <p>Start by creating your first service request to get help with your home maintenance needs.</p>
                            <a href="request_service.php" class="btn-primary mt-2" style="display: inline-flex;">
                                <i class="fa-solid fa-plus"></i>
                                Create First Request
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Upcoming Appointments -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Upcoming Appointments</h3>
                        <p class="card-subtitle">Your scheduled technician visits.</p>
                    </div>
                    <button class="btn-secondary">
                        <i class="fa-solid fa-calendar"></i>
                        View Calendar
                    </button>
                </div>
                <div class="alert">
                    <i class="fa-solid fa-calendar-check"></i>
                    <div>
                        <h4>No upcoming appointments</h4>
                        <p>Schedule a service to see your appointments here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animation library
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('mobile-open');
                });
            }

            // Add hover effects to cards
            const cards = document.querySelectorAll('.stat-card, .card, .action-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Button click effects
            const buttons = document.querySelectorAll('.btn-primary, .btn-secondary');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });

            // Action card clicks
            const actionCards = document.querySelectorAll('.action-card');
            actionCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Add your navigation logic here
                    alert('Action clicked: ' + this.querySelector('.action-title').textContent);
                });
            });
        });
    </script>
</body>
</html>