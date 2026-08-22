<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'homefix_pro');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_URL', 'http://localhost/project');
define('SITE_NAME', 'HomeFix Pro');
define('FROM_EMAIL', 'no-reply@homefixpro.local');
define('SMTP_HOST', 'smtp.yourprovider.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_SECURE', 'tls');

// Create database connection
function getDBConnection() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>