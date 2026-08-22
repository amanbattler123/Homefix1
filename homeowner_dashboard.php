<?php
// Start session and include config
session_start();
require_once 'includes/config.php';

// Check if user is logged in as homeowner
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'homeowner') {
    header("Location: login.php");
    exit();
}

header("Location: views/homeowner/dashboard.php");
exit();