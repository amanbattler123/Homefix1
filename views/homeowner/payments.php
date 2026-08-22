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

$message = '';
$focusRequestId = isset($_GET['request_id']) ? (int)$_GET['request_id'] : null;

// Capture filters from query parameters
$rawFilters = [
    'status' => $_GET['status'] ?? '',
    'payment_method' => $_GET['payment_method'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$validStatuses = ['pending', 'paid', 'verified'];
$validMethods = ['tele_birr', 'cbe', 'bank_transfer', 'cash', 'not_specified'];

$filters = [
    'status' => in_array(strtolower(trim($rawFilters['status'])), $validStatuses, true)
        ? strtolower(trim($rawFilters['status']))
        : '',
    'payment_method' => in_array(strtolower(trim($rawFilters['payment_method'])), $validMethods, true)
        ? strtolower(trim($rawFilters['payment_method']))
        : '',
    'date_from' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawFilters['date_from']) ? $rawFilters['date_from'] : '',
    'date_to' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawFilters['date_to']) ? $rawFilters['date_to'] : '',
    'search' => trim($rawFilters['search'])
];

if(isset($_GET['action']) && $_GET['action'] === 'export') {
    $homeownerController->exportPaymentsCsv($filters);
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_payment') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $data = [
        'amount' => $_POST['amount'] ?? null,
        'payment_method' => $_POST['payment_method'] ?? null,
        'transaction_id' => $_POST['transaction_id'] ?? null
    ];
    $result = $homeownerController->submitPaymentReceipt($requestId, $data, $_FILES ?? []);
    if(!empty($result['success'])) {
        $message = '<div class="alert alert-success" data-aos="fade-down">Payment details submitted! Awaiting technician confirmation.</div>';
        $focusRequestId = $requestId;
    } else {
        $errorCopy = htmlspecialchars($result['message'] ?? 'Unable to submit payment.');
        $message = "<div class=\"alert alert-danger\" data-aos=\"fade-down\">$errorCopy</div>";
        $focusRequestId = $requestId;
    }
}

$payments = $homeownerController->getPayments($filters);
// Load latest notifications and unread count for real-time header indicator
$notifications = $homeownerController->getNotifications(5);
$unreadNotifications = $homeownerController->getUnreadNotificationCount();

$hasActiveFilters = array_filter($filters, function($value) {
    return $value !== '';
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Homefix Pro</title>
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
            align-items: flex-start;
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

        /* Status Badges */
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

        .status-paid {
            background: rgba(76, 201, 240, 0.15);
            color: #4cc9f0;
        }

        .status-verified {
            background: rgba(76, 201, 240, 0.15);
            color: #4cc9f0;
        }

        /* Button Styles */
        .btn-soft-primary {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-soft-primary:hover {
            background: rgba(67, 97, 238, 0.2);
            transform: translateY(-2px);
        }

        /* Form Styles */
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-light);
            border-radius: var(--radius);
            font-size: 14px;
            transition: var(--transition);
            margin-top: 5px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-light);
            border-radius: var(--radius);
            font-size: 14px;
            transition: var(--transition);
            margin-top: 5px;
            background: white;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        /* Alert Styles */
        .alert {
            padding: 20px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .alert-success {
            background: rgba(76, 201, 240, 0.15);
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

        /* Payment Instructions */
        .payment-instructions {
            background: rgba(67, 97, 238, 0.06);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 20px;
        }

        .payment-instructions strong {
            display: block;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .payment-instructions ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .payment-instructions li {
            margin-bottom: 5px;
        }

        /* Payment Action Forms */
        .payment-action {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .payment-upload-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .payment-upload-form label {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        .payment-upload-form button {
            align-self: flex-start;
        }

        /* Highlight Row */
        .highlight-row {
            box-shadow: 0 0 0 2px var(--primary) inset;
            background: rgba(67, 97, 238, 0.03);
        }

        /* Receipt Link */
        .receipt-link {
            display: inline-block;
            margin-top: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .receipt-link:hover {
            text-decoration: underline;
            color: var(--secondary);
        }

        /* Empty State */
        .text-center {
            text-align: center;
        }

        .py-5 {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

        .text-muted {
            color: var(--gray);
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        /* Flex Utilities */
        .d-flex {
            display: flex;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .flex-column {
            flex-direction: column;
        }

        .align-items-center {
            align-items: center;
        }

        .align-self-start {
            align-self: flex-start;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        /* Text Utilities */
        .fw-semibold {
            font-weight: 600;
        }

        .text-success {
            color: var(--success);
        }

        .text-danger {
            color: var(--danger);
        }

        .text-decoration-underline {
            text-decoration: underline;
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
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 12px 8px;
            }
            
            .payment-upload-form {
                min-width: 250px;
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

        .filters-form {
            flex: 1;
            min-width: 260px;
            background: rgba(67, 97, 238, 0.05);
            padding: 18px;
            border-radius: var(--radius);
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
        }

        .filters-form label {
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filters-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .btn-reset {
            background: transparent;
            border: none;
            color: var(--danger);
            font-weight: 600;
            cursor: pointer;
            padding: 0;
        }

        .filters-summary {
            font-size: 13px;
            color: var(--gray);
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filters-summary span {
            background: white;
            border-radius: 999px;
            padding: 4px 10px;
            border: 1px solid var(--gray-light);
        }
    </style>
</head>
<body class="homeowner-body">
    <div class="dashboard">
        <!-- Include the sidebar component -->
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <!-- Header -->
            <div class="dashboard-header">
                <div class="welcome-section">
                    <h1>Payment History</h1>
                    <p>Manage your service payments, upload receipts, and track payment status.</p>
                </div>
                <div class="header-actions">
                    <div class="notification-bell">
                        <i class="fa-solid fa-bell"></i>
                        <?php if (!empty($unreadNotifications)): ?>
                            <span class="notification-badge"></span>
                        <?php endif; ?>
                    </div>
                    <a href="request_service.php" class="btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        New Request
                    </a>
                </div>
            </div>

            <!-- Display Messages -->
            <?php if(!empty($message)): ?>
                <?php echo $message; ?>
            <?php endif; ?>

            <!-- Payment History Card -->
            <div class="card" data-searchable="payment history table">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Payment History</h3>
                        <p class="card-subtitle">Track all your service payments and their current status.</p>
                        <?php if(!empty($hasActiveFilters)): ?>
                            <div class="filters-summary" data-aos="fade-in">
                                <span><strong>Filters applied:</strong></span>
                                <?php foreach($filters as $key => $value): ?>
                                    <?php if($value === '') continue; ?>
                                    <span><?php echo ucfirst(str_replace('_', ' ', $key)); ?>: <?php echo htmlspecialchars(str_replace('_', ' ', $value)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <form id="paymentFilters" class="filters-form" method="GET">
                        <div class="filters-grid">
                            <label>Search
                                <input type="text" name="search" class="form-control" placeholder="Service, technician, ref#" value="<?php echo htmlspecialchars($filters['search']); ?>">
                            </label>
                            <label>Status
                                <select name="status" class="form-select">
                                    <option value="">Any</option>
                                    <?php foreach($validStatuses as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php echo $filters['status'] === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>Payment Method
                                <select name="payment_method" class="form-select">
                                    <option value="">Any</option>
                                    <?php foreach($validMethods as $method): ?>
                                        <option value="<?php echo $method; ?>" <?php echo $filters['payment_method'] === $method ? 'selected' : ''; ?>><?php echo $method === 'not_specified' ? 'Not specified' : ucfirst(str_replace('_', ' ', $method)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>Date From
                                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                            </label>
                            <label>Date To
                                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                            </label>
                        </div>
                        <div class="filters-actions">
                            <?php if(!empty($hasActiveFilters)): ?>
                                <a href="payments.php" class="btn-reset">Clear filters</a>
                            <?php endif; ?>
                            <button type="submit" class="btn-secondary">
                                <i class="fa-solid fa-filter"></i>
                                Apply Filters
                            </button>
                            <button type="submit" name="action" value="export" class="btn-secondary">
                                <i class="fa-solid fa-download"></i>
                                Export CSV
                            </button>
                        </div>
                    </form>
                </div>

                <?php if(count($payments) > 0): ?>
                    <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Service Request</th>
                                <th>Service Type</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th style="min-width:320px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($payments as $payment): ?>
                                <?php
                                    $rowHighlight = ($focusRequestId && (int)$payment['service_request_id'] === $focusRequestId) ? 'highlight-row' : '';
                                    $amount = $payment['amount'] ?: ($payment['estimated_cost'] ?? $payment['budget'] ?? 0);
                                    $teleBirr = $payment['technician_tele_birr'] ?? $payment['tele_birr'] ?? '';
                                    $bankAccount = $payment['technician_bank_account'] ?? $payment['bank_account'] ?? '';
                                    $teleBirrDisplay = $teleBirr !== '' ? htmlspecialchars($teleBirr) : 'Not provided';
                                    $bankDisplay = $bankAccount !== '' ? htmlspecialchars($bankAccount) : 'Not provided';

                                    $awaitingPayment = ($payment['request_status'] === 'payment_requested' && $payment['payment_status'] === 'pending');
                                    $awaitingVerification = ($payment['payment_status'] === 'paid');
                                ?>
                                <tr class="<?php echo $rowHighlight; ?>" data-aos="fade-up">
                                    <td class="fw-semibold"><?php echo htmlspecialchars($payment['title']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['service_type']); ?></td>
                                    <td class="fw-semibold">ETB <?php echo number_format($amount, 2); ?></td>
                                    <td>
                                        <?php 
                                        if($payment['payment_method']) {
                                            echo ucfirst(str_replace('_', ' ', $payment['payment_method']));
                                        } else {
                                            echo 'Not specified';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="status status-<?php echo $payment['payment_status']; ?>">
                                            <?php echo ucfirst($payment['payment_status']); ?>
                                        </span>
                                        <?php if($awaitingPayment): ?>
                                            <div><small class="text-success fw-semibold">Technician completed job & payment requested</small></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if($payment['paid_at']) {
                                            echo date('M j, Y', strtotime($payment['paid_at']));
                                        } else {
                                            echo date('M j, Y', strtotime($payment['created_at']));
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="payment-action">
                                            <?php if($awaitingPayment): ?>
                                                <div class="payment-instructions">
                                                    <strong>Technician Payment Details</strong>
                                                    <ul>
                                                        <li>TeleBirr: <?php echo $teleBirrDisplay; ?></li>
                                                        <li>Bank / CBE: <?php echo $bankDisplay; ?></li>
                                                        <li>Amount Due: <strong>ETB <?php echo number_format($amount, 2); ?></strong></li>
                                                    </ul>
                                                    <small class="text-muted">Please pay using one of the provided methods and upload your receipt.</small>
                                                </div>
                                                <form method="POST" enctype="multipart/form-data" class="payment-upload-form">
                                                    <input type="hidden" name="action" value="submit_payment">
                                                    <input type="hidden" name="request_id" value="<?php echo (int)$payment['service_request_id']; ?>">
                                                    <label>Amount (ETB)
                                                        <input type="number" step="0.01" name="amount" value="<?php echo htmlspecialchars($amount); ?>" class="form-control" required>
                                                    </label>
                                                    <label>Payment Method
                                                        <select name="payment_method" class="form-select" required>
                                                            <option value="">Select method</option>
                                                            <option value="tele_birr">TeleBirr</option>
                                                            <option value="cbe">CBE</option>
                                                            <option value="bank_transfer">Bank Transfer</option>
                                                            <option value="cash">Cash</option>
                                                        </select>
                                                    </label>
                                                    <label>Transaction / Reference ID (optional)
                                                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TeleBirr Ref #">
                                                    </label>
                                                    <label>Upload Receipt (JPG, PNG, PDF up to 5MB)
                                                        <input type="file" name="payment_proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                                    </label>
                                                    <button type="submit" class="btn-soft-primary">Submit Payment Proof</button>
                                                </form>
                                            <?php elseif($awaitingVerification): ?>
                                                <div class="alert alert-warning">
                                                    <i class="fa-solid fa-clock"></i>
                                                    <div>
                                                        <div class="fw-semibold">Payment Proof Uploaded</div>
                                                        <div>Uploaded<?php echo $payment['paid_at'] ? ' on ' . date('M j, Y g:i A', strtotime($payment['paid_at'])) : ''; ?></div>
                                                        <div>Awaiting technician confirmation</div>
                                                        <?php if(!empty($payment['payment_proof'])): ?>
                                                            <a class="receipt-link" href="../../assets/uploads/payments/<?php echo htmlspecialchars($payment['payment_proof']); ?>" target="_blank">
                                                                <i class="fa-solid fa-receipt"></i> View uploaded receipt
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php elseif($payment['payment_status'] === 'verified'): ?>
                                                <div class="alert alert-success">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                    <div>
                                                        <div class="fw-semibold">Payment Verified</div>
                                                        <div>Thank you for your payment!</div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-muted">
                                                    <i class="fa-solid fa-info-circle"></i> No action needed at this time.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5" data-aos="fade-up">
                        <i class="fa-solid fa-credit-card fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">No payment history found.</p>
                        <a href="request_service.php" class="btn-primary">
                            <i class="fa-solid fa-plus"></i> Create Your First Request
                        </a>
                    </div>
                <?php endif; ?>
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
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Button click effects
            const buttons = document.querySelectorAll('.btn-primary, .btn-secondary, .btn-soft-primary');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });

            // Auto-scroll to highlighted row if present
            const highlightedRow = document.querySelector('.highlight-row');
            if (highlightedRow) {
                setTimeout(() => {
                    highlightedRow.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }, 500);
            }
        });
    </script>
</body>
</html>