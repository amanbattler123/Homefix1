<?php
function sendNotification($conn, $userId, $title, $message, $type = 'info', $relatedId = null, $actionUrl = null) {
    if (!$userId) {
        return false;
    }

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, related_id, action_url) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$userId, $title, $message, $type, $relatedId, $actionUrl]);
}

function notifyAdmins($conn, $title, $message, $type = 'info', $relatedId = null, $actionUrl = null) {
    $adminStmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin'");
    $adminStmt->execute();
    $admins = $adminStmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($admins as $adminId) {
        sendNotification($conn, $adminId, $title, $message, $type, $relatedId, $actionUrl);
    }
}
