<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/NotificationHelper.php';

class TechnicianController {
    private $conn;
    private $user_id;
    private $baseDir;
    private static $schemaEnsured = false;

    public function __construct($db) {
        $this->conn = $db;
        $this->user_id = $_SESSION['user_id'] ?? null;
        $this->baseDir = realpath(__DIR__ . '/..');
        if ($this->baseDir === false) {
            $this->baseDir = __DIR__ . '/..';
        }
        $this->ensurePaymentSchema();
    }

    private function ensurePaymentSchema() {
        if(self::$schemaEnsured) {
            return;
        }
        try {
            // Ensure technician_confirmed_at exists on payments
            $stmt = $this->conn->query("SHOW COLUMNS FROM payments LIKE 'technician_confirmed_at'");
            $exists = $stmt && $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$exists) {
                $this->conn->exec("ALTER TABLE payments ADD COLUMN technician_confirmed_at DATETIME NULL DEFAULT NULL AFTER paid_at");
            }

            // Ensure is_seen_for_technician exists on ratings for review seen state
            $stmt = $this->conn->query("SHOW COLUMNS FROM ratings LIKE 'is_seen_for_technician'");
            $exists = $stmt && $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$exists) {
                $this->conn->exec("ALTER TABLE ratings ADD COLUMN is_seen_for_technician TINYINT(1) NOT NULL DEFAULT 0 AFTER comment");
            }
        } catch (Exception $e) {
            // Silently ignore to avoid breaking if permissions are restricted
        }
        self::$schemaEnsured = true;
    }

    public function getDashboardStats() {
        $details = $this->getDetailedStats();

        $stats = [
            'total_jobs'      => $details['services']['total'],
            'pending_jobs'    => $details['services']['pending'],
            'active_jobs'     => $details['services']['active'],
            'completed_jobs'  => $details['services']['completed'],
            'cancelled_jobs'  => $details['services']['cancelled'],
            'total_earnings'  => $details['payments']['total_amount_earned'],
            'pending_payments'=> $details['payments']['pending_payments'],
            'verified_payments'=> $details['payments']['verified_payments'],
            'unread_messages' => $details['messages']['unread'],
            'success_rate'    => $details['services']['success_rate']
        ];

        $query = "SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS rating_count FROM ratings WHERE technician_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['average_rating'] = round((float)($row['avg_rating'] ?? 0), 2);
        $stats['rating_count'] = (int)($row['rating_count'] ?? 0);

        // Unseen reviews for sidebar badge
        $stats['unseen_reviews'] = $this->getUnreadReviewCount();

        return $stats;
    }

    public function getDetailedStats() {
        $services = [
            'total' => 0,
            'pending' => 0,
            'active' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'success_rate' => 0
        ];
        $serviceQuery = "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status IN ('waiting_acceptance','approved','price_proposed','price_accepted') THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status IN ('waiting_inspection','assigned','in_progress','payment_requested') THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status IN ('completed','paid') THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN status IN ('rejected','price_rejected','cancelled') THEN 1 ELSE 0 END) AS cancelled
            FROM service_requests
            WHERE technician_id = ?";
        $stmt = $this->conn->prepare($serviceQuery);
        $stmt->execute([$this->user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        foreach ($services as $key => $default) {
            if ($key === 'success_rate') {
                continue;
            }
            $services[$key] = (int)($row[$key] ?? 0);
        }
        $services['success_rate'] = $services['total'] > 0
            ? round(($services['completed'] / $services['total']) * 100, 2)
            : 0;

        $payments = [
            'total_payments' => 0,
            'pending_payments' => 0,
            'verified_payments' => 0,
            'total_amount_earned' => 0.0
        ];
        $paymentQuery = "SELECT
                COUNT(*) AS total_payments,
                SUM(CASE WHEN payment_status IN ('pending','paid') THEN 1 ELSE 0 END) AS pending_payments,
                SUM(CASE WHEN payment_status = 'verified' THEN 1 ELSE 0 END) AS verified_payments,
                SUM(CASE WHEN payment_status IN ('paid','verified') THEN amount ELSE 0 END) AS total_amount_earned
            FROM payments
            WHERE technician_id = ?";
        $stmt = $this->conn->prepare($paymentQuery);
        $stmt->execute([$this->user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        foreach ($payments as $key => $default) {
            $payments[$key] = $key === 'total_amount_earned'
                ? (float)($row[$key] ?? 0)
                : (int)($row[$key] ?? 0);
        }

        $messages = [
            'total' => 0,
            'unread' => 0,
            'conversations' => 0,
            'response_rate' => 100
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
        $messages['total'] = (int)($row['received'] ?? 0) + (int)($row['sent'] ?? 0);
        $messages['unread'] = (int)($row['unread'] ?? 0);
        // conversations and response_rate are left as safe defaults for now

        return [
            'services' => $services,
            'payments' => $payments,
            'messages' => $messages
        ];
    }

    public function getNotifications($limit = 5) {
        $limit = max(1, (int)$limit);
        $query = "SELECT id, title, message, type, related_id, action_url, created_at, COALESCE(is_read,0) AS is_read
                  FROM notifications
                  WHERE user_id = ?
                  ORDER BY created_at DESC
                  LIMIT {$limit}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadNotificationCount() {
        $query = "SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND COALESCE(is_read,0) = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    }

    public function markNotificationsRead() {
        if (!$this->user_id) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND COALESCE(is_read,0) = 0");
        return $stmt->execute([$this->user_id]);
    }

    public function clearNotifications() {
        $stmt = $this->conn->prepare("DELETE FROM notifications WHERE user_id = ?");
        return $stmt->execute([$this->user_id]);
    }

    public function getUnreadReviewCount() {
        if (!$this->user_id) {
            return 0;
        }

        $query = "SELECT COUNT(*) AS count FROM ratings WHERE technician_id = ? AND COALESCE(is_seen_for_technician,0) = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return (int)($row['count'] ?? 0);
    }

    public function markReviewsSeen() {
        if (!$this->user_id) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE ratings SET is_seen_for_technician = 1 WHERE technician_id = ? AND COALESCE(is_seen_for_technician,0) = 0");
        return $stmt->execute([$this->user_id]);
    }

    private function countRequests($statuses = []) {
        $query = "SELECT COUNT(*) FROM service_requests WHERE technician_id = ?";
        $params = [$this->user_id];
        if (!empty($statuses)) {
            $placeholders = implode(',', array_fill(0, count($statuses), '?'));
            $query .= " AND status IN ($placeholders)";
            $params = array_merge($params, $statuses);
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getPendingRequests() {
        return $this->fetchRequestsByStatuses(['waiting_acceptance', 'approved', 'price_proposed', 'price_accepted']);
    }

    public function getActiveRequests() {
        return $this->fetchRequestsByStatuses(['in_progress', 'assigned', 'waiting_inspection', 'price_accepted', 'payment_requested']);
    }

    public function getCompletedRequests() {
        return $this->fetchRequestsByStatuses(['completed', 'paid']);
    }

    // Clear all pending tasks for this technician
    public function clearPendingTasks() {
        // Find IDs of pending service requests for this technician
        $select = $this->conn->prepare(
            "SELECT id FROM service_requests
             WHERE technician_id = ?
               AND status IN ('waiting_acceptance','approved','price_proposed','price_accepted')"
        );
        $select->execute([$this->user_id]);
        $ids = $select->fetchAll(PDO::FETCH_COLUMN);

        if (empty($ids)) {
            return true; // nothing to clear
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Delete related task_assignments rows first to satisfy FK constraint
        $deleteAssignments = $this->conn->prepare(
            "DELETE FROM task_assignments WHERE service_request_id IN ($placeholders)"
        );
        if (!$deleteAssignments->execute($ids)) {
            return false;
        }

        // Delete related payments rows to satisfy FK from payments.service_request_id
        $deletePayments = $this->conn->prepare(
            "DELETE FROM payments WHERE service_request_id IN ($placeholders)"
        );
        if (!$deletePayments->execute($ids)) {
            return false;
        }

        // Now delete the service_requests rows
        $deleteRequests = $this->conn->prepare(
            "DELETE FROM service_requests WHERE id IN ($placeholders)"
        );
        return $deleteRequests->execute($ids);
    }

    // Clear all active and completed tasks for this technician ("My Tasks" history)
    public function clearMyTasksHistory() {
        // Find IDs of active/completed service requests for this technician
        $select = $this->conn->prepare(
            "SELECT id FROM service_requests
             WHERE technician_id = ?
               AND status IN ('waiting_inspection','assigned','in_progress','payment_requested','completed','paid')"
        );
        $select->execute([$this->user_id]);
        $ids = $select->fetchAll(PDO::FETCH_COLUMN);

        if (empty($ids)) {
            return true; // nothing to clear
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Delete related task_assignments rows first to satisfy FK constraint
        $deleteAssignments = $this->conn->prepare(
            "DELETE FROM task_assignments WHERE service_request_id IN ($placeholders)"
        );
        if (!$deleteAssignments->execute($ids)) {
            return false;
        }

        // Delete related payments rows to satisfy FK from payments.service_request_id
        $deletePayments = $this->conn->prepare(
            "DELETE FROM payments WHERE service_request_id IN ($placeholders)"
        );
        if (!$deletePayments->execute($ids)) {
            return false;
        }

        // Now delete the service_requests rows
        $deleteRequests = $this->conn->prepare(
            "DELETE FROM service_requests WHERE id IN ($placeholders)"
        );
        return $deleteRequests->execute($ids);
    }

    private function fetchRequestsByStatuses(array $statuses = []) {
        $query = "SELECT sr.*, h.first_name AS homeowner_first_name, h.last_name AS homeowner_last_name, h.phone AS homeowner_phone,
                         h.subcity AS homeowner_subcity, h.woreda AS homeowner_woreda,
                         p.id AS payment_id, p.amount AS payment_amount, p.payment_status AS payment_record_status,
                         p.payment_method AS payment_method, p.payment_proof AS payment_proof,
                         p.transaction_id AS payment_transaction_id, p.paid_at AS payment_paid_at,
                         p.technician_confirmed_at AS payment_technician_confirmed_at
                  FROM service_requests sr
                  JOIN users h ON sr.homeowner_id = h.id
                  LEFT JOIN payments p ON p.service_request_id = sr.id
                  WHERE sr.technician_id = ?";
        $params = [$this->user_id];
        if (!empty($statuses)) {
            $placeholders = implode(',', array_fill(0, count($statuses), '?'));
            $query .= " AND sr.status IN ($placeholders)";
            $params = array_merge($params, $statuses);
        }
        $query .= " ORDER BY sr.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentRequests($limit = 5) {
        $query = "SELECT sr.*, h.first_name AS homeowner_first_name, h.last_name AS homeowner_last_name
                  FROM service_requests sr
                  JOIN users h ON sr.homeowner_id = h.id
                  WHERE sr.technician_id = ?
                  ORDER BY sr.created_at DESC
                  LIMIT " . (int)$limit;
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPayments($limit = null) {
        $query = "SELECT p.*, sr.title, sr.service_type, sr.status AS request_status,
                         sr.payment_requested_at, sr.work_completed_at,
                         h.first_name AS homeowner_first_name, h.last_name AS homeowner_last_name
                  FROM payments p
                  JOIN service_requests sr ON sr.id = p.service_request_id
                  JOIN users h ON sr.homeowner_id = h.id
                  WHERE p.technician_id = ?
                  ORDER BY (CASE
                                WHEN p.payment_status = 'paid' AND p.technician_confirmed_at IS NULL THEN 0
                                WHEN sr.status = 'payment_requested' AND p.payment_status = 'pending' THEN 1
                                ELSE 2
                            END), COALESCE(p.paid_at, p.created_at) DESC, p.created_at DESC";
        if ($limit !== null) {
            $query .= " LIMIT " . (int)$limit;
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReviews() {
        $query = "SELECT r.*, h.first_name, h.last_name
                  FROM ratings r
                  JOIN users h ON r.homeowner_id = h.id
                  WHERE r.technician_id = ?
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserProfile() {
        $query = "SELECT first_name, last_name, email, phone, address, subcity, woreda, profession, bank_account,
                         tele_birr, profile_photo, certification_file, residence_id_file
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
            'woreda = ?',
            'profession = ?',
            'bank_account = ?',
            'tele_birr = ?'
        ];
        $params = [
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $data['address'],
            $data['subcity'],
            $data['woreda'],
            $data['profession'],
            $data['bank_account'],
            $data['tele_birr']
        ];

        $meta = [];

        if (isset($files['profile_photo']) && $files['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->uploadFile($files['profile_photo'], 'assets/uploads/profiles', ['jpg','jpeg','png','gif'], 2 * 1024 * 1024, 'profile');
            if (!$upload['success']) {
                return $upload;
            }
            $fields[] = 'profile_photo = ?';
            $params[] = $upload['file_name'];
            $meta['profile_photo'] = $upload['file_name'];
            if (!empty($current['profile_photo'])) {
                $this->deleteFile('assets/uploads/profiles', $current['profile_photo']);
            }
        }

        if (isset($files['certification']) && $files['certification']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->uploadFile($files['certification'], 'assets/uploads/certifications', ['pdf','jpg','jpeg','png'], 5 * 1024 * 1024, 'certification');
            if (!$upload['success']) {
                return $upload;
            }
            $fields[] = 'certification_file = ?';
            $params[] = $upload['file_name'];
            $meta['certification_file'] = $upload['file_name'];
            if (!empty($current['certification_file'])) {
                $this->deleteFile('assets/uploads/certifications', $current['certification_file']);
            }
        }

        if (isset($files['residence_id']) && $files['residence_id']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->uploadFile($files['residence_id'], 'assets/uploads/residence_ids', ['pdf','jpg','jpeg','png'], 5 * 1024 * 1024, 'residence');
            if (!$upload['success']) {
                return $upload;
            }
            $fields[] = 'residence_id_file = ?';
            $params[] = $upload['file_name'];
            $meta['residence_id_file'] = $upload['file_name'];
            if (!empty($current['residence_id_file'])) {
                $this->deleteFile('assets/uploads/residence_ids', $current['residence_id_file']);
            }
        }

        $params[] = $this->user_id;
        $query = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute($params)) {
            return array_merge(['success' => true], $meta);
        }

        return ['success' => false, 'message' => 'Unable to update profile. Please try again.'];
    }

    public function changePassword($currentPassword, $newPassword) {
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->user_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        if ($existing['password'] !== $currentPassword) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }

        $update = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($update->execute([$newPassword, $this->user_id])) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Unable to update password. Please try again.'];
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

    public function getRequestForTechnician($requestId) {
        // Load service request plus homeowner contact details so technician can see full info
        $query = "SELECT
                      sr.*,
                      h.first_name AS homeowner_first_name,
                      h.last_name AS homeowner_last_name,
                      h.email AS homeowner_email,
                      h.phone AS homeowner_phone,
                      h.subcity AS homeowner_subcity,
                      h.woreda AS homeowner_woreda
                  FROM service_requests sr
                  JOIN users h ON sr.homeowner_id = h.id
                  WHERE sr.id = ? AND sr.technician_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$requestId, $this->user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function acceptRequest($requestId) {
        $request = $this->getRequestForTechnician($requestId);
        if(!$request) {
            return false;
        }

        if(!in_array($request['status'], ['waiting_acceptance', 'approved'])) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE service_requests SET status = 'waiting_inspection', updated_at = NOW() WHERE id = ? AND technician_id = ?");
        $updated = $stmt->execute([$requestId, $this->user_id]);

        if($updated) {
            sendNotification(
                $this->conn,
                $request['homeowner_id'],
                'Technician Accepted Your Request',
                "{$request['homeowner_first_name']}, your technician accepted the job titled '{$request['title']}'.",
                'job',
                $requestId,
                'views/homeowner/bookings.php'
            );
        }

        return $updated;
    }

    public function markTaskCompleted($requestId) {
        $request = $this->getRequestForTechnician($requestId);
        if(!$request) {
            return false;
        }

        if(!in_array($request['status'], ['price_accepted', 'in_progress'])) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE service_requests SET status = 'payment_requested', work_completed_at = NOW(), payment_requested_at = NOW(), updated_at = NOW() WHERE id = ? AND technician_id = ?");
        $updated = $stmt->execute([$requestId, $this->user_id]);

        if($updated) {
            $amount = $request['estimated_cost'] ?? $request['budget'] ?? 0;
            $this->createOrRefreshPaymentRecord($requestId, $request['homeowner_id'], $amount);
            sendNotification(
                $this->conn,
                $request['homeowner_id'],
                'Payment Requested',
                "{$request['homeowner_first_name']}, your technician marked '{$request['title']}' as completed and requested payment.",
                'payment',
                $requestId,
                'views/homeowner/payments.php'
            );
            notifyAdmins(
                $this->conn,
                'Service Payment Requested',
                "{$request['title']} is awaiting homeowner payment.",
                'payment',
                $requestId,
                'views/admin/dashboard.php'
            );
        }

        return $updated;
    }

    public function confirmPaymentReceived($requestId) {
        $request = $this->getRequestForTechnician($requestId);
        if(!$request || $request['status'] !== 'payment_requested') {
            return false;
        }

        $payment = $this->getPaymentRecord($requestId);
        if(!$payment || $payment['payment_status'] !== 'paid') {
            return false;
        }

        if(!empty($payment['technician_confirmed_at'])) {
            return true;
        }

        $stmt = $this->conn->prepare("UPDATE payments SET technician_confirmed_at = NOW() WHERE id = ?");
        $updated = $stmt->execute([$payment['id']]);

        if($updated) {
            sendNotification(
                $this->conn,
                $request['homeowner_id'],
                'Technician Confirmed Your Payment',
                "Your payment for '{$request['title']}' has been acknowledged by the technician and is pending admin verification.",
                'payment',
                $requestId,
                'views/homeowner/payments.php'
            );
            notifyAdmins(
                $this->conn,
                'Payment Awaiting Verification',
                "{$request['title']} payment is ready for admin verification.",
                'payment',
                $requestId,
                'views/admin/dashboard.php'
            );
        }

        return $updated;
    }

    public function rejectRequest($requestId, $reason = null) {
        $request = $this->getRequestForTechnician($requestId);
        if(!$request) {
            return false;
        }

        if(!in_array($request['status'], ['waiting_acceptance', 'approved'])) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE service_requests SET status = 'rejected', price_rejection_reason = ?, updated_at = NOW() WHERE id = ? AND technician_id = ?");
        $updated = $stmt->execute([
            $reason,
            $requestId,
            $this->user_id
        ]);

        if($updated) {
            sendNotification(
                $this->conn,
                $request['homeowner_id'],
                'Technician Rejected Your Request',
                "{$request['homeowner_first_name']}, your technician rejected the job '{$request['title']}'. Please choose another technician.",
                'job',
                $requestId,
                'views/homeowner/bookings.php'
            );
        }

        return $updated;
    }

    public function submitInspection($requestId, $data) {
        $request = $this->getRequestForTechnician($requestId);
        if(!$request) {
            return false;
        }

        if(!in_array($request['status'], ['waiting_inspection', 'assigned', 'in_progress'])) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE service_requests SET inspection_notes = ?, estimated_cost = ?, inspection_submitted_at = NOW(), status = 'price_proposed', updated_at = NOW(), inspection_findings = ?, labor_cost = ?, materials_cost = ?, inspection_recommendations = ? WHERE id = ? AND technician_id = ?");
        $updated = $stmt->execute([
            $data['inspection_notes'],
            $data['estimated_cost'],
            $data['inspection_findings'] ?? null,
            $data['labor_cost'] ?? null,
            $data['materials_cost'] ?? null,
            $data['inspection_recommendations'] ?? null,
            $requestId,
            $this->user_id
        ]);

        if($updated) {
            sendNotification(
                $this->conn,
                $request['homeowner_id'],
                'New Price Proposal Ready',
                "{$request['homeowner_first_name']}, your technician submitted an inspection report for '{$request['title']}'.",
                'pricing',
                $requestId,
                'views/homeowner/bookings.php'
            );
        }

        return $updated;
    }

    private function getPaymentRecord($requestId) {
        $stmt = $this->conn->prepare("SELECT * FROM payments WHERE service_request_id = ?");
        $stmt->execute([$requestId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function createOrRefreshPaymentRecord($requestId, $homeownerId, $amount) {
        $amount = $amount ?: 0;
        $existing = $this->getPaymentRecord($requestId);
        if($existing) {
            $stmt = $this->conn->prepare("UPDATE payments
                SET amount = ?, payment_status = 'pending', payment_method = 'cash', transaction_id = NULL,
                    payment_proof = NULL, paid_at = NULL, technician_confirmed_at = NULL
                WHERE id = ?");
            $stmt->execute([$amount, $existing['id']]);
            return $existing['id'];
        }

        $stmt = $this->conn->prepare("INSERT INTO payments (service_request_id, homeowner_id, technician_id, amount, payment_method, payment_status)
                VALUES (?, ?, ?, ?, 'cash', 'pending')");
        $stmt->execute([$requestId, $homeownerId, $this->user_id, $amount]);
        return $this->conn->lastInsertId();
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
