<?php
session_start();
require_once 'includes/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'technician') {
    header('Location: login.php');
    exit();
}

header('Location: views/technician/dashboard.php');
exit();
