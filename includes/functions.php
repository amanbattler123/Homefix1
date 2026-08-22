<?php
// Utility functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

function isTechnician() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'technician';
}

function isHomeowner() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'homeowner';
}

function displayMessage($message, $type = 'info') {
    $class = 'alert-' . $type;
    return '<div class="alert ' . $class . '">' . $message . '</div>';
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>