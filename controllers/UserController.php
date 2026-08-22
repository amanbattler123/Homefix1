<?php
class UserController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getAllTechnicians() {
        $query = "SELECT * FROM users WHERE role = 'technician' ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPendingTechnicians() {
        $query = "SELECT * FROM users WHERE role = 'technician' AND status = 'pending' ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllUsers() {
        $query = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateUserStatus($user_id, $status) {
        $query = "UPDATE users SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $user_id);
        return $stmt->execute();
    }
    
    public function deleteUser($user_id) {
        try {
            // Start transaction to safely remove dependent records
            $this->conn->beginTransaction();

            // Delete related chat messages where this user is sender or receiver
            $chatQuery = "DELETE FROM chat_messages WHERE sender_id = :id OR receiver_id = :id";
            $chatStmt = $this->conn->prepare($chatQuery);
            $chatStmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $chatStmt->execute();

            // Delete ratings involving this user (as technician or homeowner)
            try {
                $ratingsQuery = "DELETE FROM ratings WHERE technician_id = :id OR homeowner_id = :id";
                $ratingsStmt = $this->conn->prepare($ratingsQuery);
                $ratingsStmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $ratingsStmt->execute();
            } catch (PDOException $ignored) {
                // Table might not exist in some installs; ignore
            }

            // Delete payments where this user is technician or homeowner
            try {
                $paymentsQuery = "DELETE FROM payments WHERE technician_id = :id OR homeowner_id = :id";
                $paymentsStmt = $this->conn->prepare($paymentsQuery);
                $paymentsStmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $paymentsStmt->execute();
            } catch (PDOException $ignored) {
                // Optional table; ignore if missing
            }

            // Detach this user from service requests (keep history but remove FK reference)
            try {
                $requestsQuery = "UPDATE service_requests SET technician_id = NULL WHERE technician_id = :id";
                $requestsStmt = $this->conn->prepare($requestsQuery);
                $requestsStmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $requestsStmt->execute();

                $requestsHomeQuery = "UPDATE service_requests SET homeowner_id = NULL WHERE homeowner_id = :id";
                $requestsHomeStmt = $this->conn->prepare($requestsHomeQuery);
                $requestsHomeStmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $requestsHomeStmt->execute();
            } catch (PDOException $ignored) {
                // Optional table or different schema; ignore if missing
            }

            // Delete task assignments for this technician
            try {
                $tasksQuery = "DELETE FROM task_assignments WHERE technician_id = :id";
                $tasksStmt = $this->conn->prepare($tasksQuery);
                $tasksStmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $tasksStmt->execute();
            } catch (PDOException $ignored) {
                // Optional table; ignore if missing
            }

            // Delete notifications for this user
            try {
                $notificationsQuery = "DELETE FROM notifications WHERE user_id = :id";
                $notificationsStmt = $this->conn->prepare($notificationsQuery);
                $notificationsStmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $notificationsStmt->execute();
            } catch (PDOException $ignored) {
                // Optional table; ignore if missing
            }

            // Finally delete the user
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('Error deleting user ' . $user_id . ': ' . $e->getMessage());
            return false;
        }
    }
    
    public function getUserById($user_id) {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getStats() {
        $stats = [];
        
        // Total users
        $query = "SELECT COUNT(*) as count FROM users";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total technicians
        $query = "SELECT COUNT(*) as count FROM users WHERE role = 'technician'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_technicians'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Pending technicians
        $query = "SELECT COUNT(*) as count FROM users WHERE role = 'technician' AND status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['pending_technicians'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total homeowners
        $query = "SELECT COUNT(*) as count FROM users WHERE role = 'homeowner'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_homeowners'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Unread messages
        $query = "SELECT COUNT(*) as count FROM messages WHERE status = 'unread'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['unread_messages'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total messages
        $query = "SELECT COUNT(*) as count FROM messages";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_messages'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return $stats;
    }
    
    // Message management functions
    public function getAllMessages() {
        $query = "SELECT * FROM messages ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUnreadMessages() {
        $query = "SELECT * FROM messages WHERE status = 'unread' ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function markMessageAsRead($message_id) {
        $query = "UPDATE messages SET status = 'read' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $message_id);
        return $stmt->execute();
    }
    
    public function deleteMessage($message_id) {
        $query = "DELETE FROM messages WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $message_id);
        return $stmt->execute();
    }
    
    public function getMessageById($message_id) {
        $query = "SELECT * FROM messages WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $message_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateProfilePhoto($user_id, $profile_photo) {
        $query = "UPDATE " . $this->table_name . " SET profile_photo = :profile_photo WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':profile_photo', $profile_photo);
        $stmt->bindParam(':id', $user_id);
        
        return $stmt->execute();
    }

    public function updatePassword($user_id, $password) {
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':id', $user_id);
        
        return $stmt->execute();
    }

    public function getTechniciansPaymentSummary() {
        $query = "SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    u.profession,
                    u.status,
                    u.created_at,
                    COALESCE(SUM(CASE WHEN p.payment_status IN ('paid','verified') THEN p.amount END), 0) AS total_received,
                    COALESCE(COUNT(CASE WHEN p.payment_status IN ('paid','verified') THEN p.id END), 0) AS payments_count
                FROM users u
                LEFT JOIN payments p ON p.technician_id = u.id
                WHERE u.role = 'technician'
                GROUP BY u.id
                ORDER BY total_received DESC, u.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $overallTotal = 0;
        foreach ($technicians as $tech) {
            $overallTotal += (float)($tech['total_received'] ?? 0);
        }

        return [
            'technicians' => $technicians,
            'overall_total' => $overallTotal
        ];
    }

    public function getAllTechnicianPayments($statusFilter = ['paid','verified']) {
        $allowedStatuses = array_map('strtolower', $statusFilter ?? []);
        if (empty($allowedStatuses)) {
            $allowedStatuses = ['paid','verified'];
        }
        $placeholders = implode(',', array_fill(0, count($allowedStatuses), '?'));

        $query = "SELECT 
                    p.id,
                    p.amount,
                    p.payment_status,
                    p.payment_method,
                    p.paid_at,
                    p.verified_at,
                    p.created_at,
                    sr.title AS service_title,
                    sr.service_type,
                    CONCAT(t.first_name, ' ', t.last_name) AS technician_name,
                    t.profession AS technician_profession,
                    t.id AS technician_id,
                    CONCAT(h.first_name, ' ', h.last_name) AS homeowner_name
                FROM payments p
                JOIN service_requests sr ON sr.id = p.service_request_id
                JOIN users t ON t.id = p.technician_id
                JOIN users h ON h.id = sr.homeowner_id
                WHERE LOWER(p.payment_status) IN ($placeholders)
                ORDER BY COALESCE(p.paid_at, p.verified_at, p.created_at) DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($allowedStatuses);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>