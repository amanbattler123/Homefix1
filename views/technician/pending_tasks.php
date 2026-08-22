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

$message = '';
$pendingCount = 0;

// Load live request lists for accurate counts
$pendingRequests = $technicianController->getPendingRequests();
$activeRequestsForStats = $technicianController->getActiveRequests();
$completedRequestsForStats = $technicianController->getCompletedRequests();

$pendingCount = count($pendingRequests);

$activeCountForStats = count($activeRequestsForStats);
$completedCountForStats = count($completedRequestsForStats);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if(isset($_POST['request_id']) && in_array($_POST['action'], ['accept','reject'])) {
        $requestId = (int)$_POST['request_id'];
        if($_POST['action'] === 'accept') {
            $success = $technicianController->acceptRequest($requestId);
            $message = $success
                ? '<div class="alert alert-success" data-aos="fade-down"><i class="fas fa-check-circle me-2"></i> Task accepted successfully! You can now proceed to inspection.</div>'
                : '<div class="alert alert-danger" data-aos="fade-down"><i class="fas fa-exclamation-circle me-2"></i> Unable to accept this task. It might have been updated already.</div>';
        } elseif($_POST['action'] === 'reject') {
            $reason = trim($_POST['reason'] ?? '');
            if(empty($reason)) {
                $message = '<div class="alert alert-warning" data-aos="fade-down"><i class="fas fa-exclamation-triangle me-2"></i> Please provide a reason for rejection.</div>';
            } else {
                $success = $technicianController->rejectRequest($requestId, $reason);
                $message = $success
                    ? '<div class="alert alert-info" data-aos="fade-down"><i class="fas fa-info-circle me-2"></i> Task rejected. The homeowner has been notified.</div>'
                    : '<div class="alert alert-danger" data-aos="fade-down"><i class="fas fa-exclamation-circle me-2"></i> Unable to reject this task. Please try again.</div>';
            }
        }
    } elseif($_POST['action'] === 'clear_pending') {
        $success = $technicianController->clearPendingTasks();
        $message = $success
            ? '<div class="alert alert-info" data-aos="fade-down"><i class="fas fa-check-circle me-2"></i> All pending tasks have been cleared.</div>'
            : '<div class="alert alert-danger" data-aos="fade-down"><i class="fas fa-exclamation-circle me-2"></i> Unable to clear pending tasks. Please try again.</div>';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Tasks - HomeFix Pro</title>
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
            min-width: 800px;
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
        }

        .data-table td:first-child {
            font-weight: 600;
        }

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

        .status-accepted {
            background: rgba(6, 214, 160, 0.15);
            color: #06d6a0;
        }

        .status-rejected {
            background: rgba(247, 37, 133, 0.15);
            color: #f72585;
        }

        .status-completed {
            background: rgba(67, 97, 238, 0.15);
            color: #4361ee;
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

        .btn-danger {
            background: var(--gradient-danger);
            border: none;
            color: white;
        }

        .btn-warning {
            background: var(--gradient-warning);
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

        /* Action Forms */
        .action-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-width: 200px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-buttons .btn {
            flex: 1;
        }

        /* Textarea Styles */
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-light);
            border-radius: var(--radius);
            font-size: 14px;
            transition: var(--transition);
            background-color: white;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-control::placeholder {
            color: #adb5bd;
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius-lg);
            padding: 40px;
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow-lg);
            animation: modalSlideIn 0.3s ease;
        }

        .modal-header {
            margin-bottom: 20px;
            text-align: center;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .modal-body {
            margin-bottom: 30px;
        }

        .modal-footer {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            
            .action-buttons {
                flex-direction: column;
            }
            
            .data-table {
                min-width: 600px;
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

        .mt-4 {
            margin-top: 1.5rem;
        }

        .me-2 {
            margin-right: 0.5rem;
        }

        .small {
            font-size: 12px;
            color: var(--gray);
        }

        .homeowner-info {
            display: flex;
            align-items: center;
            gap: 10px;
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
                    <h1>Pending Tasks</h1>
                    <p>Review service requests awaiting your acceptance or action</p>
                </div>
                <div class="header-actions">
                    <div class="notification-bell">
                        <i class="fa-solid fa-bell"></i>
                        <?php $unreadNotifications = $technicianController->getUnreadNotificationCount(); ?>
                        <?php if(!empty($unreadNotifications)): ?>
                            <span class="notification-badge"></span>
                        <?php endif; ?>
                    </div>
                    <a href="dashboard.php" class="btn-primary">
                        <i class="fa-solid fa-home"></i>
                        Dashboard
                    </a>
                </div>
            </div>

            <!-- Messages Container -->
            <div id="message-container">
                <?php 
                // Display messages if they exist
                if (!empty($message)) {
                    echo $message;
                }
                ?>
            </div>

            <!-- Pending Tasks Card -->
            <div class="card" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header">
                    <div>
                        <h2 class="card-title"><i class="fas fa-tasks"></i> Pending Requests</h2>
                        <p class="card-subtitle">Accept or reject incoming service requests</p>
                    </div>
                    <?php if($pendingCount > 0): ?>
                    <form method="POST" id="clear-pending-form">
                        <input type="hidden" name="action" value="clear_pending">
                        <button type="button" class="btn btn-danger" onclick="showClearConfirmation()">
                            <i class="fas fa-trash-alt me-2"></i> Clear All Pending
                        </button>
                    </form>
                    <?php endif; ?>
                </div>

                <?php if($pendingCount > 0): ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Service Request</th>
                                    <th>Homeowner</th>
                                    <th>Location</th>
                                    <th>Preferred Date</th>
                                    <th>Urgency</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pendingRequests as $index => $request): ?>
                                    <tr data-aos="fade-up" data-aos-delay="<?php echo ($index * 100) + 300; ?>">
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($request['title']); ?></strong>
                                                <div class="small mt-1"><?php echo htmlspecialchars($request['service_type']); ?></div>
                                                <div class="small text-muted">Requested: <?php echo date('M j, Y', strtotime($request['created_at'])); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="homeowner-info">
                                                <div class="homeowner-avatar">
                                                    <?php 
                                                    $initials = '';
                                                    if(!empty($request['homeowner_first_name'])) {
                                                        $initials .= strtoupper(substr($request['homeowner_first_name'], 0, 1));
                                                    }
                                                    if(!empty($request['homeowner_last_name'])) {
                                                        $initials .= strtoupper(substr($request['homeowner_last_name'], 0, 1));
                                                    }
                                                    echo $initials ?: '?';
                                                    ?>
                                                </div>
                                                <div class="homeowner-details">
                                                    <div class="homeowner-name">
                                                        <?php echo htmlspecialchars($request['homeowner_first_name'] . ' ' . $request['homeowner_last_name']); ?>
                                                    </div>
                                                    <div class="homeowner-phone">
                                                        <?php echo htmlspecialchars($request['homeowner_phone'] ?? 'No phone'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($request['homeowner_subcity'] . ', ' . $request['homeowner_woreda']); ?>
                                            <?php if(!empty($request['homeowner_address'])): ?>
                                                <div class="small text-muted mt-1"><?php echo htmlspecialchars($request['homeowner_address']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($request['preferred_date']): ?>
                                                <div><?php echo date('M j, Y', strtotime($request['preferred_date'])); ?></div>
                                                <div class="small text-muted">
                                                    <?php 
                                                    $daysDiff = floor((strtotime($request['preferred_date']) - time()) / (60 * 60 * 24));
                                                    if($daysDiff < 0) {
                                                        echo '<span style="color:var(--danger)">Overdue</span>';
                                                    } elseif($daysDiff == 0) {
                                                        echo '<span style="color:var(--warning)">Today</span>';
                                                    } elseif($daysDiff == 1) {
                                                        echo '<span style="color:var(--warning)">Tomorrow</span>';
                                                    } elseif($daysDiff <= 7) {
                                                        echo '<span style="color:var(--info)">In ' . $daysDiff . ' days</span>';
                                                    } else {
                                                        echo 'In ' . $daysDiff . ' days';
                                                    }
                                                    ?>
                                                </div>
                                            <?php else: ?>
                                                <div>Flexible</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $urgency = $request['urgency_level'] ?? 'normal';
                                            $urgencyColors = [
                                                'low' => 'var(--success)',
                                                'normal' => 'var(--warning)',
                                                'high' => 'var(--danger)'
                                            ];
                                            ?>
                                            <span class="status-badge" style="background:<?php echo $urgencyColors[$urgency] ?? 'var(--warning)'; ?>20; color:<?php echo $urgencyColors[$urgency] ?? 'var(--warning)'; ?>;">
                                                <?php echo ucfirst($urgency); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-form">
                                                <div class="action-buttons">
                                                    <a href="request_details.php?id=<?php echo (int)$request['id']; ?>&from=pending" class="btn btn-outline btn-sm" target="_blank">
                                                        <i class="fas fa-eye me-1"></i> Details
                                                    </a>
                                                    <button class="btn btn-success btn-sm" onclick="acceptRequest(<?php echo (int)$request['id']; ?>)">
                                                        <i class="fas fa-check me-1"></i> Accept
                                                    </button>
                                                </div>
                                                <textarea 
                                                    name="reason" 
                                                    class="form-control" 
                                                    rows="2" 
                                                    placeholder="Reason for rejection (required)" 
                                                    id="reason-<?php echo (int)$request['id']; ?>"
                                                    style="display: none;"
                                                ></textarea>
                                                <button class="btn btn-danger btn-sm" onclick="showRejectForm(<?php echo (int)$request['id']; ?>)">
                                                    <i class="fas fa-times me-1"></i> Reject
                                                </button>
                                                
                                                <!-- Hidden form for submission -->
                                                <form method="POST" class="d-none" id="accept-form-<?php echo (int)$request['id']; ?>">
                                                    <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                                    <input type="hidden" name="action" value="accept">
                                                </form>
                                                
                                                <form method="POST" class="d-none" id="reject-form-<?php echo (int)$request['id']; ?>">
                                                    <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="reason" id="reject-reason-<?php echo (int)$request['id']; ?>">
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state" data-aos="fade-up" data-aos-delay="300">
                        <i class="fas fa-inbox"></i>
                        <h3>No Pending Tasks</h3>
                        <p>You're all caught up! New service requests will appear here when homeowners submit them.</p>
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Clear Confirmation Modal -->
    <div class="modal" id="clear-confirm-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Clear All Pending Tasks
                </div>
                <p class="text-muted mb-0">This action will reject all pending tasks automatically.</p>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to clear all <?php echo $pendingCount; ?> pending task<?php echo $pendingCount == 1 ? '' : 's'; ?>? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    All pending requests will be automatically rejected with a system message.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="hideClearConfirmation()">
                    <i class="fas fa-times me-2"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" onclick="submitClearForm()">
                    <i class="fas fa-trash-alt me-2"></i> Clear All Tasks
                </button>
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

        // Show reject form for a specific request
        function showRejectForm(requestId) {
            const textarea = document.getElementById('reason-' + requestId);
            const rejectBtn = document.querySelector(`button[onclick="showRejectForm(${requestId})"]`);
            const acceptBtn = document.querySelector(`button[onclick="acceptRequest(${requestId})"]`);
            
            if (textarea.style.display === 'none') {
                textarea.style.display = 'block';
                rejectBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Submit Rejection';
                rejectBtn.setAttribute('onclick', `submitReject(${requestId})`);
                acceptBtn.disabled = true;
                textarea.focus();
            }
        }

        // Accept request
        function acceptRequest(requestId) {
            if (!confirm('Are you sure you want to accept this task?')) {
                return;
            }
            
            const form = document.getElementById('accept-form-' + requestId);
            const submitBtn = document.querySelector(`button[onclick="acceptRequest(${requestId})"]`);
            
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<div class="spinner me-2"></div> Accepting...';
                submitBtn.disabled = true;
                
                // Submit form after delay for visual feedback
                setTimeout(() => {
                    form.submit();
                }, 500);
            } else {
                form.submit();
            }
        }

        // Submit reject request
        function submitReject(requestId) {
            const textarea = document.getElementById('reason-' + requestId);
            const reason = textarea.value.trim();
            const rejectBtn = document.querySelector(`button[onclick="submitReject(${requestId})"]`);
            
            if (!reason) {
                alert('Please provide a reason for rejection.');
                textarea.focus();
                return;
            }
            
            if (!confirm('Are you sure you want to reject this task?')) {
                return;
            }
            
            const form = document.getElementById('reject-form-' + requestId);
            const reasonInput = document.getElementById('reject-reason-' + requestId);
            
            reasonInput.value = reason;
            
            if (rejectBtn) {
                const originalText = rejectBtn.innerHTML;
                rejectBtn.innerHTML = '<div class="spinner me-2"></div> Rejecting...';
                rejectBtn.disabled = true;
                
                // Submit form after delay for visual feedback
                setTimeout(() => {
                    form.submit();
                }, 500);
            } else {
                form.submit();
            }
        }

        // Show clear confirmation modal
        function showClearConfirmation() {
            const modal = document.getElementById('clear-confirm-modal');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        // Hide clear confirmation modal
        function hideClearConfirmation() {
            const modal = document.getElementById('clear-confirm-modal');
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }

        // Submit clear form
        function submitClearForm() {
            const form = document.getElementById('clear-pending-form');
            const submitBtn = document.querySelector('#clear-confirm-modal .btn-danger');
            
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<div class="spinner me-2"></div> Clearing...';
                submitBtn.disabled = true;
                
                // Submit form after delay for visual feedback
                setTimeout(() => {
                    form.submit();
                }, 500);
            } else {
                form.submit();
            }
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

        // Update stats with animation
        function updateStats() {
            const pendingCount = <?php echo $pendingCount; ?>;
            const pendingElement = document.getElementById('pending-count');
            
            if (pendingElement) {
                animateValue(pendingElement, 0, pendingCount, 1000);
            }
            
            // Simulate fetching active and completed counts (replace with actual API calls)
            setTimeout(() => {
                const activeElement = document.getElementById('active-count');
                const completedElement = document.getElementById('completed-count');
                
                if (activeElement) {
                    // Replace with actual data
                    const activeCount = Math.floor(Math.random() * 5) + 1;
                    animateValue(activeElement, 0, activeCount, 1000);
                }
                
                if (completedElement) {
                    // Replace with actual data
                    const completedCount = Math.floor(Math.random() * 10) + 5;
                    animateValue(completedElement, 0, completedCount, 1000);
                }
            }, 500);
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

        // Close modal when clicking outside
        document.getElementById('clear-confirm-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideClearConfirmation();
            }
        });

        // Escape key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideClearConfirmation();
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateStats();
            
            // Add fade-in animation for existing alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach((alert, index) => {
                alert.style.animation = `fadeIn 0.5s ease ${index * 0.1}s forwards`;
            });
            
            // Add animation for table rows on hover
            const tableRows = document.querySelectorAll('.data-table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });

        // Add fadeIn animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
            
            .pulse {
                animation: pulse 2s infinite;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>