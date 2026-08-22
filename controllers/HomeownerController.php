<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/NotificationHelper.php';

class HomeownerController {
    private $conn;
    private $user_id;
    private $baseDir;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->user_id = $_SESSION['user_id'] ?? null;
        $this->baseDir = realpath(__DIR__ . '/..');
        if($this->baseDir === false) {
            $this->baseDir = __DIR__ . '/..';
        }
    }
    
    public function getDashboardStats() {
        $details = $this->getDetailedStats();

        return [
            'total_requests'    => $details['services']['total'],
            'pending_requests'  => $details['services']['pending'],
            'active_requests'   => $details['services']['active'],
            'completed_requests'=> $details['services']['completed'],
            'cancelled_requests'=> $details['services']['cancelled'],
            'unread_messages'   => $details['messages']['unread'],
            'total_payments'    => $details['payments']['total_payments'],
            'pending_payments'  => $details['payments']['pending_payments'],
            'verified_payments' => $details['payments']['verified_payments'],
            'total_amount_paid' => $details['payments']['total_amount_paid'],
            'success_rate'      => $details['services']['success_rate']
        ];
    }

    public function getDetailedStats() {
        $serviceStats = [
            'total'      => 0,
            'pending'    => 0,
            'active'     => 0,
            'completed'  => 0,
            'cancelled'  => 0,
            'success_rate' => 0
        ];

        $serviceQuery = "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status IN ('pending','approved','waiting_acceptance') THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status IN ('waiting_inspection','assigned','price_proposed','price_accepted','in_progress','payment_requested') THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status IN ('completed','paid') THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN status IN ('rejected','price_rejected','cancelled') THEN 1 ELSE 0 END) AS cancelled
            FROM service_requests
            WHERE homeowner_id = ?";
        $stmt = $this->conn->prepare($serviceQuery);
        $stmt->execute([$this->user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        foreach ($serviceStats as $key => $default) {
            if ($key === 'success_rate') {
                continue;
            }
            $serviceStats[$key] = (int)($row[$key] ?? 0);
        }
        $serviceStats['success_rate'] = $serviceStats['total'] > 0
            ? round(($serviceStats['completed'] / $serviceStats['total']) * 100, 2)
            : 0;

        $paymentStats = [
            'total_payments'    => 0,
            'pending_payments'  => 0,
            'verified_payments' => 0,
            'total_amount_paid' => 0.0
        ];
        $paymentQuery = "SELECT 
                COUNT(*) AS total_payments,
                SUM(CASE WHEN payment_status IN ('pending','paid') THEN 1 ELSE 0 END) AS pending_payments,
                SUM(CASE WHEN payment_status = 'verified' THEN 1 ELSE 0 END) AS verified_payments,
                SUM(CASE WHEN payment_status IN ('paid','verified') THEN amount ELSE 0 END) AS total_amount_paid
            FROM payments
            WHERE homeowner_id = ?";
        $stmt = $this->conn->prepare($paymentQuery);
        $stmt->execute([$this->user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        foreach ($paymentStats as $key => $default) {
            $paymentStats[$key] = $key === 'total_amount_paid'
                ? (float)($row[$key] ?? 0)
                : (int)($row[$key] ?? 0);
        }

        $messageStats = [
            'total'  => 0,
            'unread' => 0
        ];
        $messageQuery = "SELECT 
                SUM(CASE WHEN receiver_id = ? THEN 1 ELSE 0 END) AS received,
                SUM(CASE WHEN sender_id = ? THEN 1 ELSE 0 END) AS sent,
                SUM(CASE WHEN receiver_id = ? AND COALESCE(is_read,0) = 0 THEN 1 ELSE 0 END) AS unread
            FROM chat_messages
            WHERE receiver_id = ? OR sender_id = ?";
        $stmt = $this->conn->prepare($messageQuery);
        $stmt->execute([$this->user_id, $this->user_id, $this->user_id, $this->user_id, $this->user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $totalMessages = (int)($row['received'] ?? 0) + (int)($row['sent'] ?? 0);
        $messageStats['total'] = $totalMessages;
        $messageStats['unread'] = (int)($row['unread'] ?? 0);

        return [
            'services' => $serviceStats,
            'payments' => $paymentStats,
            'messages' => $messageStats
        ];
    }

    public function getNotifications($limit = 5) {
        $limit = max(1, (int)$limit);
        $query = "SELECT id, title, message, type, related_id, action_url, created_at, COALESCE(is_read, 0) AS is_read
                  FROM notifications
                  WHERE user_id = ?
                  ORDER BY created_at DESC
                  LIMIT {$limit}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadNotificationCount() {
        $query = "SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND COALESCE(is_read, 0) = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    }

    public function getRecentActivity($limit = 3) {
        $limit = max(1, (int)$limit);
        $events = [];

        // Recent service request updates
        $requestLimit = $limit * 2;
        $requestQuery = "SELECT id, title, status, COALESCE(updated_at, created_at) AS activity_time
                          FROM service_requests
                          WHERE homeowner_id = ?
                          ORDER BY COALESCE(updated_at, created_at) DESC
                          LIMIT {$requestLimit}";
        $stmt = $this->conn->prepare($requestQuery);
        $stmt->execute([$this->user_id]);
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $events[] = [
                'timestamp' => $row['activity_time'],
                'icon' => 'fas fa-clipboard-check',
                'link' => 'bookings.php#request-' . $row['id'],
                'description' => "Request '" . ($row['title'] ?? 'Service') . "' is " . ucfirst(str_replace('_', ' ', $row['status'] ?? 'updated')) . '.',
            ];
        }

        // Recent payment submissions
        $paymentQuery = "SELECT p.id, p.payment_status, COALESCE(p.paid_at, p.updated_at, p.created_at) AS activity_time, sr.title
                         FROM payments p
                         JOIN service_requests sr ON sr.id = p.service_request_id
                         WHERE p.homeowner_id = ?
                         ORDER BY COALESCE(p.paid_at, p.updated_at, p.created_at) DESC
                         LIMIT {$requestLimit}";
        $stmt = $this->conn->prepare($paymentQuery);
        $stmt->execute([$this->user_id]);
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $events[] = [
                'timestamp' => $row['activity_time'],
                'icon' => 'fas fa-credit-card',
                'link' => 'payments.php#payment-' . $row['id'],
                'description' => "Payment for '" . ($row['title'] ?? 'service') . "' marked as " . ucfirst($row['payment_status'] ?? 'updated') . '.',
            ];
        }

        // Recent messages
        $messageQuery = "SELECT id, message, created_at AS activity_time
                         FROM chat_messages
                         WHERE sender_id = ? OR receiver_id = ?
                         ORDER BY created_at DESC
                         LIMIT {$requestLimit}";
        $stmt = $this->conn->prepare($messageQuery);
        $stmt->execute([$this->user_id, $this->user_id]);
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $excerpt = mb_substr($row['message'] ?? '', 0, 60);
            $events[] = [
                'timestamp' => $row['activity_time'],
                'icon' => 'fas fa-comment-dots',
                'link' => 'messages.php#chat-' . $row['id'],
                'description' => $excerpt ? "Message: {$excerpt}" : 'You have recent chat activity.',
            ];
        }

        // Sort events by time and trim to requested limit
        usort($events, function ($a, $b) {
            return strtotime($b['timestamp']) <=> strtotime($a['timestamp']);
        });
        $events = array_slice($events, 0, $limit);

        // Format output with human readable timestamps
        return array_map(function ($event) {
            $event['time_ago'] = $this->formatTimeAgo($event['timestamp']);
            return $event;
        }, $events);
    }

    private function formatTimeAgo($timestamp) {
        $timeAgo = strtotime($timestamp);
        $currentTime = time();
        $timeDiff = $currentTime - $timeAgo;

        $units = [
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second',
        ];

        foreach ($units as $unit => $label) {
            if ($timeDiff >= $unit) {
                $count = floor($timeDiff / $unit);
                return $count . ' ' . $label . ($count > 1 ? 's' : '') . ' ago';
            }
        }

        return 'just now';
    }

    public function getRecentServiceRequests($limit = 5) {
        $query = "SELECT sr.*, u.first_name, u.last_name, u.profession 
                 FROM service_requests sr 
                 LEFT JOIN users u ON sr.technician_id = u.id 
                 WHERE sr.homeowner_id = ? 
                 ORDER BY sr.created_at DESC 
                 LIMIT " . (int)$limit;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllServiceRequests() {
        $query = "SELECT sr.*, u.first_name, u.last_name, u.profession, u.tele_birr, u.bank_account,
                         p.id AS payment_id, p.amount AS payment_amount, p.payment_status AS payment_record_status,
                         p.payment_method AS payment_method, p.payment_proof, p.transaction_id, p.paid_at AS payment_paid_at,
                         p.technician_confirmed_at AS payment_technician_confirmed_at
                 FROM service_requests sr 
                 LEFT JOIN users u ON sr.technician_id = u.id 
                 LEFT JOIN payments p ON p.service_request_id = sr.id
                 WHERE sr.homeowner_id = ? 
                 ORDER BY sr.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUnreadMessages() {
        $query = "SELECT cm.*, u.first_name, u.last_name 
                 FROM chat_messages cm 
                 JOIN users u ON cm.sender_id = u.id 
                 WHERE cm.receiver_id = ? AND cm.is_read = 0 
                 ORDER BY cm.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllMessages() {
        $query = "SELECT cm.*, u.first_name, u.last_name, u.role 
                 FROM chat_messages cm 
                 JOIN users u ON cm.sender_id = u.id 
                 WHERE cm.receiver_id = ? OR cm.sender_id = ?
                 ORDER BY cm.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id, $this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createServiceRequest($data) {
        // Validate required fields
        $required = ['service_type', 'title', 'description', 'address', 'subcity', 'woreda'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        
        $status = !empty($data['technician_id']) ? 'waiting_acceptance' : 'pending';

        $query = "INSERT INTO service_requests 
                 (homeowner_id, service_type, title, description, address, subcity, woreda, preferred_date, preferred_time, budget, technician_id, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([
            $this->user_id,
            $data['service_type'],
            $data['title'],
            $data['description'],
            $data['address'],
            $data['subcity'],
            $data['woreda'],
            $data['preferred_date'] ?: null,
            $data['preferred_time'] ?: null,
            $data['budget'] ?: null,
            $data['technician_id'] ?: null,
            $status
        ]);

        if($result) {
            $requestId = $this->conn->lastInsertId();
            $title = 'New Service Request';
            $message = "Homeowner {$_SESSION['user_name']} submitted a new service request ({$data['title']}).";
            notifyAdmins($this->conn, $title, $message, 'request', $requestId, 'admin/requests.php');

            if(!empty($data['technician_id'])) {
                sendNotification(
                    $this->conn,
                    $data['technician_id'],
                    'New Job Awaiting Your Acceptance',
                    "A homeowner requested your service for {$data['service_type']}.",
                    'job',
                    $requestId,
                    'views/technician/pending_tasks.php'
                );
            }
        }

        return $result;
    }
    
    public function getUserProfile() {
        $query = "SELECT first_name, last_name, email, phone, address, subcity, woreda, profile_photo, residence_id_file 
                 FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateProfile($data, $files = []) {
        $current = $this->getUserProfile();
        $fields = [
            'first_name = ?',
            'last_name = ?',
            'phone = ?',
            'address = ?',
            'subcity = ?',
            'woreda = ?'
        ];
        $params = [
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $data['address'],
            $data['subcity'],
            $data['woreda']
        ];

        $meta = [];

        if(isset($files['profile_photo']) && $files['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->uploadFile($files['profile_photo'], 'assets/uploads/profiles', ['jpg','jpeg','png','gif'], 2 * 1024 * 1024, 'profile');
            if(!$upload['success']) {
                return $upload;
            }
            $fields[] = 'profile_photo = ?';
            $params[] = $upload['file_name'];
            $meta['profile_photo'] = $upload['file_name'];
            if(!empty($current['profile_photo'])) {
                $this->deleteFile('assets/uploads/profiles', $current['profile_photo']);
            }
        }

        if(isset($files['residence_id']) && $files['residence_id']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->uploadFile($files['residence_id'], 'assets/uploads/residence_ids', ['pdf','jpg','jpeg','png'], 5 * 1024 * 1024, 'residence');
            if(!$upload['success']) {
                return $upload;
            }
            $fields[] = 'residence_id_file = ?';
            $params[] = $upload['file_name'];
            $meta['residence_id_file'] = $upload['file_name'];
            if(!empty($current['residence_id_file'])) {
                $this->deleteFile('assets/uploads/residence_ids', $current['residence_id_file']);
            }
        }

        $params[] = $this->user_id;
        $query = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        if($stmt->execute($params)) {
            return array_merge(['success' => true], $meta);
        }

        return ['success' => false, 'message' => 'Error updating profile. Please try again.'];
    }
    
    public function changePassword($currentPassword, $newPassword) {
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$existing) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        if($existing['password'] !== $currentPassword) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }

        $update = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if($update->execute([$newPassword, $this->user_id])) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Unable to update password. Please try again.'];
    }
    
    public function getServiceRequestDetails($request_id) {
        $query = "SELECT sr.*, u.first_name, u.last_name, u.phone as technician_phone, u.email as technician_email, u.profession 
                 FROM service_requests sr 
                 LEFT JOIN users u ON sr.technician_id = u.id 
                 WHERE sr.id = ? AND sr.homeowner_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$request_id, $this->user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function respondToPriceProposal($requestId, $decision) {
        $request = $this->getServiceRequestDetails($requestId);
        if(!$request || $request['status'] !== 'price_proposed') {
            return false;
        }

        $newStatus = ($decision === 'accept') ? 'price_accepted' : 'price_rejected';
        $priceAcceptedAt = ($decision === 'accept') ? date('Y-m-d H:i:s') : null;
        $priceRejectedAt = ($decision === 'reject') ? date('Y-m-d H:i:s') : null;

        $stmt = $this->conn->prepare("UPDATE service_requests SET status = ?, price_accepted_at = ?, price_rejected_at = ?, updated_at = NOW() WHERE id = ? AND homeowner_id = ?");
        $updated = $stmt->execute([$newStatus, $priceAcceptedAt, $priceRejectedAt, $requestId, $this->user_id]);

        if($updated && !empty($request['technician_id'])) {
            $title = ($decision === 'accept') ? 'Price Accepted' : 'Price Rejected';
            $message = ($decision === 'accept')
                ? "The homeowner accepted your proposed price for {$request['title']}."
                : "The homeowner rejected your proposed price for {$request['title']}.";
            sendNotification(
                $this->conn,
                $request['technician_id'],
                $title,
                $message,
                'pricing',
                $requestId,
                'views/technician/my_tasks.php'
            );
        }

        return $updated;
    }

    public function getPayments($filters = []) {
        $query = "SELECT p.*, sr.title, sr.service_type, sr.status AS request_status,
                         sr.estimated_cost, sr.budget, sr.payment_requested_at,
                         t.first_name AS technician_first_name, t.last_name AS technician_last_name,
                         t.profession AS technician_profession,
                         t.tele_birr AS technician_tele_birr,
                         t.bank_account AS technician_bank_account,
                         p.technician_confirmed_at
                  FROM payments p
                  JOIN service_requests sr ON sr.id = p.service_request_id
                  LEFT JOIN users t ON sr.technician_id = t.id
                  WHERE p.homeowner_id = ?";

        $params = [$this->user_id];
        $filters = $filters ?? [];

        $validStatuses = ['pending', 'paid', 'verified'];
        if(!empty($filters['status']) && in_array($filters['status'], $validStatuses, true)) {
            $query .= " AND p.payment_status = ?";
            $params[] = $filters['status'];
        }

        $validMethods = ['tele_birr', 'cbe', 'bank_transfer', 'cash'];
        if(isset($filters['payment_method']) && $filters['payment_method'] !== '') {
            if($filters['payment_method'] === 'not_specified') {
                $query .= " AND (p.payment_method IS NULL OR p.payment_method = '')";
            } elseif(in_array($filters['payment_method'], $validMethods, true)) {
                $query .= " AND p.payment_method = ?";
                $params[] = $filters['payment_method'];
            }
        }

        if(!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query .= " AND (sr.title LIKE ? OR sr.service_type LIKE ? OR p.transaction_id LIKE ? OR CONCAT(COALESCE(t.first_name,''), ' ', COALESCE(t.last_name,'')) LIKE ?)";
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        $dateField = "DATE(COALESCE(p.paid_at, p.created_at))";
        if(!empty($filters['date_from']) && $this->isValidDateInput($filters['date_from'])) {
            $query .= " AND {$dateField} >= ?";
            $params[] = $filters['date_from'];
        }

        if(!empty($filters['date_to']) && $this->isValidDateInput($filters['date_to'])) {
            $query .= " AND {$dateField} <= ?";
            $params[] = $filters['date_to'];
        }

        $query .= " ORDER BY (CASE WHEN sr.status = 'payment_requested' AND p.payment_status = 'pending' THEN 0 ELSE 1 END),
                           COALESCE(p.paid_at, p.created_at) DESC, p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exportPaymentsCsv($filters = []) {
        $payments = $this->getPayments($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="payment_history_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Service Request',
            'Service Type',
            'Amount (ETB)',
            'Payment Method',
            'Status',
            'Date',
            'Technician',
            'Transaction ID'
        ]);

        foreach($payments as $payment) {
            $amount = $payment['amount'] ?: ($payment['estimated_cost'] ?? $payment['budget'] ?? 0);
            $dateValue = $payment['paid_at'] ?: $payment['created_at'];
            $technician = trim(
                (($payment['technician_first_name'] ?? '') . ' ' . ($payment['technician_last_name'] ?? ''))
            );
            $method = $payment['payment_method']
                ? ucfirst(str_replace('_', ' ', $payment['payment_method']))
                : 'Not specified';

            fputcsv($output, [
                $payment['title'] ?? '-',
                $payment['service_type'] ?? '-',
                number_format((float)$amount, 2, '.', ''),
                $method,
                ucfirst($payment['payment_status'] ?? ''),
                $dateValue ? date('Y-m-d H:i', strtotime($dateValue)) : '',
                $technician,
                $payment['transaction_id'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    public function submitPaymentReceipt($requestId, $data, $files = []) {
        $requestId = (int)$requestId;
        if($requestId <= 0 || !$this->user_id) {
            return ['success' => false, 'message' => 'Invalid payment request.'];
        }

        // Fetch payment and related request to validate state and ownership
        $stmt = $this->conn->prepare(
            "SELECT p.*, sr.status AS request_status, sr.technician_id
             FROM payments p
             JOIN service_requests sr ON sr.id = p.service_request_id
             WHERE p.service_request_id = ? AND p.homeowner_id = ?"
        );
        $stmt->execute([$requestId, $this->user_id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$payment) {
            return ['success' => false, 'message' => 'Payment record not found for this request.'];
        }

        // Only allow upload when payment is requested and still pending
        if($payment['request_status'] !== 'payment_requested' || $payment['payment_status'] !== 'pending') {
            return ['success' => false, 'message' => 'This payment is not currently awaiting your receipt upload.'];
        }

        $amount = isset($data['amount']) ? (float)$data['amount'] : 0;
        if($amount <= 0) {
            // Fallback to stored amount/estimate if not provided
            $amount = (float)($payment['amount'] ?? 0);
        }

        if($amount <= 0) {
            return ['success' => false, 'message' => 'Please specify a valid payment amount.'];
        }

        $validMethods = ['tele_birr', 'cbe', 'bank_transfer', 'cash'];
        $method = isset($data['payment_method']) ? strtolower(trim($data['payment_method'])) : '';
        if(!in_array($method, $validMethods, true)) {
            return ['success' => false, 'message' => 'Please choose a valid payment method.'];
        }

        $transactionId = trim($data['transaction_id'] ?? '');

        if(!isset($files['payment_proof']) || $files['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Please upload a valid receipt file.'];
        }

        // Upload receipt file
        $upload = $this->uploadFile(
            $files['payment_proof'],
            'assets/uploads/payments',
            ['jpg','jpeg','png','pdf'],
            5 * 1024 * 1024,
            'payment'
        );

        if(!$upload['success']) {
            return $upload;
        }

        $fileName = $upload['file_name'];

        // Update payment record
        $update = $this->conn->prepare(
            "UPDATE payments
             SET amount = ?,
                 payment_method = ?,
                 transaction_id = ?,
                 payment_proof = ?,
                 payment_status = 'paid',
                 paid_at = NOW(),
                 technician_confirmed_at = NULL
             WHERE id = ? AND homeowner_id = ?"
        );

        $ok = $update->execute([
            $amount,
            $method,
            $transactionId ?: null,
            $fileName,
            $payment['id'],
            $this->user_id
        ]);

        if(!$ok) {
            return ['success' => false, 'message' => 'Unable to save payment details. Please try again.'];
        }

        // Clean up old proof if any
        if(!empty($payment['payment_proof']) && $payment['payment_proof'] !== $fileName) {
            $this->deleteFile('assets/uploads/payments', $payment['payment_proof']);
        }

        // Notify technician and admins that a payment was submitted
        if(!empty($payment['technician_id'])) {
            sendNotification(
                $this->conn,
                $payment['technician_id'],
                'Payment Proof Submitted',
                'The homeowner has submitted payment proof for one of your jobs. Please review and confirm.',
                'payment',
                $requestId,
                'views/technician/payments.php'
            );
        }

        notifyAdmins(
            $this->conn,
            'New Payment Awaiting Verification',
            "A homeowner submitted payment proof for request #{$requestId}.",
            'payment',
            $requestId,
            'views/admin/dashboard.php'
        );

        return ['success' => true];
    }

    private function isValidDateInput($date) {
        $dt = DateTime::createFromFormat('Y-m-d', $date);
        return $dt && $dt->format('Y-m-d') === $date;
    }

    public function getTechnicians($filters = []) {
        $query = "SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.profession,
                         u.profile_photo, u.status, u.created_at,
                         COALESCE(AVG(r.rating), 0) AS average_rating,
                         COUNT(r.id) AS rating_count
                  FROM users u
                  LEFT JOIN ratings r ON r.technician_id = u.id
                  WHERE u.role = 'technician'";

        $params = [];
        $filters = $filters ?? [];

        // Restrict to approved/active technicians for homeowners
        $query .= " AND COALESCE(u.status, 'active') IN ('active','approved','verified')";

        if(!empty($filters['profession'])) {
            $query .= " AND LOWER(u.profession) = ?";
        }

        if(!empty($filters['search'])) {
            $term = '%' . strtolower($filters['search']) . '%';
            $query .= " AND (LOWER(u.first_name) LIKE ? OR LOWER(u.last_name) LIKE ? OR LOWER(u.profession) LIKE ?)";
            $params = array_merge($params, [$term, $term, $term]);
        }

        if(!empty($filters['profession'])) {
            $params[] = strtolower($filters['profession']);
        }

        $query .= " GROUP BY u.id
                    ORDER BY average_rating DESC, rating_count DESC, u.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTechnicianReviews($technicianId, $limit = 3) {
        $limit = max(1, (int)$limit);
        $query = "SELECT r.*, h.first_name AS homeowner_first_name, h.last_name AS homeowner_last_name
                  FROM ratings r
                  JOIN users h ON r.homeowner_id = h.id
                  WHERE r.technician_id = ?
                  ORDER BY r.created_at DESC
                  LIMIT {$limit}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$technicianId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTechniciansByService($serviceType) {
        if(empty($serviceType)) {
            return $this->getTechnicians();
        }

        return $this->getTechnicians(['profession' => $serviceType]);
    }

    public function submitFeedback($data) {
        if (!$this->user_id) {
            return false;
        }

        $technicianId = isset($data['technician_id']) ? (int)$data['technician_id'] : 0;
        $rating = isset($data['rating']) ? (int)$data['rating'] : 0;
        $comment = trim($data['comment'] ?? '');

        if ($technicianId <= 0 || $rating < 1 || $rating > 5) {
            return false;
        }

        // Ensure technician exists and is a technician
        $checkStmt = $this->conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'technician'");
        $checkStmt->execute([$technicianId]);
        if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
            return false;
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO ratings (homeowner_id, technician_id, rating, comment, created_at) 
             VALUES (?, ?, ?, ?, NOW())"
        );

        return $stmt->execute([
            $this->user_id,
            $technicianId,
            $rating,
            $comment
        ]);
    }

    private function uploadFile($file, $directory, $allowedExtensions, $maxBytes, $prefix) {
        $targetDir = rtrim($this->baseDir . '/' . $directory, '/');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            return ['success' => false, 'message' => 'Invalid file type uploaded.'];
        }

        if ($file['size'] > $maxBytes) {
            return ['success' => false, 'message' => 'File is too large.'];
        }

        $fileName = time() . '_' . $prefix . '.' . $ext;
        $targetPath = $targetDir . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'file_name' => $fileName];
        }

        return ['success' => false, 'message' => 'Unable to upload file.'];
    }

    private function deleteFile($directory, $fileName) {
        if (!$fileName) {
            return;
        }

        $path = rtrim($this->baseDir . '/' . $directory, '/') . '/' . $fileName;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}