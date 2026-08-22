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
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['price_action'], $_POST['request_id'])) {
        $decision = $_POST['price_action'] === 'accept' ? 'accept' : 'reject';
        $requestId = (int)$_POST['request_id'];
        $result = $homeownerController->respondToPriceProposal($requestId, $decision);
        if($result) {
            $message = $decision === 'accept'
                ? '<div class="alert alert-success" data-aos="fade-down">Price accepted! The technician has been notified.</div>'
                : '<div class="alert alert-info" data-aos="fade-down">You rejected the proposed price. The technician has been notified.</div>';
        } else {
            $message = '<div class="alert alert-danger" data-aos="fade-down">Unable to update this request. Please refresh and try again.</div>';
        }
    } elseif(isset($_POST['action']) && $_POST['action'] === 'submit_payment' && isset($_POST['request_id'])) {
        $requestId = (int)$_POST['request_id'];
        $data = [
            'amount' => $_POST['amount'] ?? null,
            'payment_method' => $_POST['payment_method'] ?? null,
            'transaction_id' => $_POST['transaction_id'] ?? null
        ];
        $result = $homeownerController->submitPaymentReceipt($requestId, $data, $_FILES ?? []);
        if(!empty($result['success'])) {
            $message = '<div class="alert alert-success" data-aos="fade-down">Payment details submitted! Awaiting technician confirmation.</div>';
        } else {
            $errorCopy = htmlspecialchars($result['message'] ?? 'Unable to submit payment.');
            $message = "<div class=\"alert alert-danger\" data-aos=\"fade-down\">$errorCopy</div>";
        }
    }
}

$allRequests = $homeownerController->getAllServiceRequests();
// Load latest notifications and unread count for real-time header indicator
$notifications = $homeownerController->getNotifications(5);
$unreadNotifications = $homeownerController->getUnreadNotificationCount();

$pageTitle = 'My Service Requests';
$pageDescription = 'Review every service journey, approve pricing, upload receipts, and keep work moving forward.';
$headerActions = [
    [
        'label' => 'New Request',
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
    <title>My Service Requests - Homefix Pro</title>
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

        .status-completed {
            background: rgba(76, 201, 240, 0.15);
            color: #4cc9f0;
        }

        .status-in-progress {
            background: rgba(67, 97, 238, 0.15);
            color: #4361ee;
        }

        .status-waiting_acceptance {
            background: rgba(67, 97, 238, 0.15);
            color: #4361ee;
        }

        .status-price_proposed {
            background: rgba(76, 201, 240, 0.15);
            color: #4cc9f0;
        }

        .status-price_accepted {
            background: rgba(76, 201, 240, 0.15);
            color: #4cc9f0;
        }

        .status-price_rejected {
            background: rgba(247, 37, 133, 0.15);
            color: #f72585;
        }

        .status-payment_requested {
            background: rgba(248, 150, 30, 0.15);
            color: #f8961e;
        }

        .status-paid {
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

        .btn-outline-danger {
            background: transparent;
            color: var(--danger);
            border: 2px solid var(--danger);
            padding: 10px 20px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-outline-danger:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-2px);
        }

        .btn-outline-secondary {
            background: transparent;
            color: var(--gray);
            border: 2px solid var(--gray);
            padding: 8px 16px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-outline-secondary:hover {
            background: var(--gray);
            color: white;
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

        .alert-info {
            background: rgba(67, 97, 238, 0.15);
            color: var(--dark);
            border-left: 4px solid var(--primary);
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

        /* Badge Styles */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .bg-light {
            background: var(--gray-light);
            color: var(--dark);
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
        <!-- Include the sidebar component -->
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <!-- Header -->
            <div class="dashboard-header">
                <div class="welcome-section">
                    <h1>My Service Requests</h1>
                    <p>Review every service journey, approve pricing, upload receipts, and keep work moving forward.</p>
                </div>

                <!-- Display Messages -->
                <?php if(!empty($message)): ?>
                    <?php echo $message; ?>
                <?php endif; ?>

                <!-- Service Requests Card -->
                <div class="card" data-searchable="service requests table">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">All Service Requests</h3>
                            <p class="card-subtitle">Manage your service requests and track their progress.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn-secondary">
                                <i class="fa-solid fa-filter"></i>
                                Filter
                            </button>
                            <button class="btn-secondary">
                                <i class="fa-solid fa-download"></i>
                                Export
                            </button>
                        </div>
                    </div>

                    <?php if(count($allRequests) > 0): ?>
                        <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Service</th>
                                    <th>Technician</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th style="min-width:280px;">Price / Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($allRequests as $request): ?>
                                    <tr data-aos="fade-up">
                                        <td class="fw-semibold"><?php echo htmlspecialchars($request['title']); ?></td>
                                        <td><?php echo htmlspecialchars($request['service_type']); ?></td>
                                        <td>
                                            <?php if($request['first_name']): ?>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($request['profession']); ?></small>
                                            <?php else: ?>
                                                <span class="badge bg-light">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status status-<?php echo $request['status']; ?>">
                                                <?php 
                                                $statusText = $request['status'];
                                                if($statusText == 'waiting_acceptance') {
                                                    echo 'Waiting Acceptance';
                                                } else {
                                                    echo ucfirst(str_replace('_',' ', $statusText));
                                                }
                                                ?>
                                            </span>
                                            <?php if(!empty($request['inspection_submitted_at'])): ?>
                                                <div><small class="text-success fw-semibold">Inspection submitted</small></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($request['created_at'])); ?></td>
                                        <td>
                                            <?php if($request['status'] === 'price_proposed'): ?>
                                                <div class="mb-3">
                                                    <div class="fw-semibold">Proposed Price: ETB <?php echo number_format($request['estimated_cost'], 2); ?></div>
                                                    <?php if(!empty($request['inspection_notes'])): ?>
                                                        <small class="text-muted d-block"><?php echo htmlspecialchars($request['inspection_notes']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <form method="POST" class="d-flex flex-wrap gap-2">
                                                    <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                                    <button class="btn-soft-primary" name="price_action" value="accept">Accept</button>
                                                    <button class="btn-outline-danger" name="price_action" value="reject">Reject</button>
                                                </form>
                                            <?php elseif($request['status'] === 'price_accepted'): ?>
                                                <div class="fw-semibold text-success">Price Accepted: ETB <?php echo number_format($request['estimated_cost'], 2); ?></div>
                                            <?php elseif($request['status'] === 'price_rejected'): ?>
                                                <div class="fw-semibold text-danger">Price rejected</div>
                                            <?php elseif($request['status'] === 'payment_requested'): ?>
                                                <?php 
                                                    $paymentAmount = $request['payment_amount'] ?? $request['estimated_cost'] ?? $request['budget'] ?? 0;
                                                    $teleBirr = $request['tele_birr'] ?: 'Not provided';
                                                    $bankAccount = $request['bank_account'] ?: 'Not provided';
                                                ?>
                                                <div class="payment-instructions">
                                                    <strong>Technician Payment Details</strong>
                                                    <ul>
                                                        <li>TeleBirr: <?php echo htmlspecialchars($teleBirr); ?></li>
                                                        <li>Bank / CBE: <?php echo htmlspecialchars($bankAccount); ?></li>
                                                        <li>Amount Due: <strong>ETB <?php echo number_format($paymentAmount, 2); ?></strong></li>
                                                    </ul>
                                                    <small class="text-muted">Pay via a preferred method and upload the receipt below.</small>
                                                </div>

                                                <?php if($request['payment_record_status'] === 'paid'): ?>
                                                    <div class="alert alert-warning">
                                                        Payment proof uploaded<?php echo $request['payment_paid_at'] ? ' on ' . date('M j, Y g:i A', strtotime($request['payment_paid_at'])) : ''; ?>.
                                                        <?php if(!empty($request['payment_technician_confirmed_at'])): ?>
                                                            <br>Technician confirmed on <?php echo date('M j, Y g:i A', strtotime($request['payment_technician_confirmed_at'])); ?>.
                                                        <?php else: ?>
                                                            <br>Awaiting technician confirmation.
                                                        <?php endif; ?>
                                                        <?php if(!empty($request['payment_proof'])): ?>
                                                            <br><a class="text-decoration-underline" href="../../assets/uploads/payments/<?php echo htmlspecialchars($request['payment_proof']); ?>" target="_blank">View uploaded receipt</a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <form method="POST" enctype="multipart/form-data" class="payment-upload-form d-flex flex-column gap-2">
                                                        <input type="hidden" name="action" value="submit_payment">
                                                        <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                                        <label class="fw-semibold">Amount (ETB)
                                                            <input type="number" step="0.01" name="amount" value="<?php echo htmlspecialchars($paymentAmount); ?>" class="form-control" required>
                                                        </label>
                                                        <label class="fw-semibold">Payment Method
                                                            <select name="payment_method" class="form-select" required>
                                                                <option value="">Select method</option>
                                                                <option value="tele_birr">TeleBirr</option>
                                                                <option value="cbe">CBE</option>
                                                                <option value="bank_transfer">Bank Transfer</option>
                                                                <option value="cash">Cash</option>
                                                            </select>
                                                        </label>
                                                        <label class="fw-semibold">Transaction / Reference ID (optional)
                                                            <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TeleBirr Ref #">
                                                        </label>
                                                        <label class="fw-semibold">Upload Receipt (JPG, PNG, PDF up to 5MB)
                                                            <input type="file" name="payment_proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                                        </label>
                                                        <button type="submit" class="btn-soft-primary align-self-start">Submit Payment Proof</button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php elseif($request['status'] === 'paid'): ?>
                                                <div class="alert alert-success">Payment verified. Thank you!</div>
                                            <?php endif; ?>
                                            <div class="mt-3">
                                                <a href="request_details.php?id=<?php echo $request['id']; ?>" class="btn-outline-secondary" target="_blank">
                                                    <i class="fa-solid fa-up-right-from-square"></i> View Details
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5" data-aos="fade-up">
                            <i class="fa-solid fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">No service requests found.</p>
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
                const buttons = document.querySelectorAll('.btn-primary, .btn-secondary, .btn-soft-primary, .btn-outline-danger');
                buttons.forEach(button => {
                    button.addEventListener('click', function() {
                        this.style.transform = 'scale(0.98)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 150);
                    });
                });
            });
        </script>
    </body>
</html>