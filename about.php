<?php
session_start();
require_once 'includes/config.php';
require_once 'models/Database.php';

$pageTitle = "About Us - HomeFix Pro | Premium Home Services in Addis Ababa";
$pageDescription = "Learn about HomeFix Pro's mission, vision, and why we're the trusted choice for professional home services in Addis Ababa.";
$pageKeywords = "about us, home services, Addis Ababa, mission, vision, trusted professionals";

$db = new Database();
$conn = $db->getConnection();

// Local image list (10 total) - same as services page
$photos = [
    'assets/photos/photo1.jpg',
    'assets/photos/photo10.jpg',
    'assets/photos/photo9.jpg',
    'assets/photos/photo8.jpg',
    'assets/photos/photo7.jpg',
    'assets/photos/photo6.jpg',
    'assets/photos/photo5.jpg',
    'assets/photos/photo4.jpg',
    'assets/photos/photo3.jpg',
    'assets/photos/photo2.jpg'
];
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* COPY ALL THE SAME CSS STYLES FROM SERVICES PAGE */
/* Premium Color Scheme */
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

/* Premium Background with Parallax */
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

/* Floating Elements */
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

/* Enhanced Navigation */
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

/* Mobile Menu */
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

/* Buttons */
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

/* Enhanced Services Hero Section */
.services-hero {
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    color: white;
    padding-top: 100px;
    padding-bottom: 50px;
}

.services-hero .container {
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

.service-badge {
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

/* Scroll Indicator */
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

/* Your Original About Page Styles */
.page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 4rem 2rem;
    background: white;
}

.content-section {
    margin-bottom: 4rem;
}

.section-header {
    text-align: center;
    margin-bottom: 2rem;
}

.section-header h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 1rem;
}

.section-content {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.section-content p {
    font-size: 1.125rem;
    color: var(--gray-600);
    line-height: 1.7;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.feature-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--gray-200);
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: var(--gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: white;
    font-size: 2rem;
}

.feature-card h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 1rem;
}

.feature-card p {
    color: var(--gray-600);
    line-height: 1.6;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.stat-card-about {
    background: var(--gradient-primary);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: var(--shadow-lg);
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 1rem;
    opacity: 0.9;
}

/* Addis Ababa Map Section */
.map-section {
    padding: 4rem 0;
    background: var(--gray-50);
}

.map-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.map-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.map-visual {
    position: relative;
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: var(--shadow-xl);
    border: 1px solid var(--gray-200);
}

.map-image {
    width: 100%;
    height: 400px;
    background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.map-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(26, 54, 93, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.map-pin {
    position: absolute;
    width: 40px;
    height: 40px;
    background: #e53e3e;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    box-shadow: 0 4px 12px rgba(229, 62, 62, 0.4);
    animation: pulse 2s infinite;
}

.map-pin::after {
    content: '';
    position: absolute;
    width: 60px;
    height: 60px;
    border: 2px solid #e53e3e;
    border-radius: 50%;
    animation: ripple 2s infinite;
}

@keyframes ripple {
    0% {
        transform: scale(0.8);
        opacity: 1;
    }
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

.map-info {
    padding: 1rem 0;
}

.map-info h3 {
    font-size: 1.5rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.coverage-list {
    list-style: none;
    margin-bottom: 2rem;
}

.coverage-list li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    color: var(--gray-600);
}

.coverage-list i {
    color: var(--success);
    font-size: 1.1rem;
}

.coverage-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 2rem;
}

.coverage-stat {
    text-align: center;
    padding: 1rem;
    background: var(--gray-100);
    border-radius: 8px;
}

.coverage-stat .number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.25rem;
}

.coverage-stat .label {
    font-size: 0.875rem;
    color: var(--gray-600);
}

/* Service Areas Section */
.service-areas {
    padding: 4rem 0;
    background: white;
}

.areas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.area-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    text-align: center;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
    transition: all 0.3s ease;
}

.area-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary);
}

.area-card i {
    font-size: 2rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.area-card h4 {
    color: var(--primary);
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.area-card p {
    color: var(--gray-600);
    font-size: 0.9rem;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .hero-content {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 3rem;
    }
    
    .services-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }
    
    .cta-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .map-content {
        grid-template-columns: 1fr;
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
    
    .section-title {
        font-size: 2.5rem;
    }
    
    .hero-actions {
        flex-direction: column;
    }
    
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .cta-features {
        flex-direction: column;
        gap: 1rem;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .coverage-stats {
        grid-template-columns: 1fr;
    }
    
    .areas-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .nav-container {
        padding: 0 1rem;
    }
    
    .container {
        padding: 0 1rem;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .service-card {
        margin: 0;
    }
    
    .service-content {
        padding: 1.5rem;
    }
    
    .service-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .btn-book {
        width: 100%;
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .areas-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Premium Background with Parallax Effect -->
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

<!-- Premium Navigation -->
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
            <a href="about.php" class="nav-link active">About</a>
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
                <a href="login.php" class="btn btn-text">Sign In</a>
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
        <a href="about.php" class="mobile-nav-link active">About</a>
        <a href="contact.php" class="mobile-nav-link">Contact</a>
        <div class="mobile-nav-actions">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn btn-secondary btn-full">Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-text btn-full">Sign In</a>
                <a href="register.php" class="btn btn-primary btn-full">Get Started</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- About Hero Section - Clean and Simple -->
<section class="services-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="service-badge">
                    <i class="fas fa-star"></i>
                    Your Trusted Home Service Partner
                </div>
                
                <h1 class="hero-title">
                    About <span class="title-accent">HomeFix Pro</span>
                </h1>
                
                <p class="hero-description">
                    Learn about our mission, vision, and why thousands of homeowners in Addis Ababa trust us for their home service needs.
                </p>
                
                <div class="hero-actions">
                    <a href="#about-content" class="btn btn-primary btn-large scroll-to">
                        <i class="fas fa-book-open"></i>
                        Our Story
                    </a>
                    <a href="contact.php" class="btn btn-secondary btn-large">
                        <i class="fas fa-envelope"></i>
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
        <div class="scroll-text">Scroll to Learn More</div>
        <div class="scroll-arrow">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</section>

<!-- Your Original About Content Starts Here -->
<div class="page-container" id="about-content">
    <div class="content-section">
        <div class="section-header">
            <h2>Our Mission</h2>
        </div>
        <div class="section-content">
            <p>To make home services accessible, reliable, and professional by connecting homeowners with certified technicians while providing technicians with fair opportunities to grow their business.</p>
        </div>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h2>Our Vision</h2>
        </div>
        <div class="section-content">
            <p>To become the most trusted home service platform worldwide, known for quality, reliability, and customer satisfaction.</p>
        </div>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h2>Why Choose HomeFix Pro?</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Verified Professionals</h3>
                <p>All our technicians are carefully vetted, trained, and ID-verified here in Ethiopia.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Quick Response</h3>
                <p>Get connected with nearby technicians in Addis Ababa as soon as you submit your request.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3>Fair Pricing</h3>
                <p>Transparent pricing in ETB with no hidden costs or surprise charges.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3>Quality Guarantee</h3>
                <p>We stand behind the quality of every service and help if anything is not right.</p>
            </div>
        </div>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h2>Our Impact</h2>
        </div>
        <div class="stats-grid">
            <div class="stat-card-about">
                <div class="stat-number">500+</div>
                <div class="stat-label">Verified Technicians</div>
            </div>
            <div class="stat-card-about">
                <div class="stat-number">10,000+</div>
                <div class="stat-label">Happy Customers</div>
            </div>
            <div class="stat-card-about">
                <div class="stat-number">50+</div>
                <div class="stat-label">Cities Served</div>
            </div>
            <div class="stat-card-about">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Customer Support</div>
            </div>
        </div>
    </div>
</div>

<!-- Addis Ababa Map Section -->
<section class="map-section">
    <div class="map-container">
        <div class="section-header">
            <h2>Serving All of Addis Ababa</h2>
            <p>We provide comprehensive home services across all subcities and woredas in Ethiopia's capital</p>
        </div>
        
        <div class="map-content">
            <div class="map-visual">
                <div class="map-image">
                    <div class="map-overlay">
                        <div class="map-pin">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="map-info">
                    <h3>Citywide Coverage</h3>
                    <p>HomeFix Pro serves homeowners and businesses across all 10 subcities of Addis Ababa with reliable, professional home services.</p>
                    
                    <ul class="coverage-list">
                        <li><i class="fas fa-check-circle"></i> All 10 subcities covered</li>
                        <li><i class="fas fa-check-circle"></i> Emergency services available</li>
                        <li><i class="fas fa-check-circle"></i> Same-day service in most areas</li>
                        <li><i class="fas fa-check-circle"></i> Local technicians in every neighborhood</li>
                    </ul>
                    
                    <div class="coverage-stats">
                        <div class="coverage-stat">
                            <span class="number">10</span>
                            <span class="label">Subcities</span>
                        </div>
                        <div class="coverage-stat">
                            <span class="number">100+</span>
                            <span class="label">Woredas</span>
                        </div>
                        <div class="coverage-stat">
                            <span class="number">24/7</span>
                            <span class="label">Availability</span>
                        </div>
                        <div class="coverage-stat">
                            <span class="number">1-3h</span>
                            <span class="label">Response Time</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="map-info">
                <h3>Our Service Areas</h3>
                <p>We're proud to serve every corner of Addis Ababa, from Bole to Kirkos, ensuring quality home services are accessible to all residents.</p>
                
                <div class="areas-grid">
                    <div class="area-card">
                        <i class="fas fa-building"></i>
                        <h4>Bole</h4>
                        <p>International area with premium services</p>
                    </div>
                    <div class="area-card">
                        <i class="fas fa-city"></i>
                        <h4>Kirkos</h4>
                        <p>Central business district coverage</p>
                    </div>
                    <div class="area-card">
                        <i class="fas fa-home"></i>
                        <h4>Yeka</h4>
                        <p>Residential area specialists</p>
                    </div>
                    <div class="area-card">
                        <i class="fas fa-university"></i>
                        <h4>Arada</h4>
                        <p>Historic district services</p>
                    </div>
                    <div class="area-card">
                        <i class="fas fa-industry"></i>
                        <h4>Gulele</h4>
                        <p>Industrial and residential mix</p>
                    </div>
                    <div class="area-card">
                        <i class="fas fa-tree"></i>
                        <h4>Kolfe</h4>
                        <p>Suburban area coverage</p>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="services.php" class="btn btn-primary">
                        <i class="fas fa-search-location"></i>
                        Find Services in Your Area
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollToPlugin.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
// Enhanced JavaScript with more functionality
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
    
    tl.from('.service-badge', {
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

    // Add animation to content sections on scroll
    gsap.utils.toArray('.content-section, .feature-card, .stat-card-about, .map-visual, .area-card').forEach(element => {
        gsap.from(element, {
            scrollTrigger: {
                trigger: element,
                start: 'top 80%',
                end: 'bottom 20%',
                toggleActions: 'play none none reverse'
            },
            y: 50,
            opacity: 0,
            duration: 1,
            ease: 'power3.out'
        });
    });

    // Map pin animation
    const mapPin = document.querySelector('.map-pin');
    if (mapPin) {
        setInterval(() => {
            mapPin.style.transform = 'scale(1.1)';
            setTimeout(() => {
                mapPin.style.transform = 'scale(1)';
            }, 300);
        }, 2000);
    }
});
</script>