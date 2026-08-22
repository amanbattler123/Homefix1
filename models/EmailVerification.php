<?php
class EmailVerification {
    private $conn;
    private $table_name = "email_verifications";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new verification token for a user
    public function createToken($user_id) {
        $token = bin2hex(random_bytes(32));

        // Remove any existing tokens for this user
        $deleteQuery = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $deleteStmt = $this->conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $deleteStmt->execute();

        // Insert new token
        $query = "INSERT INTO " . $this->table_name . " (user_id, token, created_at) VALUES (:user_id, :token, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $token;
        }

        return false;
    }

    // Check if a user is verified
    public function isVerified($user_id) {
        $query = "SELECT is_verified FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return !empty($row['is_verified']) && $row['is_verified'] == 1;
        }

        return false;
    }

    // Verify user by token
    public function verifyByToken($token) {
        $query = "SELECT user_id FROM " . $this->table_name . " WHERE token = :token LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_id = $row['user_id'];

            // Mark user as verified
            $updateQuery = "UPDATE users SET is_verified = 1 WHERE id = :user_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

            if ($updateStmt->execute()) {
                // Remove used token
                $deleteQuery = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $deleteStmt->execute();

                return $user_id;
            }
        }

        return false;
    }
}
?>
