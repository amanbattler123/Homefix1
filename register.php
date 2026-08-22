<?php
session_start();
require_once 'includes/config.php';
require_once 'models/Database.php';
require_once 'controllers/AuthController.php';

// Update page metadata for SEO
$pageTitle = "Register - HomeFix Pro | Create Your Account for Premium Home Services";
$pageDescription = "Join HomeFix Pro as a homeowner or service technician. Register now for premium home services in Addis Ababa.";
$pageKeywords = "register, create account, homeowner registration, technician registration, Addis Ababa, join";

$db = new Database();
$authController = new AuthController($db->getConnection());

$message = '';
$role = isset($_GET['role']) ? $_GET['role'] : '';

// Addis Ababa Subcities and Woredas
$subcities = [
    'Arada', 'Addis Ketema', 'Kirkos', 'Gulele', 'Lideta', 
    'Bole', 'Yeka', 'Nifas Silk-Lafto', 'Kolfe Keranio', 
    'Akaki Kaliti'
];

$woredas = [
    'Arada' => ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4'],
    'Addis Ketema' => ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5'],
    'Kirkos' => ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5'],
    'Gulele' => ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5', 'Woreda 6'],
    'Lideta' => ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4'],
    'Bole' => ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5', 'Woreda 6', 'Woreda 7', 'Woreda 8'],
    'Yeka' => ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5', 'Woreda 6', 'Woreda 7', 'Woreda 8', 'Woreda 9', 'Woreda 10'],
    'Nifas Silk-Lafto' => ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5', 'Woreda 6', 'Woreda 7'],
    'Kolfe Keranio' => ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5', 'Woreda 6'],
    'Akaki Kaliti' => ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5', 'Woreda 6']
];

if($_POST){
    $result = $authController->register($_POST, $_FILES);
    
    if($result['success']) {
        // Store success message in session for login page
        $_SESSION['registration_success'] = $result['message'];
        $_SESSION['registered_role'] = $result['role'];
        
        // Redirect immediately to login page
        header("Location: login.php?registered=1&role=" . $result['role']);
        exit();
    } else {
        $message = '<div class="alert alert-danger">' . $result['message'] . '</div>';
    }
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

/* Enhanced Register Hero Section (same style as About page hero) */
.register-hero {
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    color: white;
    padding-top: 100px;
    padding-bottom: 50px;
}

.register-hero .container {
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

.register-badge {
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
    max-width: 700px;
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

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

select.form-control {
    cursor: pointer;
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

/* Form Layout Styles */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.location-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

/* Profile Image Styles */
.profile-image-container {
    text-align: center;
    margin-bottom: 1.5rem;
}

.profile-image-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--primary);
    margin-bottom: 1rem;
    box-shadow: var(--shadow-lg);
}

/* File Upload Styles */
.file-upload-container {
    margin-top: 0.5rem;
}

.file-preview {
    margin-top: 0.5rem;
}

.file-success {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: rgba(56, 161, 105, 0.1);
    border: 1px solid rgba(56, 161, 105, 0.2);
    border-radius: 6px;
    color: var(--success);
}

.file-success i {
    font-size: 1.2rem;
}

.file-success div {
    display: flex;
    flex-direction: column;
}

.file-success strong {
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.file-success span {
    font-size: 0.75rem;
    color: var(--gray-600);
}

.file-error {
    padding: 0.75rem;
    background: rgba(229, 62, 62, 0.1);
    border: 1px solid rgba(229, 62, 62, 0.2);
    border-radius: 6px;
    color: var(--danger);
    font-size: 0.875rem;
}

/* Terms and Conditions */
.terms-group {
    margin: 1.5rem 0;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: var(--gray-700);
}

.checkbox-label input[type="checkbox"] {
    margin-top: 0.25rem;
}

.checkbox-label a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.checkbox-label a:hover {
    text-decoration: underline;
}

/* Loading State */
.btn-loading {
    opacity: 0.7;
    cursor: not-allowed;
    position: relative;
}

.btn-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Technician Fields */
#technician-fields {
    padding: 1.5rem;
    background: var(--gray-50);
    border-radius: 8px;
    border: 1px solid var(--gray-200);
    margin-bottom: 1.5rem;
}

#technician-fields .form-group:last-child {
    margin-bottom: 0;
}

/* Small Text Helper */
small {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: var(--gray-500);
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
        max-width: 90%;
    }
    
    .form-row,
    .location-group {
        grid-template-columns: 1fr;
    }
    
    .profile-image-preview {
        width: 120px;
        height: 120px;
    }
}

@media (max-width: 480px) {
    .nav-container {
        padding: 0 1rem;
    }
    
    .register-hero .container,
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
    
    .profile-image-preview {
        width: 100px;
        height: 100px;
    }
}

/* Hide global footer on Registration page only */
.footer-modern {
    display: none !important;
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
                <a href="login.php" class="nav-link">Sign In</a>
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
                <a href="login.php" class="mobile-nav-link">Sign In</a>
                <a href="register.php" class="mobile-nav-link active">Register</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Register Hero Section (same style as About page) -->
<section class="register-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="register-badge">
                    <i class="fas fa-users"></i>
                    Join Our Community
                </div>
                
                <h1 class="hero-title">
                    Start Your <span class="title-accent">Journey</span>
                </h1>
                
                <p class="hero-description">
                    Join HomeFix Pro as a homeowner or service technician. Register now to access premium home services or start receiving jobs across Addis Ababa.
                </p>
                
                <div class="hero-actions">
                    <a href="#auth-form" class="btn btn-primary btn-large scroll-to">
                        <i class="fas fa-user-plus"></i>
                        Register Now
                    </a>
                    <a href="#role-info" class="btn btn-secondary btn-large scroll-to">
                        <i class="fas fa-info-circle"></i>
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
        <div class="scroll-text">Scroll to Register Form</div>
        <div class="scroll-arrow">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</section>

<!-- Auth Container -->
<div class="auth-container" id="auth-form">
    <div class="auth-form">
        <h2>Create Your Account</h2>
        <p>Join HomeFix Pro - Serving All of Addis Ababa</p>
        
        <?php echo $message; ?>
        
        <!-- Role Information -->
        <div id="role-info" style="margin-bottom: 2rem; padding: 1.5rem; background: var(--gray-50); border-radius: 8px; border: 1px solid var(--gray-200);">
            <h4 style="color: var(--primary); margin-bottom: 1rem;">Choose Your Role:</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="role-card" data-role="homeowner" style="padding: 1rem; background: white; border-radius: 6px; border: 1px solid var(--gray-200); cursor: pointer;">
                    <h5 style="color: var(--primary); margin-bottom: 0.5rem;"><i class="fas fa-home"></i> Homeowner</h5>
                    <p style="font-size: 0.875rem; color: var(--gray-600);">Book professional home services, track requests, and manage your home maintenance needs.</p>
                </div>
                <div class="role-card" data-role="technician" style="padding: 1rem; background: white; border-radius: 6px; border: 1px solid var(--gray-200); cursor: pointer;">
                    <h5 style="color: var(--primary); margin-bottom: 0.5rem;"><i class="fas fa-tools"></i> Technician</h5>
                    <p style="font-size: 0.875rem; color: var(--gray-600);">Receive job requests, grow your business, and provide professional services to homeowners.</p>
                </div>
            </div>
        </div>
        
        <form action="" method="POST" enctype="multipart/form-data" id="registration-form">
            <input type="hidden" name="form_submitted" value="1">
            <input type="hidden" name="role" id="role-input" value="<?php
                echo htmlspecialchars(
                    $role ?: (isset($_POST['role']) ? $_POST['role'] : 'homeowner'),
                    ENT_QUOTES,
                    'UTF-8'
                );
            ?>">
            
            <!-- Profile Photo -->
            <div class="form-group">
                <label>Profile Photo *</label>
                <div class="profile-image-container">
                    <img id="profile-preview" src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Profile Preview" class="profile-image-preview">
                    <input type="file" name="profile_photo" class="form-control" accept=".jpg,.jpeg,.png,.gif" required onchange="previewProfileImage(this)">
                    <small>Upload a clear profile photo (JPG, PNG, GIF - Max 2MB)</small>
                </div>
            </div>
            
            <!-- Name Fields -->
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" class="form-control" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" class="form-control" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" class="form-control" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).+">
                    <small>At least 8 characters, with letters, numbers, and a special character.</small>
                </div>
                
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="phone" class="form-control" placeholder="+2519XXXXXXXX or +2517XXXXXXXX" pattern="^\+251[79][0-9]{8}$" minlength="13" maxlength="13" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                    <small>Must start with +2517 or +2519 and have exactly 12 digits (13 characters including +).</small>
                </div>
            </div>
            
            <!-- Address Information -->
            <div class="form-group">
                <label>Full Address *</label>
                <textarea name="address" class="form-control" placeholder="House number, street, specific location..." required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>
            
            <!-- Location -->
            <div class="location-group">
                <div class="form-group">
                    <label>Subcity *</label>
                    <select name="subcity" class="form-control" required onchange="window.updateWoredas(this.value)">
                        <option value="">Select Subcity</option>
                        <?php foreach($subcities as $subcity): ?>
                            <option value="<?php echo $subcity; ?>" <?php echo (isset($_POST['subcity']) && $_POST['subcity'] == $subcity) ? 'selected' : ''; ?>>
                                <?php echo $subcity; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Woreda *</label>
                    <select name="woreda" class="form-control" required id="woreda-select">
                        <option value="">Select Woreda</option>
                        <?php if(isset($_POST['subcity']) && isset($_POST['woreda']) && isset($woredas[$_POST['subcity']])): ?>
                            <?php foreach($woredas[$_POST['subcity']] as $woreda): ?>
                                <option value="<?php echo $woreda; ?>" <?php echo ($_POST['woreda'] == $woreda) ? 'selected' : ''; ?>>
                                    <?php echo $woreda; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- Residence ID -->
            <div class="form-group">
                <label>Residence ID Document *</label>
                <div class="file-upload-container">
                    <input type="file" name="residence_id" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required onchange="validateResidenceID(this)">
                    <small>Upload a clear copy of your residence ID (PDF, JPG, PNG - Max 5MB)</small>
                    <div id="residence-id-preview" class="file-preview"></div>
                </div>
            </div>
            
            <!-- Technician Fields -->
            <div id="technician-fields" style="display: <?php echo ($role == 'technician' || (isset($_POST['role']) && $_POST['role'] == 'technician')) ? 'block' : 'none'; ?>">
                <div class="form-group">
                    <label>Profession *</label>
                    <select name="profession" class="form-control" id="profession-select">
                        <option value="">Select Your Profession</option>
                        <option value="Plumbing" <?php echo (isset($_POST['profession']) && $_POST['profession'] == 'Plumbing') ? 'selected' : ''; ?>>Plumbing</option>
                        <option value="Electrical" <?php echo (isset($_POST['profession']) && $_POST['profession'] == 'Electrical') ? 'selected' : ''; ?>>Electrical</option>
                        <option value="HVAC" <?php echo (isset($_POST['profession']) && $_POST['profession'] == 'HVAC') ? 'selected' : ''; ?>>HVAC</option>
                        <option value="Carpentry" <?php echo (isset($_POST['profession']) && $_POST['profession'] == 'Carpentry') ? 'selected' : ''; ?>>Carpentry</option>
                        <option value="Painting" <?php echo (isset($_POST['profession']) && $_POST['profession'] == 'Painting') ? 'selected' : ''; ?>>Painting</option>
                        <option value="Cleaning" <?php echo (isset($_POST['profession']) && $_POST['profession'] == 'Cleaning') ? 'selected' : ''; ?>>Cleaning</option>
                        <option value="Landscaping" <?php echo (isset($_POST['profession']) && $_POST['profession'] == 'Landscaping') ? 'selected' : ''; ?>>Landscaping</option>
                        <option value="Appliance Repair" <?php echo (isset($_POST['profession']) && $_POST['profession'] == 'Appliance Repair') ? 'selected' : ''; ?>>Appliance Repair</option>
                        <option value="Roofing" <?php echo (isset($_POST['profession']) && $_POST['profession'] == 'Roofing') ? 'selected' : ''; ?>>Roofing</option>
                        <option value="Handyman" <?php echo (isset($_POST['profession']) && $_POST['profession'] == 'Handyman') ? 'selected' : ''; ?>>Handyman</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>CBE Account Number *</label>
                    <input type="text" name="bank_account" class="form-control" placeholder="13 digit CBE account number" pattern="^[0-9]{13}$" minlength="13" maxlength="13" value="<?php echo isset($_POST['bank_account']) ? htmlspecialchars($_POST['bank_account']) : ''; ?>" required>
                    <small>Must be exactly 13 digits (CBE account number).</small>
                </div>
                
                <div class="form-group">
                    <label>Tele Birr Number *</label>
                    <input type="text" name="tele_birr" class="form-control" placeholder="+2519XXXXXXXX" pattern="^\+2519[0-9]{8}$" minlength="13" maxlength="13" value="<?php echo isset($_POST['tele_birr']) ? htmlspecialchars($_POST['tele_birr']) : ''; ?>" required>
                    <small>Your Tele Birr number for payments (Format: +2519XXXXXXXX)</small>
                </div>
                
                <div class="form-group">
                    <label>Certification Document *</label>
                    <div class="file-upload-container">
                        <input type="file" name="certification" class="form-control" accept=".pdf,.jpg,.jpeg,.png" id="certification-input" onchange="validateCertification(this)">
                        <small>Upload your professional certification (PDF, JPG, PNG - Max 5MB)</small>
                        <div id="certification-preview" class="file-preview"></div>
                    </div>
                </div>
            </div>
            
            <!-- Terms and Conditions -->
            <div class="form-group terms-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                    <span>I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a> *</span>
                </label>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary btn-block" id="submit-btn">
                <i class="fas fa-user-plus"></i>
                Create Account
            </button>
        </form>
        
        <p class="auth-link">
            Already have an account? 
            <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<!-- Scripts (identical to About page) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollToPlugin.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
// Addis Ababa Woredas data
const woredasData = {
    'Arada': ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4'],
    'Addis Ketema': ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5'],
    'Kirkos': ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5'],
    'Gulele': ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5', 'Woreda 6'],
    'Lideta': ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4'],
    'Bole': ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5', 'Woreda 6', 'Woreda 7', 'Woreda 8'],
    'Yeka': ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5', 'Woreda 6', 'Woreda 7', 'Woreda 8', 'Woreda 9', 'Woreda 10'],
    'Nifas Silk-Lafto': ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5', 'Woreda 6', 'Woreda 7'],
    'Kolfe Keranio': ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5', 'Woreda 6'],
    'Akaki Kaliti': ['Woreda 1', 'Woreda 2', 'Woreda 3', 'Woreda 4', 'Woreda 5', 'Woreda 6']
};

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
    
    tl.from('.register-badge', {
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

    // ---- Form helper functions ----
    function updateWoredas(subcity) {
        const woredaSelect = document.getElementById('woreda-select');
        if (!woredaSelect) return;

        woredaSelect.innerHTML = '<option value="">Select Woreda</option>';

        if (subcity && woredasData[subcity]) {
            woredasData[subcity].forEach(woreda => {
                const option = document.createElement('option');
                option.value = woreda;
                option.textContent = woreda;
                woredaSelect.appendChild(option);
            });
        }
    }

    function toggleTechnicianFields() {
        const roleInput = document.getElementById('role-input');
        const role = roleInput ? roleInput.value : 'homeowner';
        const techFields = document.getElementById('technician-fields');
        if (techFields) {
            techFields.style.display = (role === 'technician') ? 'block' : 'none';
        }

        // Only set required attributes if the fields are visible
        const professionField = document.getElementById('profession-select');
        const bankAccountField = document.querySelector('input[name="bank_account"]');
        const teleBirrField = document.querySelector('input[name="tele_birr"]');
        const certificationField = document.getElementById('certification-input');

        if (role === 'technician') {
            if (professionField) professionField.required = true;
            if (bankAccountField) bankAccountField.required = true;
            if (teleBirrField) teleBirrField.required = true;
            if (certificationField) certificationField.required = true;
        } else {
            if (professionField) {
                professionField.required = false;
                professionField.setCustomValidity('');
            }
            if (bankAccountField) {
                bankAccountField.required = false;
                bankAccountField.setCustomValidity('');
            }
            if (teleBirrField) {
                teleBirrField.required = false;
                teleBirrField.setCustomValidity('');
            }
            if (certificationField) {
                certificationField.required = false;
                certificationField.setCustomValidity('');
            }
        }
    }

    // Enforce hard 13-character limit on phone and TeleBirr fields
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            if (this.value.length > 13) {
                this.value = this.value.slice(0, 13);
            }
        });
    }

    const teleBirrInput = document.querySelector('input[name="tele_birr"]');
    if (teleBirrInput) {
        teleBirrInput.addEventListener('input', function() {
            if (this.value.length > 13) {
                this.value = this.value.slice(0, 13);
            }
        });
    }

    // Role card click handling
    const roleCards = document.querySelectorAll('.role-card');
    const roleInput = document.getElementById('role-input');
    if (roleCards && roleInput) {
        // Set initial active state
        roleCards.forEach(card => {
            if (card.getAttribute('data-role') === roleInput.value) {
                card.style.borderColor = 'var(--primary)';
                card.style.boxShadow = '0 0 0 2px rgba(26, 54, 93, 0.2)';
            }
        });

        roleCards.forEach(card => {
            card.addEventListener('click', function() {
                const selectedRole = this.getAttribute('data-role');
                roleInput.value = selectedRole;

                // Visual highlight
                roleCards.forEach(c => {
                    c.style.borderColor = 'var(--gray-200)';
                    c.style.boxShadow = 'none';
                });
                this.style.borderColor = 'var(--primary)';
                this.style.boxShadow = '0 0 0 2px rgba(26, 54, 93, 0.2)';

                // Toggle technician fields
                toggleTechnicianFields();
            });
        });
    }

    // Make updateWoredas available for inline onchange handlers
    window.updateWoredas = updateWoredas;

    // Initialize technician fields and woredas on load
    toggleTechnicianFields();

    const subcitySelect = document.querySelector('select[name="subcity"]');
    if (subcitySelect && subcitySelect.value) {
        updateWoredas(subcitySelect.value);
    }
});

// File validation functions
function previewProfileImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function validateResidenceID(input) {
    const file = input.files[0];
    const preview = document.getElementById('residence-id-preview');
    
    if (file) {
        // Check file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            preview.innerHTML = '<div class="file-error">File is too large. Maximum size is 5MB.</div>';
            input.value = '';
            return;
        }
        
        // Check file type
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            preview.innerHTML = '<div class="file-error">Please upload PDF, JPG, or PNG files only.</div>';
            input.value = '';
            return;
        }
        
        // Show file info
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        preview.innerHTML = `
            <div class="file-success">
                <i class="fas fa-file-upload"></i>
                <div>
                    <strong>${file.name}</strong>
                    <span>${fileSize} MB</span>
                </div>
            </div>
        `;
    } else {
        preview.innerHTML = '';
    }
}

function validateCertification(input) {
    const file = input.files[0];
    const preview = document.getElementById('certification-preview');
    
    if (file) {
        // Check file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            preview.innerHTML = '<div class="file-error">File is too large. Maximum size is 5MB.</div>';
            input.value = '';
            return;
        }
        
        // Check file type
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            preview.innerHTML = '<div class="file-error">Please upload PDF, JPG, or PNG files only.</div>';
            input.value = '';
            return;
        }
        
        // Show file info
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        preview.innerHTML = `
            <div class="file-success">
                <i class="fas fa-file-certificate"></i>
                <div>
                    <strong>${file.name}</strong>
                    <span>${fileSize} MB</span>
                </div>
            </div>
        `;
    } else {
        preview.innerHTML = '';
    }
}

// Form submission handling
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registration-form');
    const submitBtn = document.getElementById('submit-btn');

    if (!form || !submitBtn) {
        return;
    }
    
    form.addEventListener('submit', function(e) {
        // Simple validation - just check if form is valid
        if (!form.checkValidity()) {
            e.preventDefault();
            form.reportValidity();
            return false;
        }
        
        // Show loading state
        submitBtn.classList.add('btn-loading');
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Creating Account...';
        
        // Allow form to submit normally
        return true;
    });
});
</script>

<?php include 'includes/footer.php'; ?>