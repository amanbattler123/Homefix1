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
$activeCount = 0;
$completedCount = 0;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if($_POST['action'] === 'submit_inspection') {
        $requestId = (int)$_POST['request_id'];
        $data = [
            'inspection_notes' => trim($_POST['inspection_notes'] ?? ''),
            'inspection_findings' => trim($_POST['inspection_findings'] ?? ''),
            'inspection_recommendations' => trim($_POST['inspection_recommendations'] ?? ''),
            'materials_cost' => !empty($_POST['materials_cost']) ? (float)$_POST['materials_cost'] : null,
            'labor_cost' => !empty($_POST['labor_cost']) ? (float)$_POST['labor_cost'] : null,
            'estimated_cost' => !empty($_POST['estimated_cost']) ? (float)$_POST['estimated_cost'] : null
        ];

        if(empty($data['inspection_notes']) || empty($data['estimated_cost'])) {
            $flashMessage = '<div class="alert alert-warning" data-aos="fade-down"><i class="fas fa-exclamation-triangle me-2"></i> Inspection notes and estimated cost are required.</div>';
        } else {
            $success = $technicianController->submitInspection($requestId, $data);
            $flashMessage = $success
                ? '<div class="alert alert-success" data-aos="fade-down"><i class="fas fa-check-circle me-2"></i> Inspection submitted successfully! Homeowner has been notified.</div>'
                : '<div class="alert alert-danger" data-aos="fade-down"><i class="fas fa-exclamation-circle me-2"></i> Unable to submit inspection for this request.</div>';
        }
    } elseif($_POST['action'] === 'complete_task') {
        $requestId = (int)$_POST['request_id'];
        $success = $technicianController->markTaskCompleted($requestId);
        $flashMessage = $success
            ? '<div class="alert alert-success" data-aos="fade-down"><i class="fas fa-check-circle me-2"></i> Task marked as completed! Homeowner has been notified.</div>'
            : '<div class="alert alert-danger" data-aos="fade-down"><i class="fas fa-exclamation-circle me-2"></i> Unable to complete this task. Please refresh and try again.</div>';
    } elseif($_POST['action'] === 'clear_my_tasks') {
        $success = $technicianController->clearMyTasksHistory();
        $flashMessage = $success
            ? '<div class="alert alert-info" data-aos="fade-down"><i class="fas fa-check-circle me-2"></i> All your task history has been cleared.</div>'
            : '<div class="alert alert-danger" data-aos="fade-down"><i class="fas fa-exclamation-circle me-2"></i> Unable to clear task history. Please try again.</div>';
    }
}

$activeRequests = $technicianController->getActiveRequests();
$completedRequests = $technicianController->getCompletedRequests();

$activeCount = count($activeRequests);
$completedCount = count($completedRequests);
$inspectionCount = 0;
foreach ($activeRequests as $req) {
    if (!empty($req['status']) && $req['status'] === 'waiting_inspection') {
        $inspectionCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - HomeFix Pro</title>
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

        .status-waiting_inspection {
            background: rgba(248, 150, 30, 0.15);
            color: #f3722c;
        }

        .status-waiting_approval {
            background: rgba(73, 149, 239, 0.15);
            color: #4895ef;
        }

        .status-price_accepted {
            background: rgba(6, 214, 160, 0.15);
            color: #06d6a0;
        }

        .status-in_progress {
            background: rgba(67, 97, 238, 0.15);
            color: #4361ee;
        }

        .status-completed {
            background: rgba(111, 66, 193, 0.15);
            color: #6f42c1;
        }

        .status-rejected {
            background: rgba(247, 37, 133, 0.15);
            color: #f72585;
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

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

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

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* Cost Calculator */
        .cost-calculator {
            background: var(--light);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 20px;
        }

        .cost-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .cost-row:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 16px;
        }

        .cost-label {
            color: var(--gray);
        }

        .cost-value {
            font-weight: 600;
            color: var(--dark);
        }

        /* Inspection Form */
        .inspection-form {
            background: var(--light);
            border-radius: var(--radius);
            padding: 25px;
            border: 1px solid var(--gray-light);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
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

        /* Task Card View */
        .task-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .task-card {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-top: 4px solid var(--primary);
        }

        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .task-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .task-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .task-service {
            font-size: 13px;
            color: var(--primary);
            font-weight: 500;
            background: rgba(67, 97, 238, 0.1);
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
        }

        .task-details {
            margin-bottom: 20px;
        }

        .task-detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .task-detail-item i {
            color: var(--gray);
            width: 20px;
        }

        .task-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
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
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
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
            
            .task-cards {
                grid-template-columns: 1fr;
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
            
            .tab-navigation {
                flex-direction: column;
            }
            
            .data-table {
                min-width: 600px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
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
                    <h1>My Tasks</h1>
                    <p>Manage your active tasks and track completed service requests</p>
                </div>
                <div class="header-actions">
                    <div class="notification-bell">
                        <i class="fa-solid fa-bell"></i>
                        <?php $unreadNotifications = $technicianController->getUnreadNotificationCount(); ?>
                        <?php if(!empty($unreadNotifications)): ?>
                            <span class="notification-badge"></span>
                        <?php endif; ?>
                    </div>
                    <a href="pending_tasks.php" class="btn-primary">
                        <i class="fa-solid fa-clock"></i>
                        Pending Tasks
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container" data-aos="fade-up">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="active-count" style="color:#000;"><?php echo $activeCount; ?></div>
                        <div class="stat-label" style="color:#000;">Active Tasks</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="pending-inspection" style="color:#000;"><?php echo $inspectionCount; ?></div>
                        <div class="stat-label" style="color:#000;">Need Inspection</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="completed-count" style="color:#000;"><?php echo $completedCount; ?></div>
                        <div class="stat-label" style="color:#000;">Completed Tasks</div>
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
                <button class="tab-btn active" onclick="switchTab('active')" id="tab-active">
                    <i class="fas fa-tasks me-2"></i> Active Tasks
                    <span class="badge" id="active-badge"><?php echo $activeCount; ?></span>
                </button>
                <button class="tab-btn" onclick="switchTab('completed')" id="tab-completed">
                    <i class="fas fa-check-circle me-2"></i> Completed Tasks
                    <span class="badge" id="completed-badge"><?php echo $completedCount; ?></span>
                </button>
            </div>

            <!-- Active Tasks Section -->
            <div class="tab-content active" id="active-tab" data-aos="fade-up" data-aos-delay="200">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title"><i class="fas fa-tasks"></i> Active Tasks</h2>
                            <p class="card-subtitle">Manage your ongoing service requests</p>
                        </div>
                        <?php if($activeCount > 0 || $completedCount > 0): ?>
                        <button type="button" class="btn btn-danger" onclick="showClearConfirmation()">
                            <i class="fas fa-trash-alt me-2"></i> Clear Task History
                        </button>
                        <?php endif; ?>
                    </div>

                    <?php if($activeCount > 0): ?>
                        <!-- Task Cards View -->
                        <div class="task-cards">
                            <?php foreach($activeRequests as $index => $request): ?>
                                <div class="task-card" data-aos="fade-up" data-aos-delay="<?php echo ($index * 100) + 300; ?>">
                                    <div class="task-card-header">
                                        <div>
                                            <div class="task-title"><?php echo htmlspecialchars($request['title']); ?></div>
                                            <span class="task-service"><?php echo htmlspecialchars($request['service_type']); ?></span>
                                        </div>
                                        <span class="status-badge status-<?php echo $request['status']; ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($request['status'])); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="task-details">
                                        <div class="task-detail-item">
                                            <i class="fas fa-user"></i>
                                            <div>
                                                <strong><?php echo htmlspecialchars($request['homeowner_first_name'] . ' ' . $request['homeowner_last_name']); ?></strong>
                                                <div class="text-muted small"><?php echo htmlspecialchars($request['homeowner_phone'] ?? 'No phone'); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="task-detail-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($request['homeowner_subcity'] . ', ' . $request['homeowner_woreda']); ?></span>
                                        </div>
                                        
                                        <div class="task-detail-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>
                                                <?php if($request['preferred_date']): ?>
                                                    Preferred: <?php echo date('M j, Y', strtotime($request['preferred_date'])); ?>
                                                <?php else: ?>
                                                    Flexible timing
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        
                                        <?php if($request['estimated_cost']): ?>
                                        <div class="task-detail-item">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <span>
                                                <strong>Estimated Cost:</strong> ETB <?php echo number_format($request['estimated_cost'], 2); ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="task-actions">
                                        <a href="request_details.php?id=<?php echo (int)$request['id']; ?>" class="btn btn-outline btn-sm" target="_blank">
                                            <i class="fas fa-eye me-1"></i> Details
                                        </a>
                                        
                                        <?php if($request['status'] === 'waiting_inspection'): ?>
                                            <button class="btn btn-warning btn-sm" onclick="showInspectionModal(<?php echo (int)$request['id']; ?>, '<?php echo htmlspecialchars($request['title'], ENT_QUOTES); ?>')">
                                                <i class="fas fa-clipboard-check me-1"></i> Submit Inspection
                                            </button>
                                        <?php elseif(in_array($request['status'], ['price_accepted', 'in_progress'])): ?>
                                            <button class="btn btn-success btn-sm" onclick="completeTask(<?php echo (int)$request['id']; ?>)">
                                                <i class="fas fa-check me-1"></i> Mark Complete
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" data-aos="fade-up" data-aos-delay="300">
                            <i class="fas fa-check-circle"></i>
                            <h3>No Active Tasks</h3>
                            <p>You don't have any active tasks at the moment. Check your pending tasks or wait for new assignments.</p>
                            <a href="pending_tasks.php" class="btn btn-primary">
                                <i class="fas fa-clock me-2"></i> Check Pending Tasks
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Completed Tasks Section -->
            <div class="tab-content d-none" id="completed-tab">
                <div class="card" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title"><i class="fas fa-check-circle"></i> Completed Tasks</h2>
                            <p class="card-subtitle">Review your completed service requests</p>
                        </div>
                    </div>

                    <?php if($completedCount > 0): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Service Request</th>
                                        <th>Homeowner</th>
                                        <th>Service Type</th>
                                        <th>Completed Date</th>
                                        <th>Total Earned</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($completedRequests as $index => $request): ?>
                                        <tr data-aos="fade-up" data-aos-delay="<?php echo ($index * 100) + 300; ?>">
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($request['title']); ?></strong>
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
                                                <?php echo htmlspecialchars($request['service_type']); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $completedDate = $request['work_completed_at'] ?: $request['updated_at'];
                                                echo date('M j, Y', strtotime($completedDate));
                                                ?>
                                                <div class="small text-muted">
                                                    <?php 
                                                    $daysAgo = floor((time() - strtotime($completedDate)) / (60 * 60 * 24));
                                                    echo $daysAgo == 0 ? 'Today' : ($daysAgo . ' day' . ($daysAgo == 1 ? '' : 's') . ' ago');
                                                    ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($request['estimated_cost']): ?>
                                                    <strong>ETB <?php echo number_format($request['estimated_cost'], 2); ?></strong>
                                                <?php else: ?>
                                                    <span class="text-muted">Not specified</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" data-aos="fade-up" data-aos-delay="300">
                            <i class="fas fa-clipboard-list"></i>
                            <h3>No Completed Tasks</h3>
                            <p>You haven't completed any tasks yet. Once you finish active tasks, they'll appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Inspection Modal -->
    <div class="modal" id="inspection-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Submit Inspection Report
                </div>
                <p class="text-muted mb-0" id="modal-task-title"></p>
            </div>
            <div class="modal-body">
                <form method="POST" id="inspection-form">
                    <input type="hidden" name="action" value="submit_inspection">
                    <input type="hidden" name="request_id" id="inspection-request-id">
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-search me-2"></i> Inspection Findings *</label>
                        <textarea name="inspection_findings" class="form-control" rows="3" placeholder="Describe what you found during inspection..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-tools me-2"></i> Recommended Work</label>
                        <textarea name="inspection_recommendations" class="form-control" rows="3" placeholder="What work do you recommend?"></textarea>
                    </div>
                    
                    <div class="cost-calculator">
                        <h4><i class="fas fa-calculator me-2"></i> Cost Estimation</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Materials Cost (ETB)</label>
                                <input type="number" step="0.01" name="materials_cost" class="form-control" placeholder="0.00" id="materials-cost">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Labor Cost (ETB)</label>
                                <input type="number" step="0.01" name="labor_cost" class="form-control" placeholder="0.00" id="labor-cost">
                            </div>
                        </div>
                        
                        <div class="cost-row">
                            <span class="cost-label">Materials + Labor:</span>
                            <span class="cost-value" id="subtotal-cost">ETB 0.00</span>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-file-invoice-dollar me-2"></i> Proposed Total Price (ETB) *</label>
                            <input type="number" step="0.01" name="estimated_cost" class="form-control" placeholder="0.00" required id="total-cost" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-comment-alt me-2"></i> Notes for Homeowner *</label>
                        <textarea name="inspection_notes" class="form-control" rows="4" placeholder="Additional notes, timeline estimates, or special instructions..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="hideInspectionModal()">
                    <i class="fas fa-times me-2"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="submitInspection()">
                    <i class="fas fa-paper-plane me-2"></i> Submit Inspection
                </button>
            </div>
        </div>
    </div>

    <!-- Clear Confirmation Modal -->
    <div class="modal" id="clear-confirm-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Clear Task History
                </div>
                <p class="text-muted mb-0">This action will permanently remove your task history.</p>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to clear all your task history? This includes:</p>
                <ul>
                    <li><strong><?php echo $activeCount; ?></strong> active task<?php echo $activeCount == 1 ? '' : 's'; ?></li>
                    <li><strong><?php echo $completedCount; ?></strong> completed task<?php echo $completedCount == 1 ? '' : 's'; ?></li>
                </ul>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    This action cannot be undone. All task history will be permanently removed from your view.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="hideClearConfirmation()">
                    <i class="fas fa-times me-2"></i> Cancel
                </button>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="clear_my_tasks">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-2"></i> Clear History
                    </button>
                </form>
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
        }

        // Show inspection modal
        function showInspectionModal(requestId, taskTitle) {
            document.getElementById('inspection-request-id').value = requestId;
            document.getElementById('modal-task-title').textContent = taskTitle;
            document.getElementById('inspection-modal').classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Reset form
            document.getElementById('inspection-form').reset();
            updateCostCalculation();
        }

        // Hide inspection modal
        function hideInspectionModal() {
            document.getElementById('inspection-modal').classList.remove('show');
            document.body.style.overflow = '';
        }

        // Submit inspection form
        function submitInspection() {
            const form = document.getElementById('inspection-form');
            const notes = form.querySelector('[name="inspection_notes"]').value.trim();
            const totalCost = form.querySelector('[name="estimated_cost"]').value.trim();
            const submitBtn = document.querySelector('#inspection-modal .btn-success');
            
            // Validation
            if (!notes || !totalCost) {
                alert('Please fill in all required fields (Notes and Total Cost).');
                return;
            }
            
            if (!confirm('Submit this inspection report? The homeowner will be notified.')) {
                return;
            }
            
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<div class="spinner me-2"></div> Submitting...';
                submitBtn.disabled = true;
                
                // Submit form after delay for visual feedback
                setTimeout(() => {
                    form.submit();
                }, 500);
            } else {
                form.submit();
            }
        }

        // Complete task
        function completeTask(requestId) {
            if (!confirm('Mark this task as completed? The homeowner will be notified.')) {
                return;
            }
            
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="complete_task">
                <input type="hidden" name="request_id" value="${requestId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        // Show clear confirmation modal
        function showClearConfirmation() {
            document.getElementById('clear-confirm-modal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        // Hide clear confirmation modal
        function hideClearConfirmation() {
            document.getElementById('clear-confirm-modal').classList.remove('show');
            document.body.style.overflow = '';
        }

        // Cost calculation: Proposed Total Price = Materials + Labor
        function updateCostCalculation() {
            const materialsCost = parseFloat(document.getElementById('materials-cost').value) || 0;
            const laborCost = parseFloat(document.getElementById('labor-cost').value) || 0;
            const subtotal = materialsCost + laborCost;
            
            document.getElementById('subtotal-cost').textContent = 'ETB ' + subtotal.toFixed(2);

            // Always sync total cost with subtotal so technician cannot submit any other number
            const totalInput = document.getElementById('total-cost');
            if (totalInput) {
                totalInput.value = subtotal > 0 ? subtotal.toFixed(2) : '';
            }
        }

        // Update stats with animation
        function updateStats() {
            const activeCount = <?php echo $activeCount; ?>;
            const completedCount = <?php echo $completedCount; ?>;
            
            // Count tasks waiting for inspection
            const waitingInspection = <?php 
                $count = 0;
                foreach($activeRequests as $request) {
                    if($request['status'] === 'waiting_inspection') $count++;
                }
                echo $count;
            ?>;
            
            // Animate counters
            animateValue('active-count', activeCount);
            animateValue('pending-inspection', waitingInspection);
            animateValue('completed-count', completedCount);
            
            // Update badges
            document.getElementById('active-badge').textContent = activeCount;
            document.getElementById('completed-badge').textContent = completedCount;
        }

        // Animate number counting
        function animateValue(elementId, target) {
            const element = document.getElementById(elementId);
            if (!element) return;
            
            const current = parseInt(element.textContent) || 0;
            if (current === target) return;
            
            const increment = target > current ? 1 : -1;
            let currentValue = current;
            
            const timer = setInterval(() => {
                currentValue += increment;
                element.textContent = currentValue;
                
                if ((increment > 0 && currentValue >= target) || (increment < 0 && currentValue <= target)) {
                    element.textContent = target;
                    clearInterval(timer);
                }
            }, 30);
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

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        });

        // Escape key to close modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.show').forEach(modal => {
                    modal.classList.remove('show');
                });
                document.body.style.overflow = '';
            }
        });

        // Event listeners for cost calculation
        document.getElementById('materials-cost')?.addEventListener('input', updateCostCalculation);
        document.getElementById('labor-cost')?.addEventListener('input', updateCostCalculation);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateStats();
            
            // Add fade-in animation for existing alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach((alert, index) => {
                alert.style.animation = `fadeIn 0.5s ease ${index * 0.1}s forwards`;
            });
            
            // Add animation for task cards on hover
            const taskCards = document.querySelectorAll('.task-card');
            taskCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
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
            
            .task-card[data-aos].aos-animate {
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
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>