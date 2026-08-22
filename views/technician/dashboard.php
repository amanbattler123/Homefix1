<?php
session_start();
require_once '../../includes/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'technician') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../controllers/TechnicianController.php';

$conn = getDBConnection();
$technicianController = new TechnicianController($conn);
$flashMessage = '';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_notifications') {
    $success = $technicianController->clearNotifications();
    $flashMessage = $success
        ? '<div class="alert success">All notifications have been cleared.</div>'
        : '<div class="alert error">Unable to clear notifications. Please try again.</div>';
}

// When technician opens the dashboard, mark all notifications as read
$technicianController->markNotificationsRead();

$stats = $technicianController->getDashboardStats();
$recentRequests = $technicianController->getRecentRequests();
$pendingRequests = $technicianController->getPendingRequests();
$activeRequests = $technicianController->getActiveRequests();
$completedRequests = $technicianController->getCompletedRequests();
$detailedStats = $technicianController->getDetailedStats();
$detailServices = $detailedStats['services'];
$detailPayments = $detailedStats['payments'];
$detailMessages = $detailedStats['messages'];
$notifications = $technicianController->getNotifications(5);
$unreadNotifications = $technicianController->getUnreadNotificationCount();
$paymentQueue = array_filter($activeRequests, function($req) {
    return in_array($req['status'], ['payment_requested']);
});

if(!function_exists('technician_next_step')) {
    function technician_next_step(array $request): array {
        $status = $request['status'];
        switch($status) {
            case 'waiting_acceptance':
            case 'approved':
                return ['label' => 'Respond to Request', 'hint' => 'Review details and accept or decline the homeowner request.'];
            case 'price_proposed':
                return ['label' => 'Awaiting Homeowner Pricing Decision', 'hint' => 'You will be notified once the homeowner responds to your estimate.'];
            case 'price_accepted':
                return ['label' => 'Schedule Work', 'hint' => 'Begin working on-site and keep the homeowner updated.'];
            case 'waiting_inspection':
                return ['label' => 'Submit Inspection & Estimate', 'hint' => 'Provide inspection findings and proposed pricing.'];
            case 'in_progress':
            case 'assigned':
                return ['label' => 'Complete Work', 'hint' => 'Finish the service and mark it as completed when done.'];
            case 'payment_requested':
                return ['label' => 'Await Payment Upload', 'hint' => 'Homeowner is uploading proof. Confirm once received.'];
            case 'completed':
                return ['label' => 'Await Payment Verification', 'hint' => 'Payment verification pending.'];
            case 'paid':
                return ['label' => 'Task Closed', 'hint' => 'This job is fully paid and archived.'];
            default:
                return ['label' => ucfirst(str_replace('_',' ', $status)), 'hint' => 'Monitor progress.'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician Dashboard - HomeFix Pro</title>
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
            
            /* Additional colors */
            --sidebar-bg: #16213e;
            --sidebar-dark: #0f172a;
            --sidebar-light: rgba(255, 255, 255, 0.1);
            --sidebar-text: #ffffff;
            --sidebar-text-muted: rgba(255, 255, 255, 0.7);
            --sidebar-border: rgba(255, 255, 255, 0.15);
            
            /* Status colors */
            --status-pending: #ffb142;
            --status-active: #3498db;
            --status-completed: #2ecc71;
            --status-payment: #9b59b6;
            --status-cancelled: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: var(--dark);
            min-height: 100vh;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 280px;
        }

        .header {
            margin-bottom: 30px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(90deg, var(--success), white);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .welcome-text {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            max-width: 600px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            box-shadow: var(--shadow);
            border: none;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 5px;
        }

        .alert.success {
            background: rgba(76, 201, 240, 0.15);
            color: var(--success);
            border: 1px solid rgba(76, 201, 240, 0.3);
        }

        .alert.success::before {
            background: var(--success);
        }

        .alert.error {
            background: rgba(247, 37, 133, 0.15);
            color: var(--accent);
            border: 1px solid rgba(247, 37, 133, 0.3);
        }

        .alert.error::before {
            background: var(--accent);
        }

        /* Header Actions */
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .notification-bell {
            position: relative;
            display: inline-flex;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(30, 41, 59, 0.8);
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            border: 1px solid var(--sidebar-border);
            transition: var(--transition);
            cursor: pointer;
            color: var(--success);
            text-decoration: none;
        }

        .notification-bell:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            background: rgba(67, 97, 238, 0.2);
        }

        .notification-bell i {
            font-size: 1.3rem;
        }

        .notification-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background: linear-gradient(135deg, var(--accent), #b5179e);
            width: 10px;
            height: 10px;
            border-radius: 50%;
            font-size: 0;
            border: 2px solid var(--sidebar-dark);
            box-shadow: 0 3px 8px rgba(247, 37, 133, 0.3);
        }

        .download-btn {
            padding: 12px 25px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            box-shadow: var(--shadow);
        }

        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.4);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 25px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            border-color: var(--primary);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #000;
            line-height: 1;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #000;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            font-weight: 600;
        }

        /* Detailed Insights */
        .card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--sidebar-border);
            box-shadow: var(--shadow);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            color: white;
        }

        .section-header h3 {
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header h3 i {
            color: var(--success);
        }

        .detail-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .detail-block {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid var(--sidebar-border);
            transition: var(--transition);
        }

        .detail-block:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-3px);
        }

        .detail-block h4 {
            margin: 0 0 15px 0;
            color: var(--success);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-block ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .detail-block li {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .detail-block li:last-child {
            border-bottom: none;
        }

        .detail-block li span:first-child {
            color: rgba(255, 255, 255, 0.7);
        }

        .detail-block li span:last-child {
            font-weight: 600;
            color: white;
        }

        /* Highlight Card */
        .highlight-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(67, 97, 238, 0.3);
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }

        .highlight-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .highlight-card h3 {
            margin: 0 0 15px 0;
            font-size: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .highlight-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 1rem;
            line-height: 1.5;
            position: relative;
            z-index: 1;
        }

        /* Flow Grid */
        .flow-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .flow-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 25px;
            border: 1px solid var(--sidebar-border);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .flow-card:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-color: var(--primary);
        }

        .flow-card h4 {
            margin: 0;
            font-size: 1.1rem;
            color: white;
            font-weight: 600;
        }

        .flow-meta {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .flow-meta span {
            display: flex;
            gap: 8px;
        }

        .flow-meta strong {
            color: rgba(255, 255, 255, 0.9);
            min-width: 100px;
        }

        .status-chip {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            text-transform: capitalize;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-chip i {
            font-size: 0.7rem;
        }

        .status-pending { background: rgba(255, 177, 66, 0.2); color: #ffb142; border: 1px solid rgba(255, 177, 66, 0.3); }
        .status-active { background: rgba(52, 152, 219, 0.2); color: #3498db; border: 1px solid rgba(52, 152, 219, 0.3); }
        .status-completed { background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.3); }
        .status-payment { background: rgba(155, 89, 182, 0.2); color: #9b59b6; border: 1px solid rgba(155, 89, 182, 0.3); }

        .flow-next-step {
            background: rgba(67, 97, 238, 0.1);
            border-radius: 12px;
            padding: 15px;
            border: 1px solid rgba(67, 97, 238, 0.2);
        }

        .flow-next-step strong {
            display: block;
            margin-bottom: 5px;
            color: var(--success);
            font-size: 0.95rem;
        }

        .flow-next-step small {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .flow-actions {
            margin-top: auto;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            border: none;
        }

        .btn-outline {
            background: transparent;
            color: var(--success);
            border: 2px solid var(--success);
        }

        .btn-outline:hover {
            background: var(--success);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 201, 240, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            text-align: left;
            padding: 15px;
            border-bottom: 2px solid var(--sidebar-border);
        }

        table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.9);
        }

        table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        table tr:last-child td {
            border-bottom: none;
        }

        /* Timeline */
        .timeline {
            border-left: 2px solid var(--sidebar-border);
            margin-top: 20px;
            padding-left: 25px;
            position: relative;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: -2px;
            top: 0;
            height: 100%;
            width: 2px;
            background: linear-gradient(180deg, var(--primary), var(--accent));
            opacity: 0.3;
        }

        .timeline-item {
            margin-bottom: 20px;
            position: relative;
            padding-left: 10px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -33px;
            top: 8px;
            width: 12px;
            height: 12px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            border: 3px solid var(--sidebar-dark);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3);
        }

        .timeline-item strong {
            display: block;
            color: white;
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .timeline-item p {
            margin: 4px 0 0 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            line-height: 1.4;
        }

        /* Notifications */
        .notification-card .card-body {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .notification-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid var(--sidebar-border);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .notification-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(5px);
        }

        .notification-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(180deg, var(--success), var(--primary));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .notification-item:hover::before {
            opacity: 1;
        }

        .notification-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .notification-meta {
            flex: 1;
        }

        .notification-meta strong {
            display: block;
            color: white;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .notification-meta div {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .notification-meta small {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
        }

        .clear-btn {
            padding: 10px 20px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--accent), #b5179e);
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .clear-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(247, 37, 133, 0.3);
        }

        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: rgba(255, 255, 255, 0.7);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--success);
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 1rem;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.5;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
            
            .flow-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
                padding-top: 80px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .header-actions {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .flow-card {
                padding: 20px;
            }
            
            .flow-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .detail-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .flow-grid {
                grid-template-columns: 1fr;
            }
            
            .notification-bell {
                width: 45px;
                height: 45px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <div class="header-top">
                    <h1><i class="fas fa-tachometer-alt"></i> Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
                    <div class="header-actions">
                        <a href="#notifications" class="notification-bell" title="Notifications">
                            <i class="fa-solid fa-bell"></i>
                            <?php if($unreadNotifications > 0): ?>
                                <span class="notification-badge"></span>
                            <?php endif; ?>
                        </a>
                        <a href="download_stats.php" class="download-btn">
                            <i class="fa-solid fa-download"></i> Download Stats
                        </a>
                    </div>
                </div>
                <div class="welcome-text">
                    Here's a quick snapshot of your performance and workflow progress.
                </div>
            </div>

            <?php echo $flashMessage; ?>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" style="color:#000;"><?php echo $stats['total_jobs']; ?></div>
                    <div class="stat-label" style="color:#000;">Total Jobs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color:#000;"><?php echo $stats['pending_jobs']; ?></div>
                    <div class="stat-label" style="color:#000;">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color:#000;"><?php echo $stats['active_jobs']; ?></div>
                    <div class="stat-label" style="color:#000;">In Progress</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color:#000;"><?php echo $stats['completed_jobs']; ?></div>
                    <div class="stat-label" style="color:#000;">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color:#000;">ETB <?php echo number_format($stats['total_earnings'], 2); ?></div>
                    <div class="stat-label" style="color:#000;">Total Earnings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color:#000;"><?php echo $stats['average_rating']; ?> ★</div>
                    <div class="stat-label" style="color:#000;">Avg Rating (<?php echo $stats['rating_count']; ?>)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color:#000;"><?php echo $stats['unread_messages']; ?></div>
                    <div class="stat-label" style="color:#000;">Unread Messages</div>
                </div>
            </div>

            <!-- Detailed Insights -->
            <div class="card">
                <div class="section-header">
                    <h3><i class="fas fa-chart-pie"></i> Detailed Insights</h3>
                    <a href="download_stats.php" class="download-btn">
                        <i class="fa-solid fa-download"></i> Download Statistics
                    </a>
                </div>
                <div class="detail-stats-grid">
                    <div class="detail-block">
                        <h4><i class="fas fa-tools"></i> Jobs Overview</h4>
                        <ul>
                            <li><span>Total</span><span><?php echo $detailServices['total']; ?></span></li>
                            <li><span>Pending</span><span><?php echo $detailServices['pending']; ?></span></li>
                            <li><span>Active</span><span><?php echo $detailServices['active']; ?></span></li>
                            <li><span>Completed</span><span><?php echo $detailServices['completed']; ?></span></li>
                            <li><span>Cancelled</span><span><?php echo $detailServices['cancelled']; ?></span></li>
                            <li><span>Success Rate</span><span><?php echo $detailServices['success_rate']; ?>%</span></li>
                        </ul>
                    </div>
                    <div class="detail-block">
                        <h4><i class="fas fa-credit-card"></i> Payments</h4>
                        <ul>
                            <li><span>Total Payments</span><span><?php echo $detailPayments['total_payments']; ?></span></li>
                            <li><span>Pending Payments</span><span><?php echo $detailPayments['pending_payments']; ?></span></li>
                            <li><span>Verified Payments</span><span><?php echo $detailPayments['verified_payments']; ?></span></li>
                            <li><span>Total Earned</span><span>ETB <?php echo number_format($detailPayments['total_amount_earned'], 2); ?></span></li>
                        </ul>
                    </div>
                    <div class="detail-block">
                        <h4><i class="fas fa-comments"></i> Communication</h4>
                        <ul>
                            <li><span>Total Messages</span><span><?php echo $detailMessages['total']; ?></span></li>
                            <li><span>Unread Messages</span><span><?php echo $detailMessages['unread']; ?></span></li>
                            <li><span>Homeowners</span><span><?php echo $detailMessages['conversations']; ?></span></li>
                            <li><span>Response Rate</span><span><?php echo $detailMessages['response_rate'] ?? '100'; ?>%</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Workflow Guide -->
            <div class="highlight-card">
                <h3><i class="fas fa-sitemap"></i> How Your Workflow Progresses</h3>
                <p>Accept → Inspect → Quote → Work → Complete → Payment Verify. Use the sections below to jump to any step.</p>
            </div>

            <!-- Pending Requests -->
            <div class="card">
                <div class="section-header">
                    <h3><i class="fas fa-clock"></i> Requests Awaiting Your Response</h3>
                    <span style="color: var(--success); font-weight: 600;"><?php echo count($pendingRequests); ?> open</span>
                </div>
                <?php if(count($pendingRequests) > 0): ?>
                    <div class="flow-grid">
                        <?php foreach(array_slice($pendingRequests, 0, 4) as $request): $step = technician_next_step($request); ?>
                            <div class="flow-card">
                                <h4><?php echo htmlspecialchars($request['title']); ?></h4>
                                <div class="flow-meta">
                                    <span><strong>Service:</strong> <?php echo htmlspecialchars($request['service_type']); ?></span>
                                    <span><strong>Homeowner:</strong> <?php echo htmlspecialchars($request['homeowner_first_name'] . ' ' . $request['homeowner_last_name']); ?></span>
                                    <span><strong>Status:</strong> <span class="status-chip status-pending"><i class="fas fa-clock"></i> <?php echo str_replace('_',' ', $request['status']); ?></span></span>
                                </div>
                                <div class="flow-next-step">
                                    <strong><?php echo htmlspecialchars($step['label']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($step['hint']); ?></small>
                                </div>
                                <div class="flow-actions">
                                    <a class="btn btn-primary" href="my_tasks.php?focus=<?php echo (int)$request['id']; ?>">Open Task</a>
                                    <a class="btn btn-outline" href="messages.php?homeowner_id=<?php echo (int)$request['homeowner_id']; ?>">Message</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p>No pending requests. Great job staying up to date!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Active Jobs -->
            <div class="card">
                <div class="section-header">
                    <h3><i class="fas fa-tasks"></i> Active Jobs & Next Steps</h3>
                    <a href="my_tasks.php" class="btn btn-outline">Go to My Tasks</a>
                </div>
                <?php if(count($activeRequests) > 0): ?>
                    <div class="flow-grid">
                        <?php foreach($activeRequests as $request): $step = technician_next_step($request); ?>
                            <div class="flow-card">
                                <h4><?php echo htmlspecialchars($request['title']); ?></h4>
                                <div class="flow-meta">
                                    <span><strong>Service:</strong> <?php echo htmlspecialchars($request['service_type']); ?></span>
                                    <span><strong>Location:</strong> <?php echo htmlspecialchars(($request['homeowner_subcity'] ?? '').', '.($request['homeowner_woreda'] ?? '')); ?></span>
                                    <span><strong>Status:</strong> <span class="status-chip status-active"><i class="fas fa-spinner"></i> <?php echo str_replace('_',' ', $request['status']); ?></span></span>
                                </div>
                                <div class="flow-next-step">
                                    <strong><?php echo htmlspecialchars($step['label']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($step['hint']); ?></small>
                                </div>
                                <div class="flow-actions">
                                    <?php if($request['status'] === 'waiting_inspection'): ?>
                                        <a class="btn btn-primary" href="my_tasks.php?focus=<?php echo (int)$request['id']; ?>">Submit Inspection</a>
                                    <?php elseif(in_array($request['status'], ['price_accepted','in_progress','assigned'])): ?>
                                        <a class="btn btn-primary" href="my_tasks.php?focus=<?php echo (int)$request['id']; ?>">Update Progress</a>
                                    <?php else: ?>
                                        <a class="btn btn-outline" href="my_tasks.php?focus=<?php echo (int)$request['id']; ?>">View Details</a>
                                    <?php endif; ?>
                                    <a class="btn btn-outline" href="messages.php?homeowner_id=<?php echo (int)$request['homeowner_id']; ?>">Message</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-coffee"></i>
                        <p>No active jobs. Check pending requests or enjoy your downtime!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Payment Queue -->
            <div class="card">
                <div class="section-header">
                    <h3><i class="fas fa-money-bill-wave"></i> Payment Queue</h3>
                    <a href="payments.php" class="btn btn-outline">View Payments</a>
                </div>
                <?php if(count($paymentQueue) > 0): ?>
                    <div class="flow-grid">
                        <?php foreach($paymentQueue as $request): ?>
                            <div class="flow-card">
                                <h4><?php echo htmlspecialchars($request['title']); ?></h4>
                                <div class="flow-meta">
                                    <span><strong>Amount:</strong> ETB <?php echo number_format($request['payment_amount'] ?: $request['estimated_cost'] ?: 0, 2); ?></span>
                                    <span><strong>Homeowner:</strong> <?php echo htmlspecialchars($request['homeowner_first_name'] . ' ' . $request['homeowner_last_name']); ?></span>
                                    <span><strong>Status:</strong> <span class="status-chip status-payment"><i class="fas fa-hourglass-half"></i> Payment Requested</span></span>
                                </div>
                                <div class="flow-next-step">
                                    <strong>Waiting on payment proof</strong><br>
                                    <small>Once uploaded, confirm receipt in the payments page.</small>
                                </div>
                                <div class="flow-actions">
                                    <a class="btn btn-primary" href="payments.php?request_id=<?php echo (int)$request['id']; ?>">Open Payment</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p>No outstanding payments awaiting homeowner uploads.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Requests -->
            <div class="card">
                <div class="section-header">
                    <h3><i class="fas fa-history"></i> Latest Requests</h3>
                </div>
                <?php if(count($recentRequests) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Service</th>
                                <th>Homeowner</th>
                                <th>Status</th>
                                <th>Requested</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentRequests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['title']); ?></td>
                                    <td><?php echo htmlspecialchars($request['service_type']); ?></td>
                                    <td><?php echo htmlspecialchars($request['homeowner_first_name'] . ' ' . $request['homeowner_last_name']); ?></td>
                                    <td>
                                        <?php 
                                            $statusClass = 'status-' . strtolower(str_replace('_', '-', $request['status']));
                                            $statusIcon = 'fas fa-info-circle';
                                            if($request['status'] === 'completed') $statusIcon = 'fas fa-check-circle';
                                            elseif($request['status'] === 'paid') $statusIcon = 'fas fa-money-bill-wave';
                                            elseif(in_array($request['status'], ['pending', 'waiting_acceptance'])) $statusIcon = 'fas fa-clock';
                                        ?>
                                        <span class="status-chip <?php echo $statusClass; ?>"><i class="<?php echo $statusIcon; ?>"></i> <?php echo str_replace('_',' ', $request['status']); ?></span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($request['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No requests yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Completed Tasks -->
            <div class="card">
                <div class="section-header">
                    <h3><i class="fas fa-trophy"></i> Recently Completed Tasks</h3>
                    <span style="color: var(--success); font-weight: 600;"><?php echo count($completedRequests); ?> total</span>
                </div>
                <?php if(count($completedRequests) > 0): ?>
                    <div class="timeline">
                        <?php foreach(array_slice($completedRequests, 0, 5) as $request): ?>
                            <div class="timeline-item">
                                <strong><?php echo htmlspecialchars($request['title']); ?></strong>
                                <p>
                                    Finished on <?php echo $request['work_completed_at'] ? date('M j, Y', strtotime($request['work_completed_at'])) : date('M j, Y', strtotime($request['updated_at'])); ?> · <?php echo htmlspecialchars($request['service_type']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No completed tasks yet. Start accepting requests to build your portfolio!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Notifications -->
            <div class="card notification-card" id="notifications">
                <div class="section-header">
                    <h3><i class="fas fa-bell"></i> Notifications</h3>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to clear ALL notifications? This cannot be undone.');">
                        <input type="hidden" name="action" value="clear_notifications">
                        <button type="submit" class="clear-btn">
                            <i class="fas fa-trash-alt"></i> Clear Notifications
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <?php if(!empty($notifications)): ?>
                        <?php foreach($notifications as $notification): ?>
                        <div class="notification-item">
                            <div class="notification-icon"><i class="fa-solid fa-bell"></i></div>
                            <div class="notification-meta">
                                <strong><?php echo htmlspecialchars($notification['title']); ?></strong>
                                <div><?php echo htmlspecialchars($notification['message']); ?></div>
                                <small><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <p>No notifications yet. You're all caught up!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 100);
            });

            // Add click animation to buttons
            document.querySelectorAll('.btn, .download-btn, .clear-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.3);
                        transform: scale(0);
                        animation: ripple 0.6s ease-out;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        pointer-events: none;
                        z-index: 0;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
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

            // Auto-refresh notifications badge
            function updateNotificationBadge() {
                // In a real app, you would fetch from an API endpoint
                // For now, we'll just simulate the update
                const badge = document.querySelector('.notification-badge');
                if(badge) {
                    const count = parseInt(badge.textContent);
                    if(count > 0) {
                        badge.style.animation = 'none';
                        setTimeout(() => {
                            badge.style.animation = 'pulse 2s infinite';
                        }, 10);
                    }
                }
            }

            // Update badge every 30 seconds
            setInterval(updateNotificationBadge, 30000);

            // Smooth scroll to notifications
            document.querySelector('.notification-bell').addEventListener('click', function(e) {
                const target = document.getElementById('notifications');
                if(target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    
                    // Highlight the notifications section
                    target.style.boxShadow = '0 0 0 3px rgba(76, 201, 240, 0.3)';
                    setTimeout(() => {
                        target.style.boxShadow = '';
                    }, 2000);
                }
            });

            // Add hover effect to timeline items
            document.querySelectorAll('.timeline-item').forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(10px)';
                    this.style.transition = 'transform 0.3s ease';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });

            // Status chip animations
            document.querySelectorAll('.status-chip').forEach(chip => {
                chip.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                    this.style.transition = 'transform 0.2s ease';
                });
                
                chip.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>