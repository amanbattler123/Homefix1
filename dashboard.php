<?php
session_start();
require_once 'includes/config.php';
require_once 'models/Database.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = "Dashboard";
include 'includes/header.php';

$db = new Database();
$userController = new UserController($db->getConnection());
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeFix Pro - Dashboard</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    
    <style>
        /* Modern Dashboard Styles */
        :root {
            --primary: #3b82f6;
            --primary-light: #60a5fa;
            --secondary: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            min-height: 100vh;
            color: var(--dark);
        }

        /* Glass Morphism Navigation */
        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .nav-brand .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .nav-btn {
            background: none;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-btn:hover {
            background: rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }

        .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid white;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid var(--primary);
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
            filter: blur(40px);
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
            animation: float 20s infinite linear;
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: -100px;
            right: -100px;
            animation: float 25s infinite linear reverse;
        }

        .shape-3 {
            width: 150px;
            height: 150px;
            top: 50%;
            left: 70%;
            animation: float 30s infinite linear;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(100px, 100px) rotate(360deg); }
        }

        /* Modern Dashboard Container */
        .modern-dashboard {
            position: relative;
            min-height: 100vh;
        }

        .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        /* Hero Welcome Section */
        .hero-welcome {
            margin-bottom: 3rem;
        }

        .welcome-content {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .greeting h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--dark), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .highlight {
            color: var(--primary);
            -webkit-text-fill-color: var(--primary);
        }

        .subtitle {
            font-size: 1.1rem;
            color: var(--gray);
        }

        .emoji {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .user-stats {
            display: flex;
            gap: 1rem;
        }

        .stat-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.75rem 1.5rem;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 16px;
            font-weight: 600;
            color: var(--primary);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .online-status {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-color: rgba(16, 185, 129, 0.2);
        }

        .status-dot {
            width: 10px;
            height: 10px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        /* Live Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .metric-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
        }

        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .live-card .metric-icon { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .revenue-card .metric-icon { background: linear-gradient(135deg, #10b981, #34d399); }
        .rating-card .metric-icon { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
        .schedule-card .metric-icon { background: linear-gradient(135deg, #3b82f6, #60a5fa); }

        .metric-content {
            flex: 1;
        }

        .metric-content h3 {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .metric-trend {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .metric-trend.up {
            color: var(--success);
        }

        .metric-trend.down {
            color: var(--danger);
        }

        .stars {
            display: flex;
            gap: 2px;
            color: #fbbf24;
            font-size: 0.9rem;
        }

        .schedule-items {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .schedule-item {
            font-size: 0.85rem;
            color: var(--gray);
            padding: 2px 8px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 12px;
            display: inline-block;
        }

        .metric-chart {
            height: 30px;
        }

        /* Dashboard Sections */
        .dashboard-section {
            margin-bottom: 3rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .section-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
        }

        .section-actions {
            display: flex;
            gap: 1rem;
        }

        .action-btn {
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        /* Advanced Cards Grid */
        .advanced-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .advanced-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .advanced-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(59, 130, 246, 0.2);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-header h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
        }

        .card-header i {
            font-size: 2rem;
            color: var(--primary);
            opacity: 0.8;
        }

        /* User Management Card */
        .card-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat {
            text-align: center;
            padding: 1rem;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 16px;
        }

        .stat-value {
            display: block;
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--gray);
        }

        /* Alert List */
        .alert-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .alert-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .alert-item:hover {
            transform: translateX(5px);
            background: rgba(59, 130, 246, 0.05);
        }

        .alert-item.critical {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--danger);
        }

        .alert-item.warning {
            background: rgba(245, 158, 11, 0.1);
            border-left: 4px solid var(--warning);
        }

        .alert-item.info {
            background: rgba(59, 130, 246, 0.1);
            border-left: 4px solid var(--primary);
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            display: block;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .alert-time {
            font-size: 0.85rem;
            color: var(--gray);
        }

        /* Job Board */
        .job-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .job-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: rgba(59, 130, 246, 0.05);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .job-item:hover {
            background: rgba(59, 130, 246, 0.1);
            transform: translateX(5px);
        }

        .job-item.urgent {
            border-left: 4px solid var(--danger);
        }

        .job-info {
            display: flex;
            flex-direction: column;
        }

        .job-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .job-location {
            font-size: 0.85rem;
            color: var(--gray);
        }

        .job-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
        }

        /* Schedule Timeline */
        .schedule-timeline {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .timeline-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .timeline-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .time {
            font-weight: 700;
            color: var(--primary);
            min-width: 60px;
        }

        .event {
            flex: 1;
        }

        .event-title {
            display: block;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .event-client {
            font-size: 0.9rem;
            color: var(--gray);
        }

        /* Quick Services */
        .service-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .service-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1.5rem;
            background: rgba(59, 130, 246, 0.05);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .service-item:hover {
            background: rgba(59, 130, 246, 0.1);
            transform: translateY(-5px);
        }

        .service-item i {
            font-size: 2rem;
            color: var(--primary);
        }

        .service-item span {
            font-weight: 600;
        }

        /* Request List */
        .request-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .request-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: rgba(59, 130, 246, 0.05);
            border-radius: 12px;
        }

        .request-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .request-title {
            font-weight: 600;
        }

        .request-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .request-status.in-progress {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .request-status.scheduled {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
        }

        .request-technician {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .request-technician img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Earnings Stats */
        .earnings-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .earning-stat {
            text-align: center;
            padding: 1rem;
            background: rgba(59, 130, 246, 0.05);
            border-radius: 16px;
        }

        .earning-stat .value {
            display: block;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .earning-stat .label {
            font-size: 0.9rem;
            color: var(--gray);
        }

        /* History Stats */
        .history-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .history-stat {
            text-align: center;
            padding: 1rem;
            background: rgba(59, 130, 246, 0.05);
            border-radius: 16px;
        }

        .history-stat .value {
            display: block;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .history-stat .label {
            font-size: 0.9rem;
            color: var(--gray);
        }

        /* Modern Buttons */
        .modern-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            font-size: 0.95rem;
        }

        .modern-btn.primary {
            background: var(--primary);
            color: white;
        }

        .modern-btn.primary:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .modern-btn.outline {
            background: transparent;
            color: var(--primary);
            border-color: var(--primary);
        }

        .modern-btn.outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .card-actions {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }

        /* Why Choose HomeFix Pro Section */
        .why-choose-section {
            position: relative;
            padding: 80px 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            overflow: hidden;
            margin: 40px 0;
            border-radius: 24px;
        }

        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
        }

        .float-element {
            position: absolute;
            font-size: 24px;
            color: rgba(59, 130, 246, 0.1);
            animation: floatElement 15s infinite linear;
        }

        .float-element.el-1 { top: 10%; left: 5%; animation-delay: 0s; }
        .float-element.el-2 { top: 20%; right: 10%; animation-delay: 3s; }
        .float-element.el-3 { bottom: 30%; left: 15%; animation-delay: 6s; }
        .float-element.el-4 { bottom: 20%; right: 5%; animation-delay: 9s; }

        @keyframes floatElement {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .why-choose-section .section-header {
            text-align: center;
            margin-bottom: 60px;
            position: relative;
            z-index: 2;
            flex-direction: column;
            gap: 24px;
        }

        .header-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 50%;
            margin-bottom: 24px;
            font-size: 36px;
            color: white;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
        }

        .header-icon.pulse {
            animation: pulse 2s infinite;
        }

        .gradient-text {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-subtitle {
            font-size: 1.25rem;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto 60px;
            position: relative;
            z-index: 2;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .feature-card:hover .feature-wave {
            transform: translateX(0%);
        }

        .feature-icon-wrapper {
            position: relative;
            width: 70px;
            height: 70px;
            margin-bottom: 24px;
        }

        .feature-icon-bg {
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 18px;
            opacity: 0.1;
            transform: rotate(45deg);
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon-bg {
            transform: rotate(135deg);
            opacity: 0.2;
        }

        .feature-icon {
            position: relative;
            z-index: 2;
            font-size: 32px;
            color: #3b82f6;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .feature-description {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 1.05rem;
        }

        /* Feature Badges */
        .feature-badge,
        .response-timer,
        .pricing-guarantee,
        .quality-badge,
        .coverage-map,
        .support-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 12px;
            color: #3b82f6;
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        .feature-badge { background: rgba(34, 197, 94, 0.1); color: #10b981; }
        .response-timer { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .pricing-guarantee { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

        .quality-badge {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            padding: 8px 12px;
        }

        .quality-badge .stars {
            display: flex;
            gap: 2px;
        }

        .rating {
            font-weight: 700;
            margin-left: 4px;
        }

        .coverage-map { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .support-status { background: rgba(16, 185, 129, 0.1); color: #10b981; }

        .support-status .status-indicator {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        /* Feature Wave Animation */
        .feature-wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 200%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #3b82f6, transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        /* Stats Banner */
        .stats-banner {
            max-width: 1000px;
            margin: 40px auto 0;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.3);
            position: relative;
            z-index: 2;
            overflow: hidden;
        }

        .stats-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: moveBackground 20s linear infinite;
            opacity: 0.5;
        }

        .stats-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .stat-item {
            text-align: center;
            padding: 0 20px;
        }

        .stat-value {
            font-size: 3rem;
            font-weight: 800;
            color: white;
            margin-bottom: 8px;
            line-height: 1;
        }

        .stat-label {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        .stat-divider {
            width: 1px;
            height: 60px;
            background: rgba(255, 255, 255, 0.3);
        }

        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(30px, 30px); }
        }

        /* Live Activity Feed */
        .activity-feed {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin-top: 3rem;
        }

        .feed-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .feed-header h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
        }

        .live-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--danger);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: var(--danger);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        .feed-content {
            min-height: 200px;
            background: rgba(59, 130, 246, 0.05);
            border-radius: 16px;
            padding: 1.5rem;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .container-fluid {
                padding: 1.5rem;
            }
            
            .advanced-cards {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .welcome-content {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }
            
            .user-stats {
                justify-content: center;
            }
            
            .section-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .section-actions {
                justify-content: center;
            }
            
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 0 15px;
            }
            
            .gradient-text {
                font-size: 2.2rem;
            }
            
            .stats-container {
                flex-direction: column;
                gap: 30px;
            }
            
            .stat-divider {
                width: 60px;
                height: 1px;
            }
            
            .feature-card {
                padding: 24px;
            }
            
            .stat-value {
                font-size: 2.5rem;
            }
            
            .hero-welcome .greeting h1 {
                font-size: 2rem;
            }
            
            .nav-container {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .card-stats {
                grid-template-columns: 1fr;
            }
            
            .service-grid {
                grid-template-columns: 1fr;
            }
            
            .modern-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* AOS Animation Styles */
        [data-aos] {
            opacity: 0;
            transition-property: opacity, transform;
        }

        [data-aos].aos-animate {
            opacity: 1;
        }

        /* Typing animation */
        .typing-text {
            overflow: hidden;
            border-right: 3px solid var(--primary);
            white-space: nowrap;
            animation: typing 3.5s steps(40, end), blink-caret 0.75s step-end infinite;
        }

        @keyframes typing {
            from { width: 0 }
            to { width: 100% }
        }

        @keyframes blink-caret {
            from, to { border-color: transparent }
            50% { border-color: var(--primary) }
        }
    </style>
</head>
<body>
    <!-- Glass Morphism Navigation -->
    <nav class="glass-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-tools pulse"></i>
                <span>HomeFix Pro</span>
            </div>
            <div class="nav-actions">
                <button class="nav-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-dot"></span>
                </button>
                <button class="nav-btn">
                    <i class="fas fa-envelope"></i>
                </button>
                <div class="user-avatar">
                    <?php if(isset($_SESSION['profile_photo'])): ?>
                        <img src="<?php echo SITE_URL; ?>/assets/uploads/profiles/<?php echo $_SESSION['profile_photo']; ?>" alt="Profile">
                    <?php else: ?>
                        <div class="avatar-placeholder"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="modern-dashboard">
        <!-- Animated Background -->
        <div class="animated-bg">
            <div class="bg-shape shape-1"></div>
            <div class="bg-shape shape-2"></div>
            <div class="bg-shape shape-3"></div>
        </div>

        <div class="container-fluid">
            <!-- Hero Welcome Section -->
            <div class="hero-welcome">
                <div class="welcome-content">
                    <div class="greeting">
                        <h1 class="typing-text">Good 
                            <span class="time-based-greeting"></span>, 
                            <span class="highlight"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </h1>
                        <p class="subtitle">Ready to tackle today's tasks? <span class="emoji">🚀</span></p>
                    </div>
                    <div class="user-stats">
                        <div class="stat-badge">
                            <i class="fas fa-shield-alt"></i>
                            <span><?php echo ucfirst($_SESSION['user_role']); ?></span>
                        </div>
                        <div class="stat-badge online-status">
                            <div class="status-dot"></div>
                            <span>Online</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Metrics Grid -->
            <div class="metrics-grid">
                <div class="metric-card live-card">
                    <div class="metric-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="metric-content">
                        <h3>Live Requests</h3>
                        <div class="metric-value" id="liveRequests">12</div>
                        <div class="metric-trend up">
                            <i class="fas fa-arrow-up"></i>
                            <span>+5% today</span>
                        </div>
                    </div>
                    <div class="metric-chart">
                        <canvas id="requestsChart" width="80" height="30"></canvas>
                    </div>
                </div>

                <div class="metric-card revenue-card">
                    <div class="metric-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="metric-content">
                        <h3>Revenue</h3>
                        <div class="metric-value">$2,847</div>
                        <div class="metric-trend up">
                            <i class="fas fa-arrow-up"></i>
                            <span>+12% this week</span>
                        </div>
                    </div>
                    <div class="metric-chart">
                        <canvas id="revenueChart" width="80" height="30"></canvas>
                    </div>
                </div>

                <div class="metric-card rating-card">
                    <div class="metric-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="metric-content">
                        <h3>Rating</h3>
                        <div class="metric-value">4.8</div>
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>

                <div class="metric-card schedule-card">
                    <div class="metric-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="metric-content">
                        <h3>Today's Schedule</h3>
                        <div class="metric-value">3</div>
                        <div class="schedule-items">
                            <span class="schedule-item">10:00 AM</span>
                            <span class="schedule-item">2:00 PM</span>
                            <span class="schedule-item">4:30 PM</span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($_SESSION['user_role'] == 'admin'): ?>
            <!-- Admin Futuristic Dashboard -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Control Center</h2>
                    <div class="section-actions">
                        <button class="action-btn">
                            <i class="fas fa-sync-alt"></i>
                            Refresh
                        </button>
                    </div>
                </div>
                
                <div class="advanced-cards">
                    <div class="advanced-card user-management">
                        <div class="card-header">
                            <h3>User Management</h3>
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div class="card-stats">
                            <div class="stat">
                                <span class="stat-value"><?php echo count($userController->getPendingTechnicians()); ?></span>
                                <span class="stat-label">Pending</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value"><?php echo count($userController->getAllTechnicians()); ?></span>
                                <span class="stat-label">Technicians</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">156</span>
                                <span class="stat-label">Homeowners</span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="modern-btn primary">
                                <i class="fas fa-cog"></i>
                                Manage Users
                            </button>
                        </div>
                    </div>

                    <div class="advanced-card analytics">
                        <div class="card-header">
                            <h3>Real-time Analytics</h3>
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="analytics-chart">
                            <canvas id="analyticsChart"></canvas>
                        </div>
                        <div class="card-actions">
                            <button class="modern-btn outline">
                                <i class="fas fa-chart-bar"></i>
                                View Reports
                            </button>
                        </div>
                    </div>

                    <div class="advanced-card notifications">
                        <div class="card-header">
                            <h3>System Alerts</h3>
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="alert-list">
                            <div class="alert-item critical">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div class="alert-content">
                                    <span class="alert-title">Server Load High</span>
                                    <span class="alert-time">2 min ago</span>
                                </div>
                            </div>
                            <div class="alert-item warning">
                                <i class="fas fa-clock"></i>
                                <div class="alert-content">
                                    <span class="alert-title">Pending Approvals</span>
                                    <span class="alert-time">5 min ago</span>
                                </div>
                            </div>
                            <div class="alert-item info">
                                <i class="fas fa-info-circle"></i>
                                <div class="alert-content">
                                    <span class="alert-title">System Updated</span>
                                    <span class="alert-time">1 hour ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif($_SESSION['user_role'] == 'technician'): ?>
            <!-- Technician Futuristic Dashboard -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Service Hub</h2>
                    <div class="section-actions">
                        <button class="action-btn">
                            <i class="fas fa-map-marker-alt"></i>
                            Set Location
                        </button>
                    </div>
                </div>

                <div class="advanced-cards">
                    <div class="advanced-card job-board">
                        <div class="card-header">
                            <h3>Available Jobs</h3>
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="job-list">
                            <div class="job-item urgent">
                                <div class="job-info">
                                    <span class="job-title">Emergency Plumbing</span>
                                    <span class="job-location">2.3 miles away</span>
                                </div>
                                <div class="job-price">$120</div>
                            </div>
                            <div class="job-item">
                                <div class="job-info">
                                    <span class="job-title">Electrical Repair</span>
                                    <span class="job-location">1.1 miles away</span>
                                </div>
                                <div class="job-price">$85</div>
                            </div>
                            <div class="job-item">
                                <div class="job-info">
                                    <span class="job-title">AC Installation</span>
                                    <span class="job-location">3.7 miles away</span>
                                </div>
                                <div class="job-price">$250</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="modern-btn primary">
                                <i class="fas fa-search"></i>
                                Find More Jobs
                            </button>
                        </div>
                    </div>

                    <div class="advanced-card earnings">
                        <div class="card-header">
                            <h3>Earnings Overview</h3>
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="earnings-chart">
                            <canvas id="earningsChart"></canvas>
                        </div>
                        <div class="earnings-stats">
                            <div class="earning-stat">
                                <span class="label">This Week</span>
                                <span class="value">$1,250</span>
                            </div>
                            <div class="earning-stat">
                                <span class="label">This Month</span>
                                <span class="value">$4,800</span>
                            </div>
                        </div>
                    </div>

                    <div class="advanced-card schedule">
                        <div class="card-header">
                            <h3>Today's Schedule</h3>
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="schedule-timeline">
                            <div class="timeline-item">
                                <div class="time">09:00</div>
                                <div class="event">
                                    <span class="event-title">Kitchen Plumbing</span>
                                    <span class="event-client">Sarah Johnson</span>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="time">13:30</div>
                                <div class="event">
                                    <span class="event-title">Electrical Panel</span>
                                    <span class="event-client">Mike Davis</span>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="time">16:00</div>
                                <div class="event">
                                    <span class="event-title">Bathroom Repair</span>
                                    <span class="event-client">Lisa Brown</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- Homeowner Futuristic Dashboard -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Home Services Hub</h2>
                    <div class="section-actions">
                        <button class="action-btn">
                            <i class="fas fa-plus"></i>
                            New Request
                        </button>
                    </div>
                </div>

                <div class="advanced-cards">
                    <div class="advanced-card quick-services">
                        <div class="card-header">
                            <h3>Quick Services</h3>
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="service-grid">
                            <div class="service-item" data-service="plumbing">
                                <i class="fas fa-faucet"></i>
                                <span>Plumbing</span>
                            </div>
                            <div class="service-item" data-service="electrical">
                                <i class="fas fa-bolt"></i>
                                <span>Electrical</span>
                            </div>
                            <div class="service-item" data-service="cleaning">
                                <i class="fas fa-broom"></i>
                                <span>Cleaning</span>
                            </div>
                            <div class="service-item" data-service="repair">
                                <i class="fas fa-tools"></i>
                                <span>Repair</span>
                            </div>
                        </div>
                    </div>

                    <div class="advanced-card active-requests">
                        <div class="card-header">
                            <h3>Active Requests</h3>
                            <i class="fas fa-list-alt"></i>
                        </div>
                        <div class="request-list">
                            <div class="request-item">
                                <div class="request-info">
                                    <span class="request-title">Kitchen Sink Repair</span>
                                    <span class="request-status in-progress">In Progress</span>
                                </div>
                                <div class="request-technician">
                                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop&crop=face" alt="Technician">
                                    <span>John Smith</span>
                                </div>
                            </div>
                            <div class="request-item">
                                <div class="request-info">
                                    <span class="request-title">Electrical Wiring</span>
                                    <span class="request-status scheduled">Scheduled</span>
                                </div>
                                <div class="request-technician">
                                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="Technician">
                                    <span>Mike Johnson</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="advanced-card service-history">
                        <div class="card-header">
                            <h3>Service History</h3>
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="history-chart">
                            <canvas id="historyChart"></canvas>
                        </div>
                        <div class="history-stats">
                            <div class="history-stat">
                                <span class="value">12</span>
                                <span class="label">Services Used</span>
                            </div>
                            <div class="history-stat">
                                <span class="value">4.8</span>
                                <span class="label">Avg Rating</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Why Choose HomeFix Pro Section -->
            <div class="why-choose-section">
                <!-- Animated Background Elements -->
                <div class="floating-elements">
                    <div class="float-element el-1"><i class="fas fa-tools"></i></div>
                    <div class="float-element el-2"><i class="fas fa-home"></i></div>
                    <div class="float-element el-3"><i class="fas fa-shield-alt"></i></div>
                    <div class="float-element el-4"><i class="fas fa-star"></i></div>
                </div>

                <div class="section-header">
                    <div class="header-icon pulse">
                        <i class="fas fa-crown"></i>
                    </div>
                    <h2 class="section-title gradient-text">Why Choose HomeFix Pro?</h2>
                    <p class="section-subtitle">We make home services simple, reliable, and professional across Addis Ababa</p>
                </div>

                <div class="features-grid">
                    <!-- Feature 1: Verified Professionals -->
                    <div class="feature-card" data-aos="fade-up">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon-bg"></div>
                            <i class="fas fa-user-shield feature-icon"></i>
                        </div>
                        <h3 class="feature-title">Verified Professionals</h3>
                        <p class="feature-description">All technicians are thoroughly verified, certified, and background-checked for your safety</p>
                        <div class="feature-badge">
                            <span class="badge-count">100%</span>
                            <span class="badge-text">Verified</span>
                        </div>
                        <div class="feature-wave"></div>
                    </div>

                    <!-- Feature 2: Quick Response -->
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon-bg"></div>
                            <i class="fas fa-bolt feature-icon"></i>
                        </div>
                        <h3 class="feature-title">Quick Response</h3>
                        <p class="feature-description">Get connected with available professionals in your area within hours, not days</p>
                        <div class="response-timer">
                            <i class="fas fa-clock"></i>
                            <span class="timer-text">Avg. 2 hours response</span>
                        </div>
                        <div class="feature-wave"></div>
                    </div>

                    <!-- Feature 3: Fair Pricing -->
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon-bg"></div>
                            <i class="fas fa-tag feature-icon"></i>
                        </div>
                        <h3 class="feature-title">Fair Pricing</h3>
                        <p class="feature-description">Transparent, upfront pricing with no hidden charges or surprise fees</p>
                        <div class="pricing-guarantee">
                            <i class="fas fa-check-circle"></i>
                            <span>Price Lock Guarantee</span>
                        </div>
                        <div class="feature-wave"></div>
                    </div>

                    <!-- Feature 4: Quality Guarantee -->
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon-bg"></div>
                            <i class="fas fa-award feature-icon"></i>
                        </div>
                        <h3 class="feature-title">Quality Guarantee</h3>
                        <p class="feature-description">We stand behind the quality of work with our satisfaction guarantee</p>
                        <div class="quality-badge">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <span class="rating">4.8/5.0</span>
                        </div>
                        <div class="feature-wave"></div>
                    </div>

                    <!-- Feature 5: Citywide Coverage -->
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon-bg"></div>
                            <i class="fas fa-map-marker-alt feature-icon"></i>
                        </div>
                        <h3 class="feature-title">Citywide Coverage</h3>
                        <p class="feature-description">Serving all subcities and woredas across Addis Ababa with local experts</p>
                        <div class="coverage-map">
                            <i class="fas fa-map"></i>
                            <span class="area-count">30+ Areas Covered</span>
                        </div>
                        <div class="feature-wave"></div>
                    </div>

                    <!-- Feature 6: 24/7 Support -->
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon-bg"></div>
                            <i class="fas fa-headset feature-icon"></i>
                        </div>
                        <h3 class="feature-title">24/7 Support</h3>
                        <p class="feature-description">Round-the-clock customer support to help with any issues or questions</p>
                        <div class="support-status">
                            <div class="status-indicator active"></div>
                            <span class="status-text">Online Now</span>
                        </div>
                        <div class="feature-wave"></div>
                    </div>
                </div>

                <!-- Statistics Banner -->
                <div class="stats-banner">
                    <div class="stats-container">
                        <div class="stat-item">
                            <div class="stat-value" data-count="5000">0</div>
                            <div class="stat-label">Happy Customers</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-value" data-count="98">0</div>
                            <div class="stat-label">Satisfaction Rate</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-value" data-count="250">0</div>
                            <div class="stat-label">Expert Technicians</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-value" data-count="30">0</div>
                            <div class="stat-label">Areas Covered</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Activity Feed -->
            <div class="activity-feed">
                <div class="feed-header">
                    <h3>Live Activity</h3>
                    <div class="live-indicator">
                        <div class="pulse-dot"></div>
                        <span>LIVE</span>
                    </div>
                </div>
                <div class="feed-content">
                    <div class="activity-stream">
                        <!-- Activities will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AOS Animation Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    
    <script>
        // Dashboard JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Time-based Greeting
            function updateGreeting() {
                const hour = new Date().getHours();
                const greetingElement = document.querySelector('.time-based-greeting');
                let greeting = 'Morning';
                
                if (hour >= 12 && hour < 17) {
                    greeting = 'Afternoon';
                } else if (hour >= 17 || hour < 5) {
                    greeting = 'Evening';
                }
                
                if (greetingElement) {
                    greetingElement.textContent = greeting;
                }
            }
            
            // Initialize AOS
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 800,
                    easing: 'ease-out-quad',
                    once: true,
                    offset: 100
                });
            }
            
            // Counter Animation for Stats
            function animateCounters() {
                const counters = document.querySelectorAll('.stat-value[data-count]');
                
                counters.forEach(counter => {
                    const target = +counter.getAttribute('data-count');
                    const increment = target / 100;
                    let current = 0;
                    
                    const updateCounter = () => {
                        if (current < target) {
                            current += increment;
                            counter.textContent = Math.ceil(current);
                            setTimeout(updateCounter, 20);
                        } else {
                            counter.textContent = target;
                        }
                    };
                    
                    updateCounter();
                });
            }
            
            // Live Requests Counter
            function updateLiveRequests() {
                const requestElement = document.getElementById('liveRequests');
                if (!requestElement) return;
                
                // Simulate live updates
                setInterval(() => {
                    const current = parseInt(requestElement.textContent);
                    const change = Math.floor(Math.random() * 3) - 1;
                    const newValue = Math.max(0, current + change);
                    requestElement.textContent = newValue;
                    
                    // Add animation
                    requestElement.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        requestElement.style.transform = 'scale(1)';
                    }, 300);
                }, 5000);
            }
            
            // Initialize Charts
            function initializeCharts() {
                const charts = document.querySelectorAll('canvas');
                charts.forEach(canvas => {
                    const ctx = canvas.getContext('2d');
                    if (!ctx) return;
                    
                    const gradient = ctx.createLinearGradient(0, 0, canvas.width, 0);
                    
                    if (canvas.id === 'requestsChart') {
                        gradient.addColorStop(0, 'rgba(245, 158, 11, 0.8)');
                        gradient.addColorStop(1, 'rgba(251, 191, 36, 0.4)');
                        drawSimpleLineChart(ctx, gradient, [5, 8, 12, 10, 15, 12]);
                    } else if (canvas.id === 'revenueChart') {
                        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.8)');
                        gradient.addColorStop(1, 'rgba(52, 211, 153, 0.4)');
                        drawSimpleLineChart(ctx, gradient, [1000, 1500, 1800, 2200, 2500, 2847]);
                    } else if (canvas.id === 'analyticsChart' || canvas.id === 'earningsChart' || canvas.id === 'historyChart') {
                        drawDoughnutChart(ctx);
                    }
                });
            }
            
            function drawSimpleLineChart(ctx, gradient, data) {
                const canvas = ctx.canvas;
                const padding = 5;
                const max = Math.max(...data);
                const step = (canvas.width - padding * 2) / (data.length - 1);
                const scale = (canvas.height - padding * 2) / max;
                
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                // Draw line
                ctx.beginPath();
                ctx.moveTo(padding, canvas.height - data[0] * scale - padding);
                
                for (let i = 1; i < data.length; i++) {
                    ctx.lineTo(padding + i * step, canvas.height - data[i] * scale - padding);
                }
                
                ctx.strokeStyle = gradient;
                ctx.lineWidth = 2;
                ctx.stroke();
                
                // Draw fill
                ctx.lineTo(canvas.width - padding, canvas.height - padding);
                ctx.lineTo(padding, canvas.height - padding);
                ctx.closePath();
                
                const fillGradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
                fillGradient.addColorStop(0, gradient.colorStops ? gradient.colorStops[0].color : 'rgba(245, 158, 11, 0.2)');
                fillGradient.addColorStop(1, gradient.colorStops ? gradient.colorStops[1].color.replace('0.4', '0.05') : 'rgba(251, 191, 36, 0.05)');
                
                ctx.fillStyle = fillGradient;
                ctx.fill();
            }
            
            function drawDoughnutChart(ctx) {
                const canvas = ctx.canvas;
                const centerX = canvas.width / 2;
                const centerY = canvas.height / 2;
                const radius = Math.min(centerX, centerY) - 5;
                
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                // Draw background circle
                ctx.beginPath();
                ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(59, 130, 246, 0.1)';
                ctx.fill();
                
                // Draw progress arc
                const progress = 0.75; // 75%
                ctx.beginPath();
                ctx.arc(centerX, centerY, radius, -Math.PI / 2, -Math.PI / 2 + (Math.PI * 2 * progress));
                ctx.strokeStyle = '#3b82f6';
                ctx.lineWidth = 4;
                ctx.stroke();
                
                // Draw percentage text
                ctx.font = 'bold 16px Arial';
                ctx.fillStyle = '#3b82f6';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(`${Math.round(progress * 100)}%`, centerX, centerY);
            }
            
            // Service Item Click Handler
            function initializeServiceItems() {
                const serviceItems = document.querySelectorAll('.service-item');
                serviceItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const service = this.getAttribute('data-service');
                        this.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            this.style.transform = '';
                            alert(`Opening ${service} service request...`);
                        }, 150);
                    });
                });
            }
            
            // Intersection Observer for animations
            function initIntersectionObserver() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            animateCounters();
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });
                
                const statsSection = document.querySelector('.stats-banner');
                if (statsSection) observer.observe(statsSection);
            }
            
            // Feature Card Hover Effects
            function initializeFeatureCards() {
                const cards = document.querySelectorAll('.feature-card');
                cards.forEach(card => {
                    card.addEventListener('mouseenter', () => {
                        card.style.zIndex = '10';
                    });
                    
                    card.addEventListener('mouseleave', () => {
                        card.style.zIndex = '1';
                    });
                });
            }
            
            // Notification Button Handler
            function initializeNotifications() {
                const notificationBtn = document.querySelector('.nav-btn:nth-child(1)');
                const notificationDot = document.querySelector('.notification-dot');
                
                if (notificationBtn && notificationDot) {
                    notificationBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        notificationDot.style.display = 'none';
                        this.style.transform = 'rotate(360deg)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 300);
                        
                        // Simulate notification popup
                        const notification = document.createElement('div');
                        notification.innerHTML = `
                            <div style="position: fixed; top: 80px; right: 20px; background: white; padding: 15px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 1000; max-width: 300px;">
                                <h4 style="margin-bottom: 10px; color: #3b82f6;">Notifications</h4>
                                <p>✅ New job assigned</p>
                                <p>⭐ Customer review received</p>
                                <p>📅 Schedule updated</p>
                            </div>
                        `;
                        document.body.appendChild(notification);
                        setTimeout(() => notification.remove(), 3000);
                    });
                }
            }
            
            // Modern Button Click Effects
            function initializeModernButtons() {
                const buttons = document.querySelectorAll('.modern-btn, .action-btn');
                buttons.forEach(button => {
                    button.addEventListener('click', function() {
                        this.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 150);
                    });
                });
            }
            
            // Generate Live Activity
            function generateLiveActivity() {
                const activities = [
                    "New service request: Plumbing repair",
                    "Technician John completed a job",
                    "Customer review submitted: ⭐⭐⭐⭐⭐",
                    "Payment received: $120",
                    "New technician registered",
                    "Schedule updated for tomorrow"
                ];
                
                const activityStream = document.querySelector('.activity-stream');
                if (!activityStream) return;
                
                // Clear existing content
                activityStream.innerHTML = '';
                
                // Add new activities
                activities.forEach((activity, index) => {
                    setTimeout(() => {
                        const activityItem = document.createElement('div');
                        activityItem.className = 'activity-item';
                        activityItem.style.cssText = `
                            padding: 10px;
                            margin: 5px 0;
                            background: rgba(255,255,255,0.9);
                            border-radius: 8px;
                            animation: fadeIn 0.5s ease;
                        `;
                        activityItem.innerHTML = `
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 8px; height: 8px; background: #3b82f6; border-radius: 50%;"></div>
                                <span>${activity}</span>
                                <span style="margin-left: auto; font-size: 12px; color: #64748b;">Just now</span>
                            </div>
                        `;
                        activityStream.prepend(activityItem);
                        
                        // Keep only 5 latest activities
                        if (activityStream.children.length > 5) {
                            activityStream.removeChild(activityStream.lastChild);
                        }
                    }, index * 2000);
                });
            }
            
            // Initialize everything
            function initDashboard() {
                updateGreeting();
                initializeCharts();
                updateLiveRequests();
                initializeServiceItems();
                initializeFeatureCards();
                initializeNotifications();
                initializeModernButtons();
                initIntersectionObserver();
                generateLiveActivity();
                
                // Update greeting every minute
                setInterval(updateGreeting, 60000);
                
                // Update activity every 10 seconds
                setInterval(generateLiveActivity, 10000);
                
                // Add CSS for fadeIn animation
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(10px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Start the dashboard
            initDashboard();
        });
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>