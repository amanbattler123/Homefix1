<?php
session_start();
require_once 'includes/config.php';
require_once 'models/Database.php';

$pageTitle = "HomeFix Pro - Trusted Home Services";

$db = new Database();

// Get popular services with recognizable icons
$popularServices = [
    ['name' => 'Plumbing', 'icon' => 'fa-wrench', 'description' => 'Pipe repairs, installations and maintenance'],
    ['name' => 'Electrical', 'icon' => 'fa-plug', 'description' => 'Wiring, fixtures and electrical repairs'],
    ['name' => 'HVAC', 'icon' => 'fa-thermometer-half', 'description' => 'Heating, ventilation and air conditioning'],
    ['name' => 'Carpentry', 'icon' => 'fa-hammer', 'description' => 'Furniture, doors and woodwork'],
    ['name' => 'Painting', 'icon' => 'fa-paint-roller', 'description' => 'Interior and exterior painting'],
    ['name' => 'Cleaning', 'icon' => 'fa-broom', 'description' => 'Deep cleaning and maintenance']
];
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* Premium Navigation Styles - Same as About Page */
/* Base color scheme and typography (matching About/Contact pages) */
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
}

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
    background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%);
    border-radius: 999px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
    color: #1a365d;
    line-height: 1.2;
}

.brand-tagline {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
    letter-spacing: 0.5px;
}

.nav-menu {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.nav-link {
    color: #374151;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    padding: 0.5rem 0;
}

.nav-link:hover,
.nav-link.active {
    color: #1a365d;
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: #1a365d;
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
    background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%);
    color: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.btn-secondary {
    background: white;
    color: #1a365d;
    border: 2px solid #e5e7eb;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.btn-secondary:hover {
    border-color: #1a365d;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.btn-text {
    background: transparent;
    color: #6b7280;
    padding: 0.75rem 1rem;
}

.btn-text:hover {
    color: #1a365d;
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
    background: #374151;
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
    color: #1f2937;
    text-decoration: none;
    transition: color 0.3s ease;
}

.mobile-nav-link:hover,
.mobile-nav-link.active {
    color: #1a365d;
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

/* Premium Background with Parallax Effect */
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

/* Premium Hero Section */
.premium-hero {
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    color: white;
    padding-top: 100px;
    padding-bottom: 50px;
}

.premium-hero .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    width: 100%;
}

.hero-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
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
    font-size: 3.5rem;
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
    font-size: 1.25rem;
    opacity: 0.9;
    margin-bottom: 3rem;
    line-height: 1.6;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 3rem;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1rem;
}

.hero-stats {
    display: flex;
    gap: 2rem;
}

.hero-stat {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.8;
}

.hero-image {
    text-align: center;
}

.hero-image img {
    max-width: 100%;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

/* Features Section */
.features {
    padding: 80px 0;
    background: white;
}

.section-title {
    text-align: center;
    margin-bottom: 60px;
}

.section-title h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1a365d;
    margin-bottom: 1rem;
}

.section-title p {
    font-size: 1.125rem;
    color: #6b7280;
    max-width: 600px;
    margin: 0 auto;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.feature-card {
    background: white;
    padding: 40px 30px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%);
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
    color: #1a365d;
    margin-bottom: 1rem;
}

.feature-card p {
    color: #6b7280;
    line-height: 1.6;
}

/* Services Section */
.services {
    padding: 80px 0;
    background: #f8f9fa;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.service-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
}

.service-card:hover {
    border-color: #1a365d;
    transform: translateY(-3px);
}

.service-icon {
    width: 70px;
    height: 70px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.service-icon i {
    font-size: 1.8rem;
    color: #1a365d;
}

.service-card h3 {
    margin-bottom: 10px;
    color: #1a365d;
    font-size: 1.2rem;
}

.service-card p {
    color: #6b7280;
    margin-bottom: 15px;
    line-height: 1.5;
    font-size: 0.9rem;
}

.service-link {
    color: #1a365d;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.service-link:hover {
    text-decoration: underline;
}

/* How It Works Section */
.how-it-works {
    padding: 80px 0;
    background: white;
}

.steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 2rem;
}

.step {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.step-number {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    flex-shrink: 0;
}

.step-content h3 {
    margin-bottom: 10px;
    color: #1a365d;
    font-size: 1.3rem;
}

.step-content p {
    color: #6b7280;
    line-height: 1.6;
}

/* Testimonials Section */
.testimonials {
    padding: 80px 0;
    background: white;
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.testimonial-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
}

.testimonial-content p {
    font-style: italic;
    line-height: 1.6;
    color: #374151;
    margin-bottom: 20px;
    font-size: 1rem;
}

.testimonial-author {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.author-info h4 {
    margin-bottom: 3px;
    color: #1a365d;
    font-size: 1.1rem;
}

.author-info span {
    color: #6b7280;
    font-size: 0.9rem;
}

.testimonial-author .rating .fa-star {
    font-size: 0.9rem;
    color: #f59e0b;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
}

.cta-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 2rem;
}

.cta-content h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.cta-content p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
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

/* Responsive Design */
@media (max-width: 1024px) {
    .hero-content {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 3rem;
    }
    
    .hero-stats {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .nav-menu {
        display: none;
    }
    
    .nav-actions {
        display: none;
    }
    
    .mobile-menu-btn {
        display: flex;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .section-title h2 {
        font-size: 2rem;
    }
    
    .hero-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .steps {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .step {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .features-grid,
    .services-grid,
    .testimonials-grid {
        grid-template-columns: 1fr;
    }
    
    .section {
        padding: 60px 0;
    }
    
    .premium-hero {
        padding-top: 80px;
        min-height: auto;
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
    
    .section-title h2 {
        font-size: 1.8rem;
    }
    
    .cta-content h2 {
        font-size: 2rem;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 1rem;
    }
}

/* Home page local footer */
.home-footer {
    background: #020617;
    color: #e5e7eb;
    padding: 3rem 0 1.5rem;
    margin-top: 3rem;
    border-top: 1px solid rgba(148, 163, 184, 0.3);
}

.home-footer .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.home-footer-main {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 2.5rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.home-footer-col {
    min-width: 220px;
    flex: 1;
}

.home-footer-brand {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.home-footer-logo {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
    font-size: 1.1rem;
}

.home-footer-logo-icon {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: linear-gradient(135deg, #1d4ed8 0%, #22c55e 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.9rem;
}

.home-footer-text {
    font-size: 0.9rem;
    color: #9ca3af;
    max-width: 320px;
}

.home-footer-heading {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9ca3af;
    margin-bottom: 0.75rem;
}

.home-footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    font-size: 0.9rem;
}

.home-footer-links a {
    color: #e5e7eb;
    text-decoration: none;
    transition: color 0.2s ease;
}

.home-footer-links a:hover {
    color: #38bdf8;
}

.home-footer-contact-item {
    font-size: 0.9rem;
    color: #cbd5f5;
}

.home-footer-contact-item span {
    display: block;
    color: #64748b;
    font-size: 0.8rem;
}

.home-footer-social {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.75rem;
}

.home-footer-social a {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #e5e7eb;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all 0.2s ease;
}

.home-footer-social a:hover {
    background: #1d4ed8;
    border-color: #1d4ed8;
    transform: translateY(-1px);
}

.home-footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    border-top: 1px solid rgba(148, 163, 184, 0.3);
    padding-top: 1rem;
    font-size: 0.8rem;
    color: #64748b;
}

.home-footer-bottom-links {
    display: flex;
    gap: 1.5rem;
}

.home-footer-bottom-links a {
    color: #9ca3af;
    text-decoration: none;
}

.home-footer-bottom-links a:hover {
    color: #e5e7eb;
}

@media (max-width: 768px) {
    .home-footer-main {
        flex-direction: column;
    }

    .home-footer-bottom {
        flex-direction: column;
        align-items: flex-start;
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

<!-- Premium Navigation - Same as About Page -->
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
            <a href="index.php" class="nav-link active">Home</a>
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
        <a href="index.php" class="mobile-nav-link active">Home</a>
        <a href="services.php" class="mobile-nav-link">Services</a>
        <a href="about.php" class="mobile-nav-link">About</a>
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

<!-- Premium Hero Section -->
<section class="premium-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="service-badge">
                    <i class="fas fa-star"></i>
                    Your Trusted Home Service Partner
                </div>
                
                <h1 class="hero-title">
                    Your Trusted <span class="title-accent">Home Service</span> Professionals in Addis Ababa
                </h1>
                
                <p class="hero-description">
                    Connect with skilled, verified technicians for all your home maintenance and repair needs. Quality service guaranteed with transparent pricing.
                </p>
                
                <div class="hero-actions">
                    <a href="register.php?role=homeowner" class="btn btn-primary btn-large">
                        <i class="fas fa-tools"></i>
                        Get Service Now
                    </a>
                    <a href="register.php?role=technician" class="btn btn-secondary btn-large">
                        <i class="fas fa-user-plus"></i>
                        Become a Technician
                    </a>
                </div>
                
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Happy Customers</span>
                    </div>
                    <div class="hero-stat">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Verified Technicians</span>
                    </div>
                    <div class="hero-stat">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Service Available</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Home Service Professional">
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

<!-- Features Section -->
<section class="features">
    <div class="container">
        <div class="section-title">
            <h2>Why Choose HomeFix Pro?</h2>
            <p>We make home services simple, reliable, and professional across Addis Ababa</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Verified Professionals</h3>
                <p>All technicians are thoroughly verified, certified, and background-checked for your safety</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Quick Response</h3>
                <p>Get connected with available professionals in your area within hours, not days</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3>Fair Pricing</h3>
                <p>Transparent, upfront pricing with no hidden charges or surprise fees</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3>Quality Guarantee</h3>
                <p>We stand behind the quality of work with our satisfaction guarantee</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>Citywide Coverage</h3>
                <p>Serving all subcities and woredas across Addis Ababa with local experts</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>24/7 Support</h3>
                <p>Round-the-clock customer support to help with any issues or questions</p>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services">
    <div class="container">
        <div class="section-title">
            <h2>Our Popular Services</h2>
            <p>Professional home services for every need</p>
        </div>
        <div class="services-grid">
            <?php foreach($popularServices as $service): ?>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas <?php echo $service['icon']; ?>"></i>
                </div>
                <h3><?php echo $service['name']; ?></h3>
                <p><?php echo $service['description']; ?></p>
                <a href="services.php?category=<?php echo strtolower($service['name']); ?>" class="service-link">
                    Find Technician <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works">
    <div class="container">
        <div class="section-title">
            <h2>How It Works</h2>
            <p>Get your home services in three simple steps</p>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>Register & Post Request</h3>
                    <p>Create your account and describe the service you need with details and location</p>
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>Get Matched</h3>
                    <p>We connect you with verified technicians in your area who can help</p>
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>Get Service & Pay</h3>
                    <p>Receive quality service and pay securely through Tele Birr or bank transfer</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials">
    <div class="container">
        <div class="section-title">
            <h2>What Our Customers Say</h2>
            <p>Real feedback from homeowners across Addis Ababa</p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"HomeFix Pro connected me with an excellent electrician who fixed my wiring issues the same day. Professional and affordable!"</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-info">
                        <h4>Alemu Bekele</h4>
                        <span>Bole, Addis Ababa</span>
                    </div>
                    <div class="rating">
                        <i class="fas fa-star active"></i>
                        <i class="fas fa-star active"></i>
                        <i class="fas fa-star active"></i>
                        <i class="fas fa-star active"></i>
                        <i class="fas fa-star active"></i>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"As a technician, HomeFix Pro has helped me grow my business significantly. The platform is easy to use and payments are reliable."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-info">
                        <h4>Teklu Mengesha</h4>
                        <span>Plumbing Technician</span>
                    </div>
                    <div class="rating">
                        <i class="fas fa-star active"></i>
                        <i class="fas fa-star active"></i>
                        <i class="fas fa-star active"></i>
                        <i class="fas fa-star active"></i>
                        <i class="fas fa-star active"></i>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"The painter I found through HomeFix Pro did an amazing job on my house. The quality exceeded my expectations!"</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-info">
                        <h4>Meron Tesfaye</h4>
                        <span>Yeka, Addis Ababa</span>
                    </div>
                    <div class="rating">
                        <i class="fas fa-star active"></i>
                        <i class="fas fa-star active"></i>
                        <i class="fas fa-star active"></i>
                        <i class="fas fa-star active"></i>
                        <i class="fas fa-star active"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Get Your Home Services?</h2>
            <p>Join thousands of satisfied customers and professional technicians in Addis Ababa</p>
            <div class="cta-buttons">
                <a href="register.php?role=homeowner" class="btn btn-primary btn-large">
                    <i class="fas fa-home"></i> Find a Technician
                </a>
                <a href="register.php?role=technician" class="btn btn-secondary btn-large">
                    <i class="fas fa-user-cog"></i> Start Earning Today
                </a>
            </div>
        </div>
    </div>
</section>

<footer class="home-footer">
    <div class="container">
        <div class="home-footer-main">
            <div class="home-footer-col">
                <div class="home-footer-brand">
                    <div class="home-footer-logo">
                        <div class="home-footer-logo-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <span>HomeFix Pro</span>
                    </div>
                    <p class="home-footer-text">
                        On-demand home services platform in Addis Ababa, connecting homeowners with trusted local professionals.
                    </p>
                    <div class="home-footer-social">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="X"><i class="fab fa-x"></i></a>
                    </div>
                </div>
            </div>
            <div class="home-footer-col">
                <div class="home-footer-heading">Explore</div>
                <ul class="home-footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="home-footer-col">
                <div class="home-footer-heading">Contact</div>
                <div class="home-footer-contact-item">
                    Bole, Addis Ababa
                    <span>Ethiopia</span>
                </div>
                <div class="home-footer-contact-item" style="margin-top:0.5rem;">
                    +251 11 123 4567
                    <span>Mon–Fri 8:00am–8:00pm</span>
                </div>
                <div class="home-footer-contact-item" style="margin-top:0.5rem;">
                    info@homefixpro.et
                    <span>Support 7 days a week</span>
                </div>
            </div>
        </div>
        <div class="home-footer-bottom">
            <div>
                &copy; <?php echo date('Y'); ?> HomeFix Pro. All rights reserved.
            </div>
            <div class="home-footer-bottom-links">
                <a href="privacy.php">Privacy</a>
                <a href="terms.php">Terms</a>
            </div>
        </div>
    </div>
</footer>

<script>
// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
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

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const nav = document.querySelector('.premium-nav');
        if (window.scrollY > 100) {
            nav.style.background = 'rgba(255, 255, 255, 0.98)';
            nav.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)';
        } else {
            nav.style.background = 'rgba(255, 255, 255, 0.95)';
            nav.style.boxShadow = 'none';
        }
    });

    // Parallax effect for background
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallax = document.querySelector('.bg-image-parallax');
        if (parallax) {
            parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });
});
</script>