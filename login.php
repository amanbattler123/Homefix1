<?php
session_start();
require_once 'includes/config.php';
require_once 'models/Database.php';
require_once 'controllers/AuthController.php';

// Update page metadata for SEO
$pageTitle = "Login - HomeFix Pro | Secure Access to Your Account";
$pageDescription = "Login to your HomeFix Pro account to manage service requests, track tasks, and access premium home services in Addis Ababa.";
$pageKeywords = "login, account access, secure login, homeowner login, technician login, Addis Ababa";

$db = new Database();
$authController = new AuthController($db->getConnection());

$message = '';

if(isset($_SESSION['registration_success'])){
    $message = '<div class="alert alert-success">' . $_SESSION['registration_success'] . '</div>';
    unset($_SESSION['registration_success'], $_SESSION['registered_role']);
}

if(isset($_SESSION['verification_success'])){
    $message = '<div class="alert alert-success">' . $_SESSION['verification_success'] . '</div>';
    unset($_SESSION['verification_success']);
}

if($_POST){
    $result = $authController->login($_POST['email'], $_POST['password']);
    
    if($result['success']) {
        // Redirect based on role
        if($result['role'] == 'admin'){
            header("Location: views/admin/dashboard.php");
            exit();
        } elseif($result['role'] == 'technician'){
            header("Location: technician_dashboard.php");
            exit();
        } elseif($result['role'] == 'homeowner'){
            header("Location: homeowner_dashboard.php");
            exit();
        }
    } else {
        $message = '<div class="alert alert-danger">' . $result['message'] . '</div>';
    }
}

if(isset($_GET['verified']) && $_GET['verified'] === '1'){
    $message = '<div class="alert alert-success">Your email has been verified. Please login.</div>';
}

?>
<style>
/* Premium Color Scheme (same as About page) */
:root {
    --primary: #1a365d;
    --primary-dark: #0f2547;
    --primary-light: #2d4a7a;
    --secondary: #2b6cb0;
    --accent: #3182ce;
    --accent-light: #63b3ed;
    --success: #38a169;
    --warning: #d69e2e;
    --danger: #e53e3e;
    --dark: #1a202c;
    --darker: #0f141e;
    --light: #f7fafc;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    --gradient-primary: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    --gradient-accent: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
    --gradient-dark: linear-gradient(135deg, var(--dark) 0%, var(--darker) 100%);
    --gradient-gold: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

/* Reset and Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: var(--gray-800);
    line-height: 1.6;
    background: white;
    overflow-x: hidden;
}

/* Premium Background with Parallax (identical to About page) */
.premium-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -2;
    overflow: hidden;
}

.bg-image-parallax {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 120%;
    background-image: url('https://images.unsplash.com/photo-1581578731548-c64695cc6952?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    filter: brightness(0.3);
    transform: translateZ(0);
}

.bg-gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg,
        rgba(26, 54, 93, 0.95) 0%,
        rgba(43, 108, 176, 0.85) 50%,
        rgba(49, 130, 206, 0.8) 100%);
}

.bg-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image:
        radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 2px, transparent 0),
        radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.05) 1px, transparent 0);
    background-size: 50px 50px, 30px 30px;
}

/* Floating Elements (identical to About page) */
.floating-elements {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.floating-element {
    position: absolute;
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.3);
    font-size: 1.5rem;
    animation: float 6s ease-in-out infinite;
}

.floating-element.el-1 {
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.floating-element.el-2 {
    top: 60%;
    left: 85%;
    animation-delay: 1s;
}

.floating-element.el-3 {
    top: 80%;
    left: 15%;
    animation-delay: 2s;
}

.floating-element.el-4 {
    top: 40%;
    left: 80%;
    animation-delay: 3s;
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

/* Enhanced Navigation (identical to About page) */
.premium-nav {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding: 1rem 0;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    transition: all 0.3s ease;
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.nav-brand {
    display: flex;
    align-items: center;
}

.logo-wrapper {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-icon {
    width: 44px;
    height: 44px;
    background: var(--gradient-primary);
    border-radius: 999px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: var(--shadow-md);
    position: relative;
    overflow: hidden;
}

.logo-icon-top {
    font-size: 0.75rem;
    opacity: 0.95;
    margin-bottom: -2px;
}

.logo-initials {
    font-size: 0.9rem;
    font-weight: 800;
    letter-spacing: 0.08em;
}

.brand-text {
    display: flex;
    flex-direction: column;
}

.brand-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    line-height: 1.2;
}

.brand-tagline {
    font-size: 0.75rem;
    color: var(--gray-500);
    font-weight: 500;
    letter-spacing: 0.5px;
}

.nav-menu {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.nav-link {
    color: var(--gray-700);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    padding: 0.5rem 0;
}

.nav-link:hover,
.nav-link.active {
    color: var(--primary);
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--primary);
    transition: width 0.3s ease;
}

.nav-link:hover::after,
.nav-link.active::after {
    width: 100%;
}

.nav-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.mobile-menu-btn {
    display: none;
    flex-direction: column;
    gap: 4px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    transition: all 0.3s ease;
}

.mobile-menu-btn span {
    width: 20px;
    height: 2px;
    background: var(--gray-700);
    transition: all 0.3s ease;
}

.mobile-menu-btn.active span:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
}

.mobile-menu-btn.active span:nth-child(2) {
    opacity: 0;
}

.mobile-menu-btn.active span:nth-child(3) {
    transform: rotate(-45deg) translate(7px, -6px);
}

/* Mobile Menu (identical to About page) */
.mobile-menu {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    z-index: 999;
    display: none;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.mobile-menu.active {
    display: flex;
}

.mobile-menu-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    text-align: center;
}

.mobile-nav-link {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--gray-800);
    text-decoration: none;
    transition: color 0.3s ease;
}

.mobile-nav-link:hover,
.mobile-nav-link.active {
    color: var(--primary);
}

.mobile-nav-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-full {
    width: 100%;
    justify-content: center;
}

/* Buttons (identical to About page) */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 0.875rem;
    position: relative;
    overflow: hidden;
}

.btn-primary {
    background: var(--gradient-primary);
    color: white;
    box-shadow: var(--shadow-md);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-secondary {
    background: white;
    color: var(--primary);
    border: 2px solid var(--gray-200);
    box-shadow: var(--shadow-sm);
}

.btn-secondary:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.btn-outline {
    background: transparent;
    border: 2px solid var(--gray-300);
    color: var(--gray-700);
}

.btn-outline:hover {
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
}

.btn-text {
    background: transparent;
    color: var(--gray-600);
    padding: 0.75rem 1rem;
}

.btn-text:hover {
    color: var(--primary);
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1rem;
}

.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
}

/* Enhanced Login Hero Section (same style as About page hero) */
.login-hero {
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    color: white;
    padding-top: 100px;
    padding-bottom: 50px;
}

.login-hero .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    width: 100%;
}

.hero-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 4rem;
    align-items: center;
    text-align: center;
}

.login-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-size: 0.875rem;
    margin-bottom: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.hero-title {
    font-size: 4rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 1.5rem;
}

.title-accent {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-description {
    font-size: 1.5rem;
    opacity: 0.9;
    margin-bottom: 3rem;
    line-height: 1.6;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    justify-content: center;
}

/* Scroll Indicator (same as About page) */
.scroll-indicator {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
    animation: bounce 2s infinite;
}

.scroll-text {
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.scroll-arrow i {
    font-size: 1.5rem;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0) translateX(-50%); }
    40% { transform: translateY(-10px) translateX(-50%); }
    60% { transform: translateY(-5px) translateX(-50%); }
}

/* Auth Container Styles */
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    background: white;
    position: relative;
}

.auth-form {
    width: 100%;
    max-width: 450px;
    padding: 3rem;
    background: white;
    border-radius: 16px;
    box-shadow: var(--shadow-2xl);
    border: 1px solid var(--gray-200);
    margin-top: -100px;
    position: relative;
    z-index: 1;
}

.auth-form h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
    text-align: center;
}

.auth-form > p {
    text-align: center;
    color: var(--gray-600);
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 1rem;
    border: 2px solid var(--gray-300);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
}

.form-control::placeholder {
    color: var(--gray-400);
}

.btn-block {
    width: 100%;
    display: block;
    text-align: center;
}

.auth-link {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--gray-200);
    color: var(--gray-600);
}

.auth-link a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.auth-link a:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    border: 1px solid transparent;
}

.alert-success {
    background-color: rgba(56, 161, 105, 0.1);
    border-color: rgba(56, 161, 105, 0.2);
    color: var(--success);
}

.alert-danger {
    background-color: rgba(229, 62, 62, 0.1);
    border-color: rgba(229, 62, 62, 0.2);
    color: var(--danger);
}

.demo-accounts {
    margin-top: 2rem;
    padding: 1.5rem;
    background: var(--gray-50);
    border-radius: 8px;
    border: 1px solid var(--gray-200);
}

.demo-accounts h4 {
    font-size: 1rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.demo-accounts p {
    font-size: 0.875rem;
    color: var(--gray-600);
    margin-bottom: 0.75rem;
    line-height: 1.5;
}

.demo-accounts strong {
    color: var(--gray-800);
}

/* Role Selection */
.role-selection {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    border-bottom: 1px solid var(--gray-200);
    padding-bottom: 1rem;
}

.role-tab {
    flex: 1;
    text-align: center;
    padding: 0.75rem;
    background: var(--gray-100);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.role-tab:hover {
    background: var(--gray-200);
}

.role-tab.active {
    background: var(--gradient-primary);
    color: white;
    border-color: var(--primary);
}

.role-tab i {
    display: block;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

/* Social Login */
.social-login {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--gray-200);
}

.social-login p {
    text-align: center;
    color: var(--gray-600);
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

.social-buttons {
    display: flex;
    gap: 1rem;
}

.social-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.social-btn.google {
    background: white;
    color: var(--gray-700);
    border: 1px solid var(--gray-300);
}

.social-btn.google:hover {
    border-color: var(--danger);
    color: var(--danger);
}

.social-btn.facebook {
    background: #1877F2;
    color: white;
    border: 1px solid #1877F2;
}

.social-btn.facebook:hover {
    background: #166FE5;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .hero-content {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 3rem;
    }
}

@media (max-width: 768px) {
    .nav-menu {
        display: none;
    }
    
    .mobile-menu-btn {
        display: flex;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-description {
        font-size: 1.25rem;
    }
    
    .hero-actions {
        flex-direction: column;
    }
    
    .auth-form {
        padding: 2rem;
        margin-top: -50px;
    }
    
    .social-buttons {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .nav-container {
        padding: 0 1rem;
    }
    
    .login-hero .container,
    .auth-container {
        padding: 0 1rem;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .auth-form {
        padding: 1.5rem;
        margin-top: -30px;
    }
    
    .auth-form h2 {
        font-size: 1.5rem;
    }
    
    .role-selection {
        flex-direction: column;
    }
}
</style>

<!-- Premium Background with Parallax Effect (same as About page) -->
<div class="premium-background">
    <div class="bg-gradient-overlay"></div>
    <div class="bg-pattern"></div>
    <div class="bg-image-parallax"></div>
    <div class="floating-elements">
        <div class="floating-element el-1"><i class="fas fa-tools"></i></div>
        <div class="floating-element el-2"><i class="fas fa-home"></i></div>
        <div class="floating-element el-3"><i class="fas fa-wrench"></i></div>
        <div class="floating-element el-4"><i class="fas fa-cog"></i></div>
    </div>
</div>

<!-- Premium Navigation (identical to About page) -->
<nav class="premium-nav">
    <div class="nav-container">
        <div class="nav-brand">
            <div class="logo-wrapper">
                <div class="logo-icon">
                    <i class="fas fa-tools logo-icon-top"></i>
                    <span class="logo-initials">HF</span>
                </div>
                <div class="brand-text">
                    <span class="brand-name">HomeFix Pro</span>
                    <span class="brand-tagline">Premium Home Services</span>
                </div>
            </div>
        </div>
        
        <div class="nav-menu">
            <a href="index.php" class="nav-link">Home</a>
            <a href="services.php" class="nav-link">Services</a>
            <a href="about.php" class="nav-link">About</a>
            <a href="contact.php" class="nav-link">Contact</a>
        </div>
        
        <div class="nav-actions">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="logout.php" class="btn btn-text">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="nav-link active">Sign In</a>
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-rocket"></i>
                    Get Started
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-container">
        <a href="index.php" class="mobile-nav-link">Home</a>
        <a href="services.php" class="mobile-nav-link">Services</a>
        <a href="about.php" class="mobile-nav-link">About</a>
        <a href="contact.php" class="mobile-nav-link">Contact</a>
        <div class="mobile-nav-actions">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn btn-secondary btn-full">Dashboard</a>
                <a href="logout.php" class="btn btn-primary btn-full">Logout</a>
            <?php else: ?>
                <a href="login.php" class="mobile-nav-link active">Sign In</a>
                <a href="register.php" class="btn btn-primary btn-full">Get Started</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Login Hero Section (same style as About page) -->
<section class="login-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="login-badge">
                    <i class="fas fa-user-shield"></i>
                    Secure Access to Your Account
                </div>
                
                <h1 class="hero-title">
                    Welcome <span class="title-accent">Back</span>
                </h1>
                
                <p class="hero-description">
                    Login to your HomeFix Pro account to manage service requests, track tasks, and access premium home services in Addis Ababa.
                </p>
                
                <div class="hero-actions">
                    <a href="#auth-form" class="btn btn-primary btn-large scroll-to">
                        <i class="fas fa-sign-in-alt"></i>
                        Login Now
                    </a>
                    <a href="register.php" class="btn btn-secondary btn-large">
                        <i class="fas fa-user-plus"></i>
                        Create Account
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
        <div class="scroll-text">Scroll to Login Form</div>
        <div class="scroll-arrow">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</section>

<!-- Auth Container -->
<div class="auth-container" id="auth-form">
    <div class="auth-form">
        <h2>Login to Your Account</h2>
        <p>Access your HomeFix Pro dashboard</p>
        
        <?php echo $message; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i>
                Login to Account
            </button>
            <div class="form-group" style="margin-top: 1rem; text-align: right;">
                <a href="forgot_password.php" style="font-size: 0.875rem; color: var(--primary); text-decoration: none;">Forgot your password?</a>
            </div>
        </form>
        
        <p class="auth-link">
            Don't have an account? 
            <a href="register.php">Create one now</a>
        </p>
    </div>
</div>

<!-- Scripts (identical to About page) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollToPlugin.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
// Enhanced JavaScript with more functionality (same as About page)
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });

    // Initialize GSAP and ScrollTrigger
    gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

    // Hero section animations
    const tl = gsap.timeline();
    
    tl.from('.login-badge', {
        duration: 1,
        y: 50,
        opacity: 0,
        ease: "power3.out"
    })
    .from('.hero-title', {
        duration: 1.2,
        y: 100,
        opacity: 0,
        ease: "power3.out"
    }, "-=0.5")
    .from('.hero-description', {
        duration: 1,
        y: 50,
        opacity: 0,
        ease: "power3.out"
    }, "-=0.3")
    .from('.hero-actions', {
        duration: 1,
        y: 50,
        opacity: 0,
        ease: "power3.out"
    }, "-=0.3");

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const nav = document.querySelector('.premium-nav');
        if (window.scrollY > 100) {
            nav.style.background = 'rgba(255, 255, 255, 0.98)';
            nav.style.boxShadow = 'var(--shadow-lg)';
        } else {
            nav.style.background = 'rgba(255, 255, 255, 0.95)';
            nav.style.boxShadow = 'none';
        }
    });

    // Mobile menu functionality
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        });
    }

    // Close mobile menu when clicking on a link
    const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
    mobileNavLinks.forEach(link => {
        link.addEventListener('click', function() {
            mobileMenu.classList.remove('active');
            mobileMenuBtn.classList.remove('active');
            document.body.style.overflow = '';
        });
    });

    // Scroll to functionality
    const scrollToButtons = document.querySelectorAll('.scroll-to');
    scrollToButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                gsap.to(window, {
                    duration: 1,
                    scrollTo: {
                        y: targetElement,
                        offsetY: 100
                    },
                    ease: "power2.inOut"
                });
            }
        });
    });

    // Parallax effect for background
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallax = document.querySelector('.bg-image-parallax');
        if (parallax) {
            parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });

    // Add animation to auth form
    const authForm = document.querySelector('.auth-form');
    if (authForm) {
        gsap.from(authForm, {
            scrollTrigger: {
                trigger: authForm,
                start: 'top 80%',
                end: 'bottom 20%',
                toggleActions: 'play none none reverse'
            },
            y: 50,
            opacity: 0,
            duration: 1,
            ease: 'power3.out'
        });
    }
});
</script>