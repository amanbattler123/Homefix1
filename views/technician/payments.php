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
$totalEarnings = 0;
$pendingPayments = 0;
$verifiedPayments = 0;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_payment') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $confirmed = $technicianController->confirmPaymentReceived($requestId);
    if($confirmed) {
        $flashMessage = '<div class="alert alert-success" data-aos="fade-down"><i class="fas fa-check-circle me-2"></i> Payment confirmed successfully! Sent to admin for final verification.</div>';
    } else {
        $flashMessage = '<div class="alert alert-danger" data-aos="fade-down"><i class="fas fa-exclamation-circle me-2"></i> Unable to confirm this payment. Please refresh and try again.</div>';
    }
}

$payments = $technicianController->getPayments();
// Load unread notifications for header bell
$unreadNotifications = $technicianController->getUnreadNotificationCount();

// Calculate statistics
foreach($payments as $payment) {
    if($payment['payment_status'] === 'verified') {
        $totalEarnings += $payment['amount'];
        $verifiedPayments++;
    } elseif($payment['payment_status'] === 'paid') {
        $pendingPayments++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - HomeFix Pro</title>
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
            --success: #06d6a0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --dark: #1e1e2c;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            --gradient-secondary: linear-gradient(135deg, #7209b7 0%, #3a0ca3 100%);
            --gradient-success: linear-gradient(135deg, #06d6a0 0%, #4361ee 100%);
            --gradient-warning: linear-gradient(135deg, #f8961e 0%, #f3722c 100%);
            --gradient-danger: linear-gradient(135deg, #f72585 0%, #b5179e 100%);
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
            top: 10px;
            right: 10px;
            background: var(--danger);
            width: 10px;
            height: 10px;
            border-radius: 50%;
            font-size: 0;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }

        .stat-card:nth-child(2) {
            border-left-color: var(--warning);
        }

        .stat-card:nth-child(3) {
            border-left-color: var(--success);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            background: var(--gradient-primary);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: var(--gradient-warning);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: var(--gradient-success);
        }

        .stat-info {
            flex: 1;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #000;
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #000;
            font-weight: 500;
        }

        /* Card Styles */
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
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-title i {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-subtitle {
            color: var(--gray);
            font-size: 16px;
        }

        /* Alert Styles */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(6, 214, 160, 0.15);
            color: var(--dark);
            border-left: 4px solid var(--success);
        }

        .alert-danger {
            background: rgba(247, 37, 133, 0.15);
            color: var(--dark);
            border-left: 4px solid var(--danger);
        }

        .alert-warning {
            background: rgba(248, 150, 30, 0.15);
            color: var(--dark);
            border-left: 4px solid var(--warning);
        }

        .alert-info {
            background: rgba(73, 149, 239, 0.15);
            color: var(--dark);
            border-left: 4px solid var(--info);
        }

        .alert i {
            font-size: 18px;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            border-radius: var(--radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        .data-table thead {
            background: var(--gradient-primary);
        }

        .data-table th {
            color: white;
            font-weight: 600;
            text-align: left;
            padding: 18px 20px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--gray-light);
            transition: var(--transition);
        }

        .data-table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
            transform: translateY(-2px);
        }

        .data-table td {
            padding: 20px;
            font-size: 14px;
            color: var(--dark);
            vertical-align: top;
        }

        /* Payment Card View */
        .payment-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .payment-card {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-top: 4px solid var(--primary);
        }

        .payment-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .payment-card.pending {
            border-top-color: var(--warning);
        }

        .payment-card.paid {
            border-top-color: var(--info);
        }

        .payment-card.verified {
            border-top-color: var(--success);
        }

        .payment-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .payment-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .payment-service {
            font-size: 13px;
            color: var(--primary);
            font-weight: 500;
            background: rgba(67, 97, 238, 0.1);
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: rgba(248, 150, 30, 0.15);
            color: #f3722c;
        }

        .status-paid {
            background: rgba(73, 149, 239, 0.15);
            color: #4895ef;
        }

        .status-verified {
            background: rgba(6, 214, 160, 0.15);
            color: #06d6a0;
        }

        .payment-details {
            margin-bottom: 20px;
        }

        .payment-detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .payment-detail-item i {
            color: var(--gray);
            width: 20px;
        }

        .payment-amount {
            font-size: 24px;
            font-weight: 700;
            color: var(--success);
            text-align: center;
            margin: 15px 0;
            padding: 15px;
            background: rgba(6, 214, 160, 0.1);
            border-radius: var(--radius);
        }

        .payment-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 24px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow);
            text-decoration: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        .btn-success {
            background: var(--gradient-success);
            border: none;
            color: white;
        }

        .btn-warning {
            background: var(--gradient-warning);
            border: none;
            color: white;
        }

        .btn-info {
            background: var(--gradient-primary);
            border: none;
            color: white;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }

        .btn-block {
            width: 100%;
        }

        /* Tab Navigation */
        .tab-navigation {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--gray-light);
            padding-bottom: 10px;
        }

        .tab-btn {
            padding: 12px 24px;
            background: transparent;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 14px;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .tab-btn:hover {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.05);
        }

        .tab-btn.active {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--gradient-primary);
            border-radius: 3px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 64px;
            color: var(--gray-light);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--gray);
            max-width: 400px;
            margin: 0 auto 30px;
        }

        /* Payment Timeline */
        .timeline {
            position: relative;
            padding-left: 30px;
            margin: 15px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--gray-light);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 15px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--gray-light);
        }

        .timeline-item.completed::before {
            background: var(--success);
        }

        .timeline-item.current::before {
            background: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .timeline-item.pending::before {
            background: var(--warning);
        }

        .timeline-date {
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 4px;
        }

        .timeline-text {
            font-size: 13px;
            color: var(--dark);
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
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .payment-cards {
                grid-template-columns: 1fr;
            }
            
            .tab-navigation {
                flex-direction: column;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .stats-container {
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
            
            .data-table {
                min-width: 800px;
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

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 30px;
            left: 30px;
            background: white;
            border-radius: var(--radius);
            padding: 16px 20px;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 400px;
            z-index: 1000;
            transform: translateY(100px);
            opacity: 0;
            transition: var(--transition);
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast.success {
            border-left: 4px solid var(--success);
        }

        .toast.error {
            border-left: 4px solid var(--danger);
        }

        .toast-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
        }

        .toast.success .toast-icon {
            background: var(--success);
        }

        .toast.error .toast-icon {
            background: var(--danger);
        }

        .toast-message {
            flex: 1;
            font-weight: 500;
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 16px;
            transition: var(--transition);
        }

        .toast-close:hover {
            color: var(--dark);
        }

        /* Loading Spinner */
        .spinner {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 2px solid white;
            width: 18px;
            height: 18px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Utility Classes */
        .d-none {
            display: none !important;
        }

        .text-muted {
            color: var(--gray);
        }

        .text-center {
            text-align: center;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 1rem; }
        .mt-4 { margin-top: 1.5rem; }
        .mt-5 { margin-top: 3rem; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .mb-5 { margin-bottom: 3rem; }
        .me-1 { margin-right: 0.25rem; }
        .me-2 { margin-right: 0.5rem; }
        .me-3 { margin-right: 1rem; }
        .me-4 { margin-right: 1.5rem; }
        .me-5 { margin-right: 3rem; }
        .ms-1 { margin-left: 0.25rem; }
        .ms-2 { margin-left: 0.5rem; }
        .ms-3 { margin-left: 1rem; }
        .ms-4 { margin-left: 1.5rem; }
        .ms-5 { margin-left: 3rem; }

        /* Homeowner Info */
        .homeowner-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .homeowner-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .homeowner-details {
            flex: 1;
        }

        .homeowner-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--dark);
        }

        .homeowner-phone {
            font-size: 12px;
            color: var(--gray);
        }

        /* Receipt Preview */
        .receipt-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: var(--light);
            border-radius: var(--radius);
            margin-top: 10px;
            border: 1px solid var(--gray-light);
        }

        .receipt-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .receipt-info {
            flex: 1;
        }

        .receipt-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--dark);
        }

        .receipt-actions {
            display: flex;
            gap: 5px;
        }
    </style>
</head>
<body class="technician-body">
    <div class="dashboard">
        <!-- Include the sidebar component -->
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <!-- Header -->
            <div class="dashboard-header">
                <div class="welcome-section">
                    <h1>Payments</h1>
                    <p>Track payouts for your completed services and manage payment confirmations</p>
                </div>
                <div class="header-actions">
                    <div class="notification-bell">
                        <i class="fa-solid fa-bell"></i>
                        <?php if(!empty($unreadNotifications)): ?>
                            <span class="notification-badge"></span>
                        <?php endif; ?>
                    </div>
                    <a href="my_tasks.php" class="btn-primary">
                        <i class="fa-solid fa-tasks"></i>
                        My Tasks
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container" data-aos="fade-up">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="total-earnings" style="color:#000;">ETB <?php echo number_format($totalEarnings, 2); ?></div>
                        <div class="stat-label" style="color:#000;">Total Earnings</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="pending-count" style="color:#000;">&nbsp;<?php echo $pendingPayments; ?></div>
                        <div class="stat-label" style="color:#000;">Pending Confirmation</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="verified-count" style="color:#000;"><?php echo $verifiedPayments; ?></div>
                        <div class="stat-label" style="color:#000;">Verified Payments</div>
                    </div>
                </div>
            </div>

            <!-- Messages Container -->
            <div id="message-container">
                <?php 
                // Display messages if they exist
                if (!empty($flashMessage)) {
                    echo $flashMessage;
                }
                ?>
            </div>

            <!-- Tab Navigation -->
            <div class="tab-navigation" data-aos="fade-up" data-aos-delay="100">
                <button class="tab-btn active" onclick="switchTab('all')" id="tab-all">
                    <i class="fas fa-list me-2"></i> All Payments
                    <span class="badge"><?php echo count($payments); ?></span>
                </button>
                <button class="tab-btn" onclick="switchTab('pending')" id="tab-pending">
                    <i class="fas fa-clock me-2"></i> Pending Confirmation
                    <span class="badge"><?php echo $pendingPayments; ?></span>
                </button>
                <button class="tab-btn" onclick="switchTab('verified')" id="tab-verified">
                    <i class="fas fa-check-circle me-2"></i> Verified
                    <span class="badge"><?php echo $verifiedPayments; ?></span>
                </button>
            </div>

            <!-- All Payments Section -->
            <div class="tab-content active" id="all-tab" data-aos="fade-up" data-aos-delay="200">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title"><i class="fas fa-money-check-alt"></i> Payment History</h2>
                            <p class="card-subtitle">All your payment records and earnings</p>
                        </div>
                    </div>

                    <?php if(count($payments) > 0): ?>
                        <!-- Payment Cards View -->
                        <div class="payment-cards">
                            <?php foreach($payments as $index => $payment): ?>
                                <?php
                                    $needsConfirmation = ($payment['payment_status'] === 'paid' && empty($payment['technician_confirmed_at']));
                                    $receiptUrl = !empty($payment['payment_proof']) ? SITE_URL . '/assets/uploads/payments/' . rawurlencode($payment['payment_proof']) : null;
                                    $statusClass = $payment['payment_status'];
                                ?>
                                <div class="payment-card <?php echo $statusClass; ?>" 
                                     data-status="<?php echo $statusClass; ?>"
                                     data-aos="fade-up" 
                                     data-aos-delay="<?php echo ($index * 100) + 300; ?>">
                                    <div class="payment-card-header">
                                        <div>
                                            <div class="payment-title"><?php echo htmlspecialchars($payment['title']); ?></div>
                                            <span class="payment-service"><?php echo htmlspecialchars($payment['service_type']); ?></span>
                                        </div>
                                        <span class="status-badge status-<?php echo $statusClass; ?>">
                                            <?php echo ucfirst($payment['payment_status']); ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Payment Amount -->
                                    <div class="payment-amount">
                                        ETB <?php echo number_format($payment['amount'], 2); ?>
                                    </div>
                                    
                                    <!-- Payment Details -->
                                    <div class="payment-details">
                                        <div class="payment-detail-item">
                                            <i class="fas fa-user"></i>
                                            <div class="homeowner-info">
                                                <div class="homeowner-avatar">
                                                    <?php 
                                                    $initials = '';
                                                    if(!empty($payment['homeowner_first_name'])) {
                                                        $initials .= strtoupper(substr($payment['homeowner_first_name'], 0, 1));
                                                    }
                                                    if(!empty($payment['homeowner_last_name'])) {
                                                        $initials .= strtoupper(substr($payment['homeowner_last_name'], 0, 1));
                                                    }
                                                    echo $initials ?: '?';
                                                    ?>
                                                </div>
                                                <div class="homeowner-details">
                                                    <div class="homeowner-name">
                                                        <?php echo htmlspecialchars($payment['homeowner_first_name'] . ' ' . $payment['homeowner_last_name']); ?>
                                                    </div>
                                                    <div class="homeowner-phone">
                                                        <?php echo htmlspecialchars($payment['homeowner_phone'] ?? 'No phone'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="payment-detail-item">
                                            <i class="fas fa-credit-card"></i>
                                            <span>
                                                <strong>Method:</strong> 
                                                <?php echo $payment['payment_method'] ? ucfirst(str_replace('_', ' ', $payment['payment_method'])) : 'Pending'; ?>
                                            </span>
                                        </div>
                                        
                                        <?php if(!empty($payment['transaction_id'])): ?>
                                        <div class="payment-detail-item">
                                            <i class="fas fa-receipt"></i>
                                            <span>
                                                <strong>Transaction ID:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Payment Timeline -->
                                        <div class="timeline">
                                            <?php if($payment['work_completed_at']): ?>
                                            <div class="timeline-item completed">
                                                <div class="timeline-date">
                                                    <?php echo date('M j, Y', strtotime($payment['work_completed_at'])); ?>
                                                </div>
                                                <div class="timeline-text">Work Completed</div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if($payment['payment_requested_at']): ?>
                                            <div class="timeline-item completed">
                                                <div class="timeline-date">
                                                    <?php echo date('M j, Y', strtotime($payment['payment_requested_at'])); ?>
                                                </div>
                                                <div class="timeline-text">Payment Requested</div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if($payment['paid_at']): ?>
                                            <div class="timeline-item <?php echo $payment['payment_status'] === 'paid' ? 'current' : 'completed'; ?>">
                                                <div class="timeline-date">
                                                    <?php echo date('M j, Y', strtotime($payment['paid_at'])); ?>
                                                </div>
                                                <div class="timeline-text">
                                                    Paid by Homeowner
                                                    <?php if($payment['paid_at']): ?>
                                                        <div class="text-muted"><?php echo date('g:i A', strtotime($payment['paid_at'])); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if($payment['technician_confirmed_at']): ?>
                                            <div class="timeline-item completed">
                                                <div class="timeline-date">
                                                    <?php echo date('M j, Y', strtotime($payment['technician_confirmed_at'])); ?>
                                                </div>
                                                <div class="timeline-text">
                                                    Confirmed by You
                                                    <?php if($payment['technician_confirmed_at']): ?>
                                                        <div class="text-muted"><?php echo date('g:i A', strtotime($payment['technician_confirmed_at'])); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if($payment['verified_at']): ?>
                                            <div class="timeline-item completed">
                                                <div class="timeline-date">
                                                    <?php echo date('M j, Y', strtotime($payment['verified_at'])); ?>
                                                </div>
                                                <div class="timeline-text">
                                                    Verified by Admin
                                                    <?php if($payment['verified_at']): ?>
                                                        <div class="text-muted"><?php echo date('g:i A', strtotime($payment['verified_at'])); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Receipt Preview -->
                                        <?php if($receiptUrl): ?>
                                        <div class="receipt-preview">
                                            <div class="receipt-icon">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                            </div>
                                            <div class="receipt-info">
                                                <div class="receipt-name">Payment Receipt</div>
                                                <div class="text-muted small">
                                                    <?php echo !empty($payment['payment_proof']) ? basename($payment['payment_proof']) : 'Receipt'; ?>
                                                </div>
                                            </div>
                                            <div class="receipt-actions">
                                                <a href="<?php echo htmlspecialchars($receiptUrl); ?>" target="_blank" class="btn btn-outline btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo htmlspecialchars($receiptUrl); ?>" download class="btn btn-outline btn-sm">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="payment-actions">
                                        <a href="request_details.php?id=<?php echo (int)$payment['service_request_id']; ?>" class="btn btn-outline btn-sm" target="_blank">
                                            <i class="fas fa-eye me-1"></i> View Details
                                        </a>
                                        
                                        <?php if($needsConfirmation): ?>
                                            <form method="POST" class="payment-confirm-form">
                                                <input type="hidden" name="action" value="confirm_payment">
                                                <input type="hidden" name="request_id" value="<?php echo (int)$payment['service_request_id']; ?>">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check-circle me-1"></i> Confirm Payment
                                                </button>
                                            </form>
                                        <?php elseif($payment['payment_status'] === 'pending'): ?>
                                            <button class="btn btn-outline btn-sm" disabled>
                                                <i class="fas fa-clock me-1"></i> Awaiting Payment
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-outline btn-sm" disabled>
                                                <i class="fas fa-check me-1"></i> Completed
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" data-aos="fade-up" data-aos-delay="300">
                            <i class="fas fa-money-bill-wave"></i>
                            <h3>No Payments Yet</h3>
                            <p>You don't have any payment records yet. Once you complete tasks and homeowners make payments, they'll appear here.</p>
                            <a href="my_tasks.php" class="btn btn-primary">
                                <i class="fas fa-tasks me-2"></i> View My Tasks
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Confirmation Tab -->
            <div class="tab-content d-none" id="pending-tab">
                <div class="card" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title"><i class="fas fa-clock"></i> Pending Confirmation</h2>
                            <p class="card-subtitle">Payments awaiting your confirmation</p>
                        </div>
                    </div>

                    <?php 
                    $pendingPaymentsList = array_filter($payments, function($p) {
                        return $p['payment_status'] === 'paid' && empty($p['technician_confirmed_at']);
                    });
                    ?>
                    
                    <?php if(count($pendingPaymentsList) > 0): ?>
                        <div class="payment-cards">
                            <?php foreach($pendingPaymentsList as $index => $payment): ?>
                                <?php $receiptUrl = !empty($payment['payment_proof']) ? SITE_URL . '/assets/uploads/payments/' . rawurlencode($payment['payment_proof']) : null; ?>
                                <div class="payment-card paid" data-aos="fade-up" data-aos-delay="<?php echo ($index * 100) + 300; ?>">
                                    <div class="payment-card-header">
                                        <div>
                                            <div class="payment-title"><?php echo htmlspecialchars($payment['title']); ?></div>
                                            <span class="payment-service"><?php echo htmlspecialchars($payment['service_type']); ?></span>
                                        </div>
                                        <span class="status-badge status-paid">
                                            Paid - Needs Confirmation
                                        </span>
                                    </div>
                                    
                                    <div class="payment-amount">
                                        ETB <?php echo number_format($payment['amount'], 2); ?>
                                    </div>
                                    
                                    <div class="payment-details">
                                        <div class="payment-detail-item">
                                            <i class="fas fa-user"></i>
                                            <span>
                                                <strong>Homeowner:</strong> 
                                                <?php echo htmlspecialchars($payment['homeowner_first_name'] . ' ' . $payment['homeowner_last_name']); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="payment-detail-item">
                                            <i class="fas fa-calendar"></i>
                                            <span>
                                                <strong>Paid on:</strong> 
                                                <?php echo $payment['paid_at'] ? date('M j, Y g:i A', strtotime($payment['paid_at'])) : 'N/A'; ?>
                                            </span>
                                        </div>
                                        
                                        <?php if($receiptUrl): ?>
                                        <div class="receipt-preview">
                                            <div class="receipt-icon">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                            </div>
                                            <div class="receipt-info">
                                                <div class="receipt-name">Payment Receipt</div>
                                                <div class="text-muted small">Click to view proof of payment</div>
                                            </div>
                                            <div class="receipt-actions">
                                                <a href="<?php echo htmlspecialchars($receiptUrl); ?>" target="_blank" class="btn btn-outline btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            Please verify that you have received this payment before confirming.
                                        </div>
                                    </div>
                                    
                                    <div class="payment-actions">
                                        <form method="POST" class="payment-confirm-form">
                                            <input type="hidden" name="action" value="confirm_payment">
                                            <input type="hidden" name="request_id" value="<?php echo (int)$payment['service_request_id']; ?>">
                                            <button type="submit" class="btn btn-success btn-block">
                                                <i class="fas fa-check-circle me-2"></i> Confirm Payment Received
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" data-aos="fade-up" data-aos-delay="300">
                            <i class="fas fa-check-circle"></i>
                            <h3>All Caught Up!</h3>
                            <p>You have no payments pending confirmation. All your received payments have been confirmed.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Verified Payments Tab -->
            <div class="tab-content d-none" id="verified-tab">
                <div class="card" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title"><i class="fas fa-check-circle"></i> Verified Payments</h2>
                            <p class="card-subtitle">Payments that have been verified and completed</p>
                        </div>
                    </div>

                    <?php 
                    $verifiedPaymentsList = array_filter($payments, function($p) {
                        return $p['payment_status'] === 'verified';
                    });
                    ?>
                    
                    <?php if(count($verifiedPaymentsList) > 0): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Service Request</th>
                                        <th>Homeowner</th>
                                        <th>Amount</th>
                                        <th>Completed Date</th>
                                        <th>Verified Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($verifiedPaymentsList as $index => $payment): ?>
                                        <tr data-aos="fade-up" data-aos-delay="<?php echo ($index * 100) + 300; ?>">
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($payment['title']); ?></strong>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($payment['service_type']); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="homeowner-info">
                                                    <div class="homeowner-avatar">
                                                        <?php 
                                                        $initials = '';
                                                        if(!empty($payment['homeowner_first_name'])) {
                                                            $initials .= strtoupper(substr($payment['homeowner_first_name'], 0, 1));
                                                        }
                                                        if(!empty($payment['homeowner_last_name'])) {
                                                            $initials .= strtoupper(substr($payment['homeowner_last_name'], 0, 1));
                                                        }
                                                        echo $initials ?: '?';
                                                        ?>
                                                    </div>
                                                    <div class="homeowner-details">
                                                        <div class="homeowner-name">
                                                            <?php echo htmlspecialchars($payment['homeowner_first_name'] . ' ' . $payment['homeowner_last_name']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>ETB <?php echo number_format($payment['amount'], 2); ?></strong>
                                                <div class="text-muted small">
                                                    <?php echo $payment['payment_method'] ? ucfirst(str_replace('_', ' ', $payment['payment_method'])) : ''; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo $payment['work_completed_at'] ? date('M j, Y', strtotime($payment['work_completed_at'])) : 'N/A'; ?>
                                            </td>
                                            <td>
                                                <?php echo $payment['verified_at'] ? date('M j, Y', strtotime($payment['verified_at'])) : 'N/A'; ?>
                                                <?php if($payment['verified_at']): ?>
                                                    <div class="text-muted small"><?php echo date('g:i A', strtotime($payment['verified_at'])); ?></div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" data-aos="fade-up" data-aos-delay="300">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <h3>No Verified Payments</h3>
                            <p>You don't have any verified payments yet. Once payments are confirmed by you and verified by admin, they'll appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <div class="toast-icon">
            <i class="fas fa-check"></i>
        </div>
        <div class="toast-message" id="toast-message">Action completed successfully!</div>
        <button class="toast-close" id="toast-close">
            <i class="fas fa-times"></i>
        </button>
    </div>

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

        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
            });
        }

        // Tab switching
        function switchTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById('tab-' + tabName).classList.add('active');
            
            // Update tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('d-none');
                content.classList.remove('active');
            });
            document.getElementById(tabName + '-tab').classList.remove('d-none');
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Update URL hash
            window.location.hash = tabName;
        }

        // Check URL hash on load
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash.substring(1);
            if (hash && ['all', 'pending', 'verified'].includes(hash)) {
                switchTab(hash);
            }
            
            // Add fade-in animation for existing alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach((alert, index) => {
                alert.style.animation = `fadeIn 0.5s ease ${index * 0.1}s forwards`;
            });
            
            // Add confirmation dialog for payment confirmation
            document.querySelectorAll('.payment-confirm-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (!confirm('Are you sure you have received this payment? This will notify the admin for final verification.')) {
                        return;
                    }
                    
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<div class="spinner me-2"></div> Confirming...';
                        submitBtn.disabled = true;
                        
                        // Submit form after delay for visual feedback
                        setTimeout(() => {
                            this.submit();
                        }, 500);
                    } else {
                        this.submit();
                    }
                });
            });
            
            // Add animation for payment cards on hover
            const paymentCards = document.querySelectorAll('.payment-card');
            paymentCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Animate stats counters
            animateStats();
        });

        // Animate stats counters
        function animateStats() {
            const totalEarnings = parseFloat('<?php echo $totalEarnings; ?>') || 0;
            const pendingCount = <?php echo $pendingPayments; ?>;
            const verifiedCount = <?php echo $verifiedPayments; ?>;
            
            // Animate total earnings
            const earningsElement = document.getElementById('total-earnings');
            if (earningsElement && totalEarnings > 0) {
                animateCurrencyValue(earningsElement, 0, totalEarnings, 2000);
            }
            
            // Animate pending count
            const pendingElement = document.getElementById('pending-count');
            if (pendingElement) {
                animateValue(pendingElement, 0, pendingCount, 1000);
            }
            
            // Animate verified count
            const verifiedElement = document.getElementById('verified-count');
            if (verifiedElement) {
                animateValue(verifiedElement, 0, verifiedCount, 1000);
            }
        }

        // Animate currency value
        function animateCurrencyValue(element, start, end, duration) {
            const startValue = start;
            const endValue = end;
            const range = endValue - startValue;
            const increment = range / (duration / 16); // 60fps
            let current = startValue;
            
            const timer = setInterval(() => {
                current += increment;
                if ((increment > 0 && current >= endValue) || (increment < 0 && current <= endValue)) {
                    current = endValue;
                    clearInterval(timer);
                }
                element.textContent = 'ETB ' + current.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }, 16);
        }

        // Animate number counting
        function animateValue(element, start, end, duration) {
            if (start === end) return;
            
            const range = end - start;
            const increment = end > start ? 1 : -1;
            const stepTime = Math.abs(Math.floor(duration / range));
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                element.textContent = current;
                if (current === end) {
                    clearInterval(timer);
                }
            }, stepTime);
        }

        // Toast notification function
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            const toastIcon = toast.querySelector('.toast-icon i');
            
            toastMessage.textContent = message;
            toast.className = 'toast ' + type;
            toastIcon.className = type === 'success' ? 'fas fa-check' : 'fas fa-exclamation-triangle';
            
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 5000);
        }

        // Close toast
        document.getElementById('toast-close').addEventListener('click', function() {
            document.getElementById('toast').classList.remove('show');
        });

        // Add custom animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            .badge {
                display: inline-block;
                background: var(--primary);
                color: white;
                font-size: 11px;
                padding: 2px 6px;
                border-radius: 10px;
                margin-left: 5px;
                vertical-align: middle;
            }
            
            .payment-card[data-aos].aos-animate {
                animation: slideUp 0.6s ease forwards;
            }
            
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .pulse {
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>