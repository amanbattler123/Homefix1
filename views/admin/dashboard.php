<?php
session_start();
// Fix the path for includes - going up two levels from views/admin/
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../includes/NotificationHelper.php';

// Check if user is admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Initialize database and controllers
$db = new Database();
$conn = $db->getConnection();
$userController = new UserController($conn);

// Handle actions
$message = '';
if(isset($_POST['update_status'])) {
    $user_id = $_POST['user_id'];
    $status = $_POST['status'];
    if($userController->updateUserStatus($user_id, $status)) {
        $message = '<div class="alert alert-success">Status updated successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error updating status.</div>';
    }
}

if(isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    if($userController->deleteUser($user_id)) {
        $message = '<div class="alert alert-success">User deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting user.</div>';
    }
}

if(isset($_POST['mark_as_read'])) {
    $message_id = $_POST['message_id'];
    if($userController->markMessageAsRead($message_id)) {
        $message = '<div class="alert alert-success">Message marked as read!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error updating message.</div>';
    }
}

if(isset($_POST['delete_message'])) {
    $message_id = $_POST['message_id'];
    if($userController->deleteMessage($message_id)) {
        $message = '<div class="alert alert-success">Message deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting message.</div>';
    }
}

if(isset($_POST['mark_all_read'])) {
    try {
        $stmt = $conn->prepare("UPDATE messages SET status = 'read' WHERE status = 'unread'");
        $stmt->execute();
        $message = '<div class="alert alert-success">All unread messages have been marked as read.</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error marking all messages as read.</div>';
    }
}

if(isset($_POST['delete_selected']) && !empty($_POST['selected_messages']) && is_array($_POST['selected_messages'])) {
    $ids = array_filter(array_map('intval', $_POST['selected_messages']));
    if(!empty($ids)) {
        try {
            $deletedCount = 0;
            foreach($ids as $id) {
                if($userController->deleteMessage($id)) {
                    $deletedCount++;
                }
            }
            if($deletedCount > 0) {
                $message = '<div class="alert alert-success">Selected messages deleted successfully.</div>';
            } else {
                $message = '<div class="alert alert-danger">No messages were deleted.</div>';
            }
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error deleting selected messages.</div>';
        }
    }
}

if(isset($_POST['verify_payment'])) {
    $paymentId = (int)$_POST['payment_id'];
    $paymentStmt = $conn->prepare("SELECT p.*, sr.title, sr.id AS request_id, sr.homeowner_id, sr.technician_id,
                                          h.first_name AS homeowner_first, h.last_name AS homeowner_last,
                                          t.first_name AS tech_first, t.last_name AS tech_last
                                   FROM payments p
                                   JOIN service_requests sr ON sr.id = p.service_request_id
                                   JOIN users h ON h.id = p.homeowner_id
                                   JOIN users t ON t.id = p.technician_id
                                   WHERE p.id = ?");
    $paymentStmt->execute([$paymentId]);
    $paymentRow = $paymentStmt->fetch(PDO::FETCH_ASSOC);

    if($paymentRow) {
        $conn->beginTransaction();
        try {
            $updatePayment = $conn->prepare("UPDATE payments SET payment_status = 'verified', verified_at = NOW(), verified_by = ? WHERE id = ?");
            $updatePayment->execute([$_SESSION['user_id'], $paymentId]);

            $updateRequest = $conn->prepare("UPDATE service_requests SET status = 'paid', payment_verified_at = NOW(), updated_at = NOW() WHERE id = ?");
            $updateRequest->execute([$paymentRow['request_id']]);

            $conn->commit();

            sendNotification($conn, $paymentRow['technician_id'], 'Payment Verified', "Admin verified payment for '{$paymentRow['title']}'.", 'payment', $paymentRow['request_id'], 'views/technician/payments.php');
            sendNotification($conn, $paymentRow['homeowner_id'], 'Payment Verified', "Your payment for '{$paymentRow['title']}' has been verified. Thank you!", 'payment', $paymentRow['request_id'], 'views/homeowner/payments.php');

            $message = '<div class="alert alert-success">Payment verified successfully!</div>';
        } catch (Exception $ex) {
            $conn->rollBack();
            $message = '<div class="alert alert-danger">Unable to verify payment. Please try again.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Payment record not found.</div>';
    }
}

// Get all data
$pendingTechnicians = $userController->getPendingTechnicians();
$allTechnicians = $userController->getAllTechnicians();
$allUsers = $userController->getAllUsers();
$stats = $userController->getStats();

// Set empty arrays for data that would come from missing controllers
$pendingRequests = [];
$allServiceRequests = [];
$serviceStats = [
    'total_requests' => 0,
    'pending_requests' => 0
];
$allReviews = [];
$reviewTechnicianOptions = [];
$reviewTechnicianFilter = isset($_GET['review_technician']) && $_GET['review_technician'] !== ''
    ? (int)$_GET['review_technician']
    : null;
$adminReviewStats = [
    'average_rating' => '0.0',
    'total_reviews' => 0,
    'positive_feedback' => 0,
    'negative_feedback' => 0
];
$pendingPayments = [];
$recentPayments = [];
$paymentQueue = [];
$finishedTasks = [];

if(!function_exists('resolveMediaPath')) {
    function resolveMediaPath($item, $folder = 'requests') {
        if(!$item) {
            return null;
        }
        if(preg_match('#^https?://#', $item)) {
            return $item;
        }
        if(strpos($item, 'assets/') !== false || strpos($item, '../') === 0) {
            return '../../' . ltrim($item, './');
        }
        $folder = trim($folder, '/');
        return '../../assets/uploads/' . $folder . '/' . ltrim($item, '/');
    }

    function formatMediaList($raw, $folder = 'requests') {
        if(empty($raw)) {
            return [];
        }
        $list = [];
        $decoded = json_decode($raw, true);
        if(is_array($decoded)) {
            $list = $decoded;
        } else {
            $parts = array_filter(array_map('trim', preg_split('/[,;]+/', $raw)));
            $list = $parts;
        }
        return array_values(array_filter(array_map(function($item) use ($folder) {
            return resolveMediaPath($item, $folder);
        }, $list)));
    }

    function getChatSnippets($conn, $homeownerId, $technicianId, $limit = 4) {
        if(!$homeownerId || !$technicianId) {
            return [];
        }
        $limit = max(1, (int)$limit);
        $stmt = $conn->prepare("SELECT cm.message, cm.created_at, u.first_name, u.last_name, u.role
                                 FROM chat_messages cm
                                 JOIN users u ON u.id = cm.sender_id
                                 WHERE (cm.sender_id = ? AND cm.receiver_id = ?)
                                    OR (cm.sender_id = ? AND cm.receiver_id = ?)
                                 ORDER BY cm.created_at DESC
                                 LIMIT {$limit}");
        $stmt->execute([$homeownerId, $technicianId, $technicianId, $homeownerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function($row) {
            return [
                'message' => $row['message'] ?? '',
                'sender_name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                'sender_role' => $row['role'] ?? null,
                'created_at' => $row['created_at'] ?? null,
            ];
        }, $rows ?: []);
    }
}

// Fetch pending payment verifications
$pendingPaymentsStmt = $conn->query("SELECT p.*, sr.title, sr.id AS request_id, sr.homeowner_id, sr.technician_id,
                                          h.first_name AS homeowner_first, h.last_name AS homeowner_last,
                                          t.first_name AS tech_first, t.last_name AS tech_last
                                   FROM payments p
                                   JOIN service_requests sr ON sr.id = p.service_request_id
                                   JOIN users h ON h.id = p.homeowner_id
                                   JOIN users t ON t.id = p.technician_id
                                   WHERE p.payment_status = 'pending'");
$pendingPayments = $pendingPaymentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent payments
$recentPaymentsStmt = $conn->query("SELECT p.*, sr.title, sr.id AS request_id, sr.homeowner_id, sr.technician_id,
                                          h.first_name AS homeowner_first, h.last_name AS homeowner_last,
                                          t.first_name AS tech_first, t.last_name AS tech_last
                                   FROM payments p
                                   JOIN service_requests sr ON sr.id = p.service_request_id
                                   JOIN users h ON h.id = p.homeowner_id
                                   JOIN users t ON t.id = p.technician_id
                                   WHERE p.payment_status = 'verified'
                                   ORDER BY p.verified_at DESC
                                   LIMIT 10");
$recentPayments = $recentPaymentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all service requests with payment progress for admin tracking
$serviceRequestsSql = "
    SELECT sr.*, 
           h.first_name AS homeowner_first_name,
           h.last_name AS homeowner_last_name,
           h.phone AS homeowner_phone,
           h.email AS homeowner_email,
           t.first_name AS technician_first_name,
           t.last_name AS technician_last_name,
           t.profession AS technician_profession,
           t.tele_birr AS technician_tele_birr,
           t.bank_account AS technician_bank_account,
           pay.id AS payment_id,
           pay.amount AS payment_amount,
           pay.payment_status,
           pay.payment_method,
           pay.transaction_id,
           pay.payment_proof,
           pay.paid_at,
           pay.verified_at,
           pay.created_at AS payment_created_at
    FROM service_requests sr
    JOIN users h ON sr.homeowner_id = h.id
    LEFT JOIN users t ON sr.technician_id = t.id
    LEFT JOIN (
        SELECT p.*
        FROM payments p
        INNER JOIN (
            SELECT service_request_id, MAX(id) AS latest_id
            FROM payments
            GROUP BY service_request_id
        ) latest ON latest.latest_id = p.id
    ) pay ON pay.service_request_id = sr.id
    ORDER BY FIELD(sr.status,'pending','approved','waiting_acceptance','assigned','price_proposed','price_accepted','in_progress','waiting_inspection','payment_requested','completed','paid'), sr.created_at DESC
";

try {
    $serviceRequestsStmt = $conn->query($serviceRequestsSql);
    $allServiceRequests = $serviceRequestsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allServiceRequests = [];
}

foreach($allServiceRequests as &$request) {
    $paymentStatus = $request['payment_status'] ?? '';
    $status = $request['status'];
    $request['awaiting_payment_upload'] = ($status === 'payment_requested' && ($paymentStatus === '' || $paymentStatus === 'pending'));
    $request['awaiting_admin_verification'] = ($request['payment_id'] && $paymentStatus === 'paid' && !empty($request['payment_proof']));
    $request['is_finished'] = (in_array($status, ['completed','paid']) && $paymentStatus === 'verified');
    $request['inspection_image_urls'] = formatMediaList($request['inspection_images'] ?? null, 'inspection');
    $request['homeowner_image_urls'] = formatMediaList($request['homeowner_images'] ?? null, 'homeowner_uploads');
    $request['payment_proof_url'] = resolveMediaPath($request['payment_proof'] ?? null, 'payments');
    $request['chat_snippets'] = getChatSnippets($conn, $request['homeowner_id'] ?? null, $request['technician_id'] ?? null);
    $request['inspection_summary'] = [
        'notes' => $request['inspection_notes'] ?? null,
        'findings' => $request['inspection_findings'] ?? null,
        'recommendations' => $request['inspection_recommendations'] ?? null,
        'materials_cost' => $request['materials_cost'] ?? null,
        'labor_cost' => $request['labor_cost'] ?? null,
        'estimated_cost' => $request['estimated_cost'] ?? null,
    ];
    if($request['awaiting_payment_upload'] || $request['awaiting_admin_verification']) {
        $paymentQueue[] = $request;
    }
    if($request['is_finished']) {
        $finishedTasks[] = $request;
    }
}
unset($request);

$serviceStats['total_requests'] = count($allServiceRequests);
$serviceStats['pending_requests'] = count(array_filter($allServiceRequests, function($req) {
    return in_array($req['status'], ['pending','approved','waiting_acceptance']);
}));

// Fetch technician review filter options and review data for admin oversight
try {
    $reviewTechnicianOptionsStmt = $conn->query("SELECT DISTINCT t.id, t.first_name, t.last_name, t.profession
                                                FROM ratings r
                                                JOIN users t ON r.technician_id = t.id
                                                ORDER BY t.first_name ASC, t.last_name ASC");
    $reviewTechnicianOptions = $reviewTechnicianOptionsStmt->fetchAll(PDO::FETCH_ASSOC);

    $reviewWhere = '';
    $reviewParams = [];
    if($reviewTechnicianFilter) {
        $reviewWhere = 'WHERE r.technician_id = ?';
        $reviewParams[] = $reviewTechnicianFilter;
    }

    $statsSql = "SELECT COALESCE(AVG(r.rating), 0) AS average_rating,
                        COUNT(*) AS total_reviews,
                        SUM(CASE WHEN r.rating >= 4 THEN 1 ELSE 0 END) AS positive_feedback,
                        SUM(CASE WHEN r.rating <= 2 THEN 1 ELSE 0 END) AS negative_feedback
                 FROM ratings r $reviewWhere";
    $statsStmt = $conn->prepare($statsSql);
    $statsStmt->execute($reviewParams);
    $adminReviewStats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: $adminReviewStats;

    $reviewsSql = "SELECT r.*, 
                          t.first_name AS technician_first_name,
                          t.last_name AS technician_last_name,
                          t.profession AS technician_profession,
                          h.first_name AS homeowner_first_name,
                          h.last_name AS homeowner_last_name,
                          DATE_FORMAT(r.created_at, '%b %e, %Y') AS formatted_date,
                          (SELECT sr.title 
                             FROM service_requests sr 
                             WHERE sr.technician_id = r.technician_id 
                               AND sr.homeowner_id = r.homeowner_id 
                             ORDER BY sr.updated_at DESC, sr.created_at DESC 
                             LIMIT 1) AS service_title
                   FROM ratings r
                   JOIN users t ON r.technician_id = t.id
                   JOIN users h ON r.homeowner_id = h.id
                   $reviewWhere
                   ORDER BY r.created_at DESC";
    $reviewsStmt = $conn->prepare($reviewsSql);
    $reviewsStmt->execute($reviewParams);
    $allReviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allReviews = [];
}

// FIXED: Enhanced message retrieval with fallback
try {
    // Try different method names that might exist in UserController
    if (method_exists($userController, 'getAllContactMessages')) {
        $allMessages = $userController->getAllContactMessages();
    } elseif (method_exists($userController, 'getAllMessages')) {
        $allMessages = $userController->getAllMessages();
    } else {
        // Fallback: direct database query
        $stmt = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");

        $allMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get unread messages count
    $unreadMessages = [];
    if (!empty($allMessages)) {
        foreach ($allMessages as $message) {
            if (isset($message['status']) && $message['status'] == 'unread') {
                $unreadMessages[] = $message;
            }
        }
    }
} catch (Exception $e) {
    $allMessages = [];
    $unreadMessages = [];
    error_log("Error fetching messages: " . $e->getMessage());
}

// Update stats with actual message counts
if (isset($stats)) {
    $stats['total_messages'] = count($allMessages);
    $stats['unread_messages'] = count($unreadMessages);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HomeFix Pro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .admin-dashboard {
            min-height: 100vh;
            background: #f8f9fa;
            padding: 80px 0 20px;
        }
        .admin-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .admin-actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .admin-actions .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .admin-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }

        /* Compact KPI overview strip */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin-bottom: 25px;
        }
        .kpi-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 14px 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            gap: 4px;
            border: 1px solid #edf2f7;
        }
        .kpi-label {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #718096;
            font-weight: 600;
        }
        .kpi-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2d3748;
        }
        .kpi-note {
            font-size: 0.75rem;
            color: #a0aec0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .stat-card:nth-child(1) .stat-icon { background: #3498db; }
        .stat-card:nth-child(2) .stat-icon { background: #e74c3c; }
        .stat-card:nth-child(3) .stat-icon { background: #f39c12; }
        .stat-card:nth-child(4) .stat-icon { background: #27ae60; }
        .stat-card:nth-child(5) .stat-icon { background: #9b59b6; }
        .stat-card:nth-child(6) .stat-icon { background: #1abc9c; }
        .stat-card:nth-child(7) .stat-icon { background: #e67e22; }
        .stat-card:nth-child(8) .stat-icon { background: #34495e; }
        .stat-card:nth-child(9) .stat-icon { background: #f39c12; }
        .stat-card:nth-child(10) .stat-icon { background: #3498db; }
        .admin-tabs {
            display: flex;
            background: white;
            border-radius: 15px;
            padding: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            flex-wrap: wrap;
            gap: 10px;
        }
        .tab-button {
            flex: 1;
            padding: 15px 25px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: #7f8c8d;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-width: 200px;
        }
        .tab-button.active {
            background: #4361ee;
            color: white;
        }
        .tab-button .badge {
            background: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .technicians-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
        }
        .technician-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-body {
            padding: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f8f9fa;
        }
        .card-actions {
            padding: 20px;
            background: #f8f9fa;
            display: flex;
            gap: 10px;
        }
        .status-form {
            display: flex;
            gap: 10px;
            flex: 1;
        }
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        .users-table th {
            background: #4361ee;
            color: white;
            padding: 15px;
            text-align: left;
        }
        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d1edff; color: #0c5460; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-unread { background: #e74c3c; color: white; }
        .status-read { background: #95a5a6; color: white; }
        .status-assigned { background: #d1ecf1; color: #0c5460; }
        .status-in_progress { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        .nav-links a.active {
            color: #4361ee;
            font-weight: 600;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .btn-view {
            background: #3498db;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .btn-view:hover {
            background: #2980b9;
        }

        /* Service Request Styles */
        .service-requests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
            gap: 25px;
        }
        .service-request-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .request-header {
            padding: 20px;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .request-title h3 {
            margin: 0 0 8px 0;
            color: #2c3e50;
            font-size: 1.2rem;
        }
        .service-type {
            background: #4361ee;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .request-meta {
            text-align: right;
        }
        .request-body {
            padding: 20px;
        }
        .request-details {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .detail-group {
            display: flex;
            gap: 15px;
        }
        .detail-group label {
            font-weight: 600;
            color: #2c3e50;
            min-width: 120px;
        }
        .detail-group span, .detail-group p {
            color: #495057;
            margin: 0;
        }
        .detail-group p {
            line-height: 1.5;
        }
        .request-actions {
            padding: 20px;
            background: #f8f9fa;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-primary { background: #4361ee; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        /* Payment Queue / Finished Tasks */
        .queue-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
            gap: 20px;
        }
        .queue-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .queue-card h4 {
            margin: 0;
            font-size: 1.1rem;
            color: #2c3e50;
        }
        .queue-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 0.9rem;
            color: #555;
        }
        .queue-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .status-chip {
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .status-chip.waiting-upload { background: #fff7e0; color: #b57600; }
        .status-chip.waiting-verify { background: #e6f7ff; color: #00588c; }
        .status-chip.finished { background: #e0f7e7; color: #0f8a4b; }
        .queue-details {
            font-size: 0.9rem;
            color: #444;
            line-height: 1.4;
        }
        .queue-details strong { color: #1f2d3d; }
        .queue-card .alert {
            margin: 0;
            padding: 10px 12px;
        }

        .finished-tasks-layout {
            display: grid;
            grid-template-columns: minmax(350px, 1.4fr) minmax(300px, 1fr);
            gap: 25px;
            align-items: start;
        }
        .finished-task-detail {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 24px;
        }
        .finished-task-detail h3 {
            margin: 0;
            color: #1f2d3d;
        }
        .finished-task-detail p {
            margin: 6px 0 0 0;
            color: #64748b;
        }
        .media-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .media-gallery img,
        .media-gallery video {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .chat-snippets {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 220px;
            overflow-y: auto;
        }
        .chat-snippet {
            padding-bottom: 10px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .chat-snippet:last-child {
            border-bottom: none;
        }
        .chat-snippet strong {
            display: block;
            color: #0f172a;
        }
        .payment-proof-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 10px;
            background: #eef2ff;
            color: #4338ca;
            text-decoration: none;
            font-weight: 600;
        }
        .detail-sections {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .detail-block h4 {
            margin: 0 0 12px;
            color: #1f2d3d;
            font-size: 1rem;
        }
        .timeline-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .timeline-entry {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .timeline-entry:last-child {
            border-bottom: none;
        }
        .timeline-entry span {
            color: #475569;
        }
        .detail-grid-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 12px;
        }
        .detail-item-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px;
        }
        .detail-item-card label {
            display: block;
            font-size: 0.75rem;
            color: #94a3b8;
            letter-spacing: .05em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .detail-item-card strong {
            color: #0f172a;
            font-size: 0.95rem;
        }
        @media (max-width: 1100px) {
            .finished-tasks-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Message Styles */
        .message-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .message-header {
            padding: 20px;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .message-unread {
            border-left: 4px solid #e74c3c;
        }
        .message-body {
            padding: 20px;
        }
        .message-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        .message-content {
            line-height: 1.6;
            color: #2c3e50;
        }
        .message-actions {
            padding: 15px 20px;
            background: #f8f9fa;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        /* Feedback Styles */
        .feedback-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .feedback-header {
            padding: 20px;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }
        .feedback-meta {
            flex: 1;
        }
        .technician-info, .homeowner-info, .service-info {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .rating-display {
            text-align: right;
            min-width: 150px;
        }
        .stars {
            margin-bottom: 5px;
        }
        .stars .fa-star {
            color: #ddd;
            font-size: 1rem;
        }
        .stars .fa-star.active {
            color: #f39c12;
        }
        .rating-value {
            font-weight: 600;
            color: #2c3e50;
            display: block;
        }
        .feedback-date {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-top: 5px;
        }
        .feedback-body {
            padding: 20px;
        }
        .feedback-comment, .positive-points, .improvement-suggestions {
            margin-bottom: 15px;
        }
        .feedback-comment:last-child, .positive-points:last-child, .improvement-suggestions:last-child {
            margin-bottom: 0;
        }
        .feedback-comment p, .positive-points p, .improvement-suggestions p {
            margin: 8px 0 0 0;
            line-height: 1.5;
            color: #495057;
        }
        .feedback-actions {
            padding: 15px 20px;
            background: #f8f9fa;
            display: flex;
            justify-content: flex-end;
        }
        .profession {
            background: #4361ee;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .modal-header {
            padding: 20px 30px;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            color: #2c3e50;
        }

        .close {
            color: #7f8c8d;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #2c3e50;
        }

        .modal-body {
            padding: 30px;
        }

        /* User Details Styles */
        .user-details {
            font-size: 14px;
        }

        .detail-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ecf0f1;
        }

        .detail-section:last-child {
            border-bottom: none;
        }

        .detail-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .detail-item.full-width {
            grid-column: 1 / -1;
        }

        .detail-item label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .detail-item span {
            color: #7f8c8d;
        }

        .document-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .status-waiting_acceptance { background: #fff3cd; color: #856404; }
        .document-link {
            color: #4361ee;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            padding: 8px 12px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .document-link:hover {
            background: #f8f9fa;
            text-decoration: none;
        }

        .detail-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }

        .role-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .role-admin { background: #4361ee; color: white; }
        .role-technician { background: #e74c3c; color: white; }
        .role-homeowner { background: #27ae60; color: white; }

        .profession {
            background: #4361ee;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .stats-grid, .technicians-grid, .service-requests-grid {
                grid-template-columns: 1fr;
            }
            .admin-tabs {
                flex-direction: column;
            }
            .detail-grid {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            .request-header {
                flex-direction: column;
                gap: 15px;
            }
            .request-meta {
                text-align: left;
            }
            .feedback-header {
                flex-direction: column;
                gap: 15px;
            }
            .rating-display {
                text-align: left;
                width: 100%;
            }
        }

        .finished-task-card.active {
            border: 2px solid #4361ee;
            box-shadow: 0 10px 25px rgba(67,97,238,0.2);
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <div class="container">
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                <p>Manage users, technicians, service requests, and messages</p>
            </div>

            <div class="admin-actions">
                <a href="technicians_gain.php" class="btn btn-primary">
                    <i class="fas fa-coins"></i> Technicians Gain
                </a>
                <a href="../../logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <?php if(is_string($message)) { echo $message; } ?>

            <!-- Quick KPI Overview -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <span class="kpi-label">All Technicians</span>
                    <span class="kpi-value"><?php echo count($allTechnicians); ?></span>
                    <span class="kpi-note">Registered service providers</span>
                </div>
                <div class="kpi-card">
                    <span class="kpi-label">All Users</span>
                    <span class="kpi-value"><?php echo count($allUsers); ?></span>
                    <span class="kpi-note">Total accounts in the system</span>
                </div>
                <div class="kpi-card">
                    <span class="kpi-label">Payment Queue</span>
                    <span class="kpi-value"><?php echo count($paymentQueue); ?></span>
                    <span class="kpi-note">Payments awaiting action</span>
                </div>
                <div class="kpi-card">
                    <span class="kpi-label">Finished Tasks</span>
                    <span class="kpi-value"><?php echo count($finishedTasks); ?></span>
                    <span class="kpi-note">Verified & closed jobs</span>
                </div>
                <div class="kpi-card">
                    <span class="kpi-label">Messages</span>
                    <span class="kpi-value"><?php echo $stats['total_messages'] ?? 0; ?></span>
                    <span class="kpi-note">All contact messages</span>
                </div>
                <div class="kpi-card">
                    <span class="kpi-label">Customer Reviews</span>
                    <span class="kpi-value"><?php echo $adminReviewStats['total_reviews'] ?? 0; ?></span>
                    <span class="kpi-note">Feedback from homeowners</span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_technicians']; ?></h3>
                        <p>Technicians</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['pending_technicians']; ?></h3>
                        <p>Pending Technicians</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_homeowners']; ?></h3>
                        <p>Homeowners</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $serviceStats['total_requests'] ?? 0; ?></h3>
                        <p>Total Requests</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $serviceStats['pending_requests'] ?? 0; ?></h3>
                        <p>Pending Requests</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_messages']; ?></h3>
                        <p>Total Messages</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['unread_messages']; ?></h3>
                        <p>Unread Messages</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $adminReviewStats['average_rating'] ?? '0.0'; ?>/5</h3>
                        <p>Avg Rating</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $adminReviewStats['total_reviews'] ?? 0; ?></h3>
                        <p>Total Reviews</p>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="admin-tabs">
                <button class="tab-button active" onclick="openTab('technicians')">
                    All Technicians
                </button>
                <button class="tab-button" onclick="openTab('users')">
                    All Users
                </button>
                <button class="tab-button" onclick="openTab('payment-queue')">
                    Payment Queue
                    <?php if(count($paymentQueue) > 0): ?>
                        <span class="badge"><?php echo count($paymentQueue); ?></span>
                    <?php endif; ?>
                </button>
                <button class="tab-button" onclick="openTab('finished-tasks')">
                    Finished Tasks
                    <?php if(count($finishedTasks) > 0): ?>
                        <span class="badge"><?php echo count($finishedTasks); ?></span>
                    <?php endif; ?>
                </button>
                <button class="tab-button" onclick="openTab('messages')">
                    Messages 
                    <?php if(count($unreadMessages) > 0): ?>
                        <span class="badge"><?php echo count($unreadMessages); ?></span>
                    <?php endif; ?>
                </button>
                <button class="tab-button" onclick="openTab('reviews')">
                    Customer Reviews 
                    <?php if(count($allReviews) > 0): ?>
                        <span class="badge"><?php echo count($allReviews); ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- Payment Queue -->
            <div id="payment-queue" class="tab-content">
                <h2>Payments Awaiting Action</h2>
                <?php if(count($paymentQueue) > 0): ?>
                    <div class="queue-grid">
                        <?php foreach($paymentQueue as $task): ?>
                            <div class="queue-card">
                                <div class="queue-meta">
                                    <span><strong>Request:</strong> <?php echo htmlspecialchars($task['title']); ?></span>
                                    <span><strong>Homeowner:</strong> <?php echo htmlspecialchars($task['homeowner_first_name'] . ' ' . $task['homeowner_last_name']); ?></span>
                                    <?php if($task['technician_first_name']): ?>
                                        <span><strong>Technician:</strong> <?php echo htmlspecialchars($task['technician_first_name'] . ' ' . $task['technician_last_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="queue-details">
                                    <p><strong>Status:</strong> <?php echo ucfirst($task['status']); ?></p>
                                    <p><strong>Amount:</strong> ETB <?php echo number_format($task['payment_amount'] ?: $task['estimated_cost'] ?: 0, 2); ?></p>
                                    <?php if($task['awaiting_payment_upload']): ?>
                                        <span class="status-chip waiting-upload"><i class="fas fa-upload"></i> Awaiting homeowner payment upload</span>
                                        <p>Technician payment details:<br>
                                            TeleBirr: <?php echo htmlspecialchars($task['technician_tele_birr'] ?? 'N/A'); ?>,<br>
                                            Bank: <?php echo htmlspecialchars($task['technician_bank_account'] ?? 'N/A'); ?>
                                        </p>
                                    <?php elseif($task['awaiting_admin_verification']): ?>
                                        <span class="status-chip waiting-verify"><i class="fas fa-check-circle"></i> Awaiting admin verification</span>
                                        <?php if(!empty($task['payment_proof'])): ?>
                                            <a class="receipt-link" href="../../assets/uploads/payments/<?php echo htmlspecialchars($task['payment_proof']); ?>" target="_blank">View receipt</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <?php if($task['awaiting_admin_verification'] && !empty($task['payment_id'])): ?>
                                    <div class="queue-actions">
                                        <form method="POST">
                                            <input type="hidden" name="payment_id" value="<?php echo $task['payment_id']; ?>">
                                            <button type="submit" name="verify_payment" class="btn btn-success btn-sm">
                                                <i class="fas fa-badge-check"></i> Verify Payment
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <h3>No Pending Payments</h3>
                        <p>All payments have been handled.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Finished Tasks -->
            <div id="finished-tasks" class="tab-content">
                <h2>Finished Tasks</h2>
                <?php if(count($finishedTasks) > 0): ?>
                    <div class="finished-tasks-layout">
                        <div class="queue-grid" id="finishedTasksList">
                            <?php foreach($finishedTasks as $task): 
                                $timeline = [
                                    'Requested' => $task['created_at'] ?? null,
                                    'Assigned' => $task['accepted_at'] ?? $task['assigned_at'] ?? null,
                                    'Work Started' => $task['work_started_at'] ?? null,
                                    'Work Completed' => $task['work_completed_at'] ?? null,
                                    'Payment Requested' => $task['payment_requested_at'] ?? null,
                                    'Payment Paid' => $task['paid_at'] ?? $task['payment_received_at'] ?? null,
                                    'Technician Confirmed' => $task['technician_confirmed_at'] ?? null,
                                    'Admin Verified' => $task['verified_at'] ?? null,
                                ];
                            ?>
                                <div class="queue-card finished-task-card" 
                                     data-task='<?php echo json_encode([
                                         'title' => $task['title'],
                                         'homeowner' => trim(($task['homeowner_first_name'] ?? '') . ' ' . ($task['homeowner_last_name'] ?? '')),
                                         'homeowner_phone' => $task['homeowner_phone'] ?? null,
                                         'homeowner_email' => $task['homeowner_email'] ?? null,
                                         'technician' => trim(($task['technician_first_name'] ?? '') . ' ' . ($task['technician_last_name'] ?? '')),
                                         'technician_profession' => $task['technician_profession'] ?? null,
                                         'amount' => $task['payment_amount'] ?? $task['estimated_cost'] ?? 0,
                                         'payment_method' => $task['payment_method'] ?? null,
                                         'payment_status' => $task['payment_status'] ?? null,
                                         'transaction_id' => $task['transaction_id'] ?? null,
                                         'timeline' => $timeline,
                                         'request_id' => $task['id'] ?? null,
                                         'reference' => $task['payment_id'] ?? null,
                                         'inspection_media' => $task['inspection_image_urls'] ?? [],
                                         'homeowner_media' => $task['homeowner_image_urls'] ?? [],
                                         'payment_proof' => $task['payment_proof_url'] ?? null,
                                         'chat_snippets' => $task['chat_snippets'] ?? [],
                                         'inspection_summary' => $task['inspection_summary'] ?? [],
                                     ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'>
                                    <div class="queue-meta">
                                        <span><strong>Request:</strong> <?php echo htmlspecialchars($task['title']); ?></span>
                                        <span><strong>Homeowner:</strong> <?php echo htmlspecialchars($task['homeowner_first_name'] . ' ' . $task['homeowner_last_name']); ?></span>
                                        <?php if($task['technician_first_name']): ?>
                                            <span><strong>Technician:</strong> <?php echo htmlspecialchars($task['technician_first_name'] . ' ' . $task['technician_last_name']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="queue-details">
                                        <span class="status-chip finished"><i class="fas fa-check"></i> Payment verified & task closed</span>
                                        <p><strong>Verified at:</strong> <?php echo date('M j, Y g:i A', strtotime($task['verified_at'] ?? $task['updated_at'])); ?></p>
                                        <?php if($task['payment_method']): ?>
                                            <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_',' ', $task['payment_method'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="finished-task-detail" id="finishedTaskDetail">
                            <h3>Select a task</h3>
                            <p>Tap any finished card to review the full lifecycle, timestamps, and payment proof.</p>
                            <div class="detail-sections" style="display:none;">
                                <div class="detail-block">
                                    <h4>Timeline</h4>
                                    <ul class="timeline-list" id="taskTimeline"></ul>
                                </div>
                                <div class="detail-block">
                                    <h4>Evidence & Uploads</h4>
                                    <div id="taskMediaGalleries"></div>
                                    <div id="taskPaymentProof" style="margin-top:12px;"></div>
                                </div>
                                <div class="detail-block">
                                    <h4>Participants</h4>
                                    <div class="detail-grid-info" id="taskParticipants"></div>
                                </div>
                                <div class="detail-block">
                                    <h4>Payment Details</h4>
                                    <div class="detail-grid-info" id="taskPaymentInfo"></div>
                                </div>
                                <div class="detail-block">
                                    <h4>Conversation Snippets</h4>
                                    <div class="chat-snippets" id="taskChatSnippets"></div>
                                </div>
                                <div class="detail-block" id="inspectionSummaryBlock" style="display:none;">
                                    <h4>Inspection Summary</h4>
                                    <div class="detail-grid-info" id="inspectionSummaryInfo"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-check"></i>
                        <h3>No Finished Tasks Yet</h3>
                    </div>
                <?php endif; ?>
            </div>

            <!-- All Technicians -->
            <div id="technicians" class="tab-content">
                <h2>All Technicians</h2>
                <?php if(count($allTechnicians) > 0): ?>
                    <div class="table-container">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Profession</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($allTechnicians as $tech): ?>
                                    <tr>
                                        <td><?php echo $tech['first_name'] . ' ' . $tech['last_name']; ?></td>
                                        <td><?php echo $tech['email']; ?></td>
                                        <td><?php echo $tech['profession']; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $tech['status']; ?>">
                                                <?php echo ucfirst($tech['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="btn-view" onclick="viewUserDetails(<?php echo $tech['id']; ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <form method="POST" class="inline-form">
                                                    <input type="hidden" name="user_id" value="<?php echo $tech['id']; ?>">
                                                    <select name="status" class="form-control small" onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $tech['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="approved" <?php echo $tech['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                        <option value="rejected" <?php echo $tech['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                    </select>
                                                    <input type="hidden" name="update_status">
                                                </form>
                                                <form method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    <input type="hidden" name="user_id" value="<?php echo $tech['id']; ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-tools"></i>
                        <h3>No Technicians Found</h3>
                    </div>
                <?php endif; ?>
            </div>

            <!-- All Users -->
            <div id="users" class="tab-content">
                <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 15px; gap: 10px; flex-wrap: wrap;">
                    <h2 style="margin:0;">All Users</h2>
                </div>
                <?php if(count($allUsers) > 0): ?>
                    <div class="table-container">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($allUsers as $user): ?>
                                    <tr>
                                        <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td>
                                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $user['status']; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="btn-view" onclick="viewUserDetails(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <?php if($user['role'] == 'technician'): ?>
                                                    <form method="POST" class="inline-form">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <select name="status" class="form-control small" onchange="this.form.submit()">
                                                            <option value="pending" <?php echo $user['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="approved" <?php echo $user['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                            <option value="rejected" <?php echo $user['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                        </select>
                                                        <input type="hidden" name="update_status">
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Users Found</h3>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Messages -->
            <div id="messages" class="tab-content">
                <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 15px; gap: 10px; flex-wrap: wrap;">
                    <h2 style="margin:0;">Contact Messages</h2>
                    <?php if(!empty($unreadMessages)): ?>
                        <form method="POST" class="inline-form">
                            <button type="submit" name="mark_all_read" class="btn btn-primary btn-sm">
                                <i class="fas fa-check-double"></i> Mark all as read
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if(!empty($allMessages)): ?>
                        <form method="POST" id="bulkMessagesForm" class="inline-form" onsubmit="return confirm('Are you sure you want to delete all selected messages?');">
                            <input type="hidden" name="delete_selected" value="1">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Delete selected
                            </button>
                        </form>
                    <?php endif; ?>
                    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                        <?php if(!empty($unreadMessages)): ?>
                            <form method="POST" class="inline-form">
                                <button type="submit" name="mark_all_read" class="btn btn-primary btn-sm">
                                    <i class="fas fa-check-double"></i> Mark all as read
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php if(!empty($allMessages)): ?>
                            <form method="POST" id="bulkMessagesForm" class="inline-form" onsubmit="return confirm('Are you sure you want to delete all selected messages?');">
                                <input type="hidden" name="delete_selected" value="1">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Delete selected
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if(count($allMessages) > 0): ?>
                    <div style="display:flex; justify-content:flex-end; align-items:center; margin-bottom:8px; gap:6px; font-size:0.85rem;">
                        <input type="checkbox" id="selectAllMessages" onclick="toggleSelectAllMessages(this)">
                        <label for="selectAllMessages" style="margin:0; cursor:pointer;">Select all</label>
                    </div>
                    <div class="messages-list">
                        <?php foreach($allMessages as $msg): ?>
                            <div class="message-card <?php echo (isset($msg['status']) && $msg['status'] == 'unread') ? 'message-unread' : ''; ?>">
                                <div class="message-header">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <input type="checkbox" name="selected_messages[]" value="<?php echo $msg['id']; ?>" class="message-select-checkbox" form="bulkMessagesForm">
                                        <div>
                                            <h3><?php echo htmlspecialchars($msg['subject'] ?? 'No Subject'); ?></h3>
                                            <div class="message-meta">
                                                <span><strong>From:</strong> <?php echo htmlspecialchars($msg['name'] ?? 'Unknown'); ?></span>
                                                <span><strong>Email:</strong> <?php echo htmlspecialchars($msg['email'] ?? 'No Email'); ?></span>
                                                <span><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($msg['created_at'] ?? 'now')); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="status-badge status-<?php echo $msg['status'] ?? 'read'; ?>">
                                        <?php echo ucfirst($msg['status'] ?? 'read'); ?>
                                    </span>
                                </div>
                                <div class="message-body">
                                    <div class="message-content">
                                        <?php echo nl2br(htmlspecialchars($msg['message'] ?? 'No message content')); ?>
                                    </div>
                                </div>
                                <div class="message-actions">
                                    <?php if(isset($msg['status']) && $msg['status'] == 'unread'): ?>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                            <button type="submit" name="mark_as_read" class="btn btn-primary">
                                                <i class="fas fa-check"></i> Mark as Read
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this message?')">
                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" name="delete_message" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top:10px; display:flex; justify-content:flex-end;">
                        <button type="submit" form="bulkMessagesForm" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete all selected messages?');">
                            <i class="fas fa-trash"></i> Delete selected
                        </button>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-envelope"></i>
                        <h3>No Messages Found</h3>
                        <p>No contact messages have been received yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Reviews Tab -->
            <div id="reviews" class="tab-content">
                <h2>Customer Reviews & Ratings</h2>
                
                <!-- Review Statistics -->
                <div class="stats-grid" style="margin-bottom: 30px;">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #f39c12;">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($adminReviewStats['average_rating'] ?? 0, 1); ?>/5</h3>
                            <p>Average Rating</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #3498db;">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $adminReviewStats['total_reviews'] ?? 0; ?></h3>
                            <p>Total Reviews</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #27ae60;">
                            <i class="fas fa-thumbs-up"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $adminReviewStats['positive_feedback'] ?? 0; ?></h3>
                            <p>Positive (4-5 stars)</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #e74c3c;">
                            <i class="fas fa-thumbs-down"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $adminReviewStats['negative_feedback'] ?? 0; ?></h3>
                            <p>Negative (1-2 stars)</p>
                        </div>
                    </div>
                </div>

                <?php if(count($allReviews) > 0): ?>
                    <div class="feedback-list">
                        <?php foreach($allReviews as $review): ?>
                            <div class="feedback-card">
                                <div class="feedback-header">
                                    <div class="feedback-meta">
                                        <div class="technician-info">
                                            <strong>Technician:</strong>
                                            <?php echo htmlspecialchars($review['technician_first_name'] . ' ' . $review['technician_last_name']); ?>
                                            <span class="profession"><?php echo htmlspecialchars($review['technician_profession']); ?></span>
                                        </div>
                                        <div class="homeowner-info">
                                            <strong>From Homeowner:</strong>
                                            <?php echo htmlspecialchars($review['homeowner_first_name'] . ' ' . $review['homeowner_last_name']); ?>
                                        </div>
                                        <?php if(!empty($review['service_title'])): ?>
                                        <div class="service-info">
                                            <strong>Service:</strong>
                                            <?php echo htmlspecialchars($review['service_title']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="rating-display">
                                        <div class="stars">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-value"><?php echo $review['rating']; ?>/5</span>
                                        <div class="feedback-date">
                                            <?php echo $review['formatted_date']; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="feedback-body">
                                    <?php if(!empty($review['comment'])): ?>
                                    <div class="feedback-comment">
                                        <strong>Comment:</strong>
                                        <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="feedback-actions">
                                    <form method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this review?')">
                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                        <button type="submit" name="delete_review" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Delete Review
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <h3>No Reviews Yet</h3>
                        <p>No customer reviews have been submitted yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>User Details</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body" id="userDetails">
                <!-- User details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Assign Technician Modal -->
    <div id="assignTechnicianModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Assign Technician</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" id="assignTechnicianForm">
                    <input type="hidden" name="request_id" id="assignRequestId">
                    <div class="form-group">
                        <label>Select Technician:</label>
                        <select name="technician_id" class="form-control" required>
                            <option value="">Select a Technician</option>
                            <?php foreach($allTechnicians as $tech): ?>
                                <?php if($tech['status'] == 'approved'): ?>
                                    <option value="<?php echo $tech['id']; ?>">
                                        <?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name'] . ' - ' . $tech['profession']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="assign_technician" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> Assign Technician
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // ALL JAVASCRIPT REMAINS EXACTLY THE SAME
    function openTab(tabName) {
        // Hide all tab contents
        var tabContents = document.getElementsByClassName('tab-content');
        for (var i = 0; i < tabContents.length; i++) {
            tabContents[i].classList.remove('active');
        }
        
        // Remove active class from all tab buttons
        var tabButtons = document.getElementsByClassName('tab-button');
        for (var i = 0; i < tabButtons.length; i++) {
            tabButtons[i].classList.remove('active');
        }
        
        // Show the specific tab content and activate the button
        document.getElementById(tabName).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    function viewUserDetails(userId) {
        // Show loading
        document.getElementById('userDetails').innerHTML = '<div style="text-align: center; padding: 40px;">Loading user details...</div>';
        document.getElementById('userModal').style.display = 'block';
        
        // Load user details via AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_user_details.php?id=' + userId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('userDetails').innerHTML = xhr.responseText;
            } else {
                document.getElementById('userDetails').innerHTML = '<div class="error">Error loading user details.</div>';
            }
        };
        xhr.onerror = function() {
            document.getElementById('userDetails').innerHTML = '<div class="error">Error loading user details.</div>';
        };
        xhr.send();
    }

    function showAssignTechnician(requestId) {
        document.getElementById('assignRequestId').value = requestId;
        document.getElementById('assignTechnicianModal').style.display = 'block';
    }

    function toggleSelectAllMessages(source) {
        var checkboxes = document.getElementsByClassName('message-select-checkbox');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = source.checked;
        }
    }

    function toggleSelectAllUsers(source) {
        var checkboxes = document.getElementsByClassName('user-select-checkbox');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = source.checked;
        }
    }

    // Modal functionality
    var modals = document.getElementsByClassName('modal');
    var closeButtons = document.getElementsByClassName('close');

    for (var i = 0; i < closeButtons.length; i++) {
        closeButtons[i].onclick = function() {
            for (var j = 0; j < modals.length; j++) {
                modals[j].style.display = 'none';
            }
        }
    }

    window.onclick = function(event) {
        for (var i = 0; i < modals.length; i++) {
            if (event.target == modals[i]) {
                modals[i].style.display = 'none';
            }
        }
    }

    // Finished task detail interactions
    const finishedCards = document.querySelectorAll('.finished-task-card');
    const finishedDetail = document.getElementById('finishedTaskDetail');
    const detailSections = finishedDetail ? finishedDetail.querySelector('.detail-sections') : null;
    const timelineList = document.getElementById('taskTimeline');
    const participantsContainer = document.getElementById('taskParticipants');
    const paymentContainer = document.getElementById('taskPaymentInfo');
    const mediaContainer = document.getElementById('taskMediaGalleries');
    const paymentProofContainer = document.getElementById('taskPaymentProof');
    const chatSnippetsContainer = document.getElementById('taskChatSnippets');
    const inspectionSummaryBlock = document.getElementById('inspectionSummaryBlock');
    const inspectionSummaryInfo = document.getElementById('inspectionSummaryInfo');
    const detailTitle = finishedDetail ? finishedDetail.querySelector('h3') : null;
    const detailSubtitle = finishedDetail ? finishedDetail.querySelector('p') : null;

    const formatDateTime = (value) => {
        if (!value) return '—';
        const date = new Date(value);
        if (isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleString(undefined, {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    };

    const renderDetailCards = (container, items) => {
        container.innerHTML = items.map(item => `
            <div class="detail-item-card">
                <label>${item.label}</label>
                <strong>${item.value || '—'}</strong>
            </div>
        `).join('');
    };

    const renderTimeline = (timeline) => {
        if (!timelineList) return;
        const rows = Object.entries(timeline || {}).filter(([_, value]) => !!value);
        if (!rows.length) {
            timelineList.innerHTML = '<li>No timeline data available.</li>';
            return;
        }
        timelineList.innerHTML = rows.map(([label, value]) => `
            <li class="timeline-entry">
                <strong>${label}</strong>
                <span>${formatDateTime(value)}</span>
            </li>
        `).join('');
    };

    const renderMedia = (inspectionMedia = [], homeownerMedia = []) => {
        if (!mediaContainer) return;
        const sections = [];
        const buildGallery = (label, items) => `
            <div style="margin-bottom:14px;">
                <strong>${label}</strong>
                <div class="media-gallery">
                    ${items.map(item => {
                        const isImage = /(\.jpg|\.jpeg|\.png|\.gif|\.webp)$/i.test(item);
                        if(isImage) {
                            return `<img src="${item}" alt="${label} evidence">`;
                        }
                        const isVideo = /(\.mp4|\.mov|\.webm)$/i.test(item);
                        if(isVideo) {
                            return `<video src="${item}" controls></video>`;
                        }
                        return `<a href="${item}" target="_blank">View file</a>`;
                    }).join('')}
                </div>
            </div>`;
        if(inspectionMedia.length) {
            sections.push(buildGallery('Technician Uploads', inspectionMedia));
        }
        if(homeownerMedia.length) {
            sections.push(buildGallery('Homeowner Uploads', homeownerMedia));
        }
        mediaContainer.innerHTML = sections.length ? sections.join('') : '<p>No evidence uploaded.</p>';
    };

    const renderPaymentProof = (proofUrl) => {
        if (!paymentProofContainer) return;
        if(proofUrl) {
            paymentProofContainer.innerHTML = `<a class="payment-proof-link" href="${proofUrl}" target="_blank"><i class="fas fa-receipt"></i> View Payment Receipt</a>`;
        } else {
            paymentProofContainer.innerHTML = '<p>No payment proof uploaded.</p>';
        }
    };

    const renderChats = (snippets = []) => {
        if (!chatSnippetsContainer) return;
        if(!snippets.length) {
            chatSnippetsContainer.innerHTML = '<p>No chat history for this task.</p>';
            return;
        }
        chatSnippetsContainer.innerHTML = snippets.map(snippet => `
            <div class="chat-snippet">
                <strong>${snippet.sender_name || 'User'}${snippet.sender_role ? ' (' + snippet.sender_role + ')' : ''}</strong>
                <div>${snippet.message || ''}</div>
                <small>${formatDateTime(snippet.created_at)}</small>
            </div>
        `).join('');
    };

    const renderInspectionSummary = (summary = {}) => {
        if (!inspectionSummaryInfo || !inspectionSummaryBlock) return;
        const items = [];
        if(summary.findings) {
            items.push({ label: 'Findings', value: summary.findings });
        }
        if(summary.recommendations) {
            items.push({ label: 'Recommendations', value: summary.recommendations });
        }
        if(summary.notes) {
            items.push({ label: 'Notes', value: summary.notes });
        }
        if(summary.materials_cost) {
            items.push({ label: 'Materials Cost', value: `ETB ${Number(summary.materials_cost).toLocaleString()}` });
        }
        if(summary.labor_cost) {
            items.push({ label: 'Labor Cost', value: `ETB ${Number(summary.labor_cost).toLocaleString()}` });
        }
        if(summary.estimated_cost) {
            items.push({ label: 'Estimated Total', value: `ETB ${Number(summary.estimated_cost).toLocaleString()}` });
        }
        if(items.length) {
            inspectionSummaryBlock.style.display = '';
            renderDetailCards(inspectionSummaryInfo, items);
        } else {
            inspectionSummaryBlock.style.display = 'none';
            inspectionSummaryInfo.innerHTML = '';
        }
    };

    finishedCards.forEach(card => {
        card.addEventListener('click', () => {
            finishedCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');

            const dataRaw = card.dataset.task || '{}';
            let data;
            try {
                data = JSON.parse(dataRaw);
            } catch (err) {
                data = {};
            }

            if (detailTitle) {
                detailTitle.textContent = data.title || 'Task details';
            }
            if (detailSubtitle) {
                detailSubtitle.textContent = `${data.homeowner || 'Homeowner'} • ${data.technician || 'Technician'}${data.technician_profession ? ' (' + data.technician_profession + ')' : ''}`;
            }
            if (detailSections) {
                detailSections.style.display = '';
            }

            renderTimeline(data.timeline || {});
            renderMedia(data.inspection_media || [], data.homeowner_media || []);
            renderPaymentProof(data.payment_proof);
            renderDetailCards(participantsContainer, [
                { label: 'Homeowner', value: data.homeowner },
                { label: 'Homeowner Phone', value: data.homeowner_phone },
                { label: 'Homeowner Email', value: data.homeowner_email },
                { label: 'Technician', value: data.technician },
                { label: 'Profession', value: data.technician_profession },
            ]);
            renderDetailCards(paymentContainer, [
                { label: 'Amount', value: data.amount ? `ETB ${Number(data.amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : null },
                { label: 'Payment Method', value: data.payment_method ? data.payment_method.replace('_',' ') : null },
                { label: 'Payment Status', value: data.payment_status ? data.payment_status.toUpperCase() : null },
                { label: 'Transaction ID', value: data.transaction_id },
                { label: 'Request ID', value: data.request_id },
                { label: 'Payment Reference', value: data.reference }
            ]);
            renderChats(data.chat_snippets || []);
            renderInspectionSummary(data.inspection_summary || {});
        });
    });
    </script>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 HomeFix Pro. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>