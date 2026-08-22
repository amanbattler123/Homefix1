<?php
session_start();
require_once 'includes/config.php';
require_once 'models/Database.php';

$pageTitle = "Our Services - HomeFix Pro | Premium Home Services in Addis Ababa";
$pageDescription = "Discover HomeFix Pro's comprehensive range of professional home services in Addis Ababa. From electrical repairs to plumbing, painting, and general maintenance.";
$pageKeywords = "home services, Addis Ababa, electrician, plumber, painter, home repair, maintenance";

$db = new Database();
$conn = $db->getConnection();

// Local image list (10 total - one for each service)
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

// Define all 10 services with proper names and descriptions
$serviceDefinitions = [
    1 => ['name' => 'Plumbing', 'description' => 'Professional plumbing services including pipe repair, fixture installation, and drainage solutions'],
    2 => ['name' => 'Electrical', 'description' => 'Certified electrical work including wiring, panel upgrades, and lighting installation'],
    3 => ['name' => 'HVAC', 'description' => 'Heating, ventilation and air conditioning services for optimal home comfort'],
    4 => ['name' => 'Carpentry', 'description' => 'Expert woodworking and furniture repair from skilled carpenters'],
    5 => ['name' => 'Painting', 'description' => 'Interior and exterior painting services with premium quality materials'],
    6 => ['name' => 'Cleaning', 'description' => 'Thorough cleaning services for homes and offices with eco-friendly products'],
    7 => ['name' => 'Landscaping', 'description' => 'Garden design, lawn care, and outdoor space beautification'],
    8 => ['name' => 'Appliance Repair', 'description' => 'Expert repair services for all major home appliances and electronics'],
    9 => ['name' => 'Roofing', 'description' => 'Professional roofing repair, installation and maintenance services'],
    10 => ['name' => 'Handyman', 'description' => 'General home repairs and maintenance from skilled handymen']
];

// Helper function to get appropriate duration estimates based on service type
function getDurationEstimate($serviceName) {
    $durations = [
        'Plumbing' => '1-2 hours',
        'Electrical' => '2-3 hours', 
        'HVAC' => '3-4 hours',
        'Carpentry' => '2-5 hours',
        'Painting' => '4-8 hours',
        'Cleaning' => '2-4 hours',
        'Landscaping' => '3-6 hours',
        'Appliance Repair' => '1-3 hours',
        'Roofing' => '4-8 hours',
        'Handyman' => '1-4 hours'
    ];
    
    return $durations[$serviceName] ?? '1-3 hours';
}

// Fetch services from database or use predefined ones
try {
    $stmt = $conn->query("
        SELECT s.id, s.name, s.description 
        FROM services s 
        ORDER BY s.id ASC 
        LIMIT 12
    ");
    $dbServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If we have services in database, use them but ensure correct names
    if (!empty($dbServices)) {
        $services = [];
        foreach ($dbServices as $dbService) {
            $serviceId = $dbService['id'];
            // Use predefined service info if available, otherwise use database values
            if (isset($serviceDefinitions[$serviceId])) {
                $services[] = [
                    'id' => $serviceId,
                    'name' => $serviceDefinitions[$serviceId]['name'],
                    'description' => $serviceDefinitions[$serviceId]['description']
                ];
            } else {
                $services[] = $dbService;
            }
        }
    } else {
        // If no services in database, use all predefined ones
        $services = [];
        foreach ($serviceDefinitions as $id => $serviceDef) {
            $services[] = [
                'id' => $id,
                'name' => $serviceDef['name'],
                'description' => $serviceDef['description']
            ];
        }
    }
    
} catch (PDOException $e) {
    // If there's an error, use all predefined services
    $services = [];
    foreach ($serviceDefinitions as $id => $serviceDef) {
        $services[] = [
            'id' => $id,
            'name' => $serviceDef['name'],
            'description' => $serviceDef['description']
        ];
    }
}

// Process services data - ensure each service gets unique image and proper data
$processedServices = [];
$serviceCount = 0;

foreach ($services as $service) {
    // Ensure we have valid service data
    if (empty($service['name']) && isset($serviceDefinitions[$service['id']])) {
        $service['name'] = $serviceDefinitions[$service['id']]['name'];
        $service['description'] = $serviceDefinitions[$service['id']]['description'];
    }
    
    // Use corresponding photo or cycle through available photos
    $photoIndex = ($service['id'] - 1) % count($photos);
    
    $processedService = [
        'id' => $service['id'],
        'name' => $service['name'],
        'description' => $service['description'] ?? 'Professional home service with guaranteed quality',
        'image' => $photos[$photoIndex],
        'rating' => 4.5 + (rand(0, 10) / 10), // Random rating between 4.5-5.5
        'review_count' => rand(15, 150),
        'duration_estimate' => getDurationEstimate($service['name']),
        'featured' => $serviceCount < 3 // First 3 services are featured
    ];
    
    $processedServices[] = $processedService;
    $serviceCount++;
    
    // Stop if we've processed all 10 services
    if ($serviceCount >= 10) {
        break;
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
html { visibility: hidden; }

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

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
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

.btn-book {
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
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
    background-image: url('https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=2070&q=80');
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
    background: linear-gradient(135deg, rgba(26, 54, 93, 0.95) 0%, rgba(43, 108, 176, 0.85) 50%, rgba(49, 130, 206, 0.8) 100%);
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

.floating-element.el-1 { top: 20%; left: 10%; animation-delay: 0s; }
.floating-element.el-2 { top: 60%; left: 85%; animation-delay: 1s; }
.floating-element.el-3 { top: 80%; left: 15%; animation-delay: 2s; }
.floating-element.el-4 { top: 40%; left: 80%; animation-delay: 3s; }

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

.services-hero {
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    color: white;
    padding-top: 110px;
    padding-bottom: 50px;
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

.hero-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 2.5rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 14px;
    padding: 1rem;
    backdrop-filter: blur(10px);
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 800;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    justify-content: center;
}

.scroll-indicator {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
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

.section {
    padding: 6rem 0;
}

.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem 0.9rem;
    border-radius: 999px;
    background: rgba(49, 130, 206, 0.1);
    color: var(--primary);
    font-weight: 700;
    font-size: 0.8rem;
    margin-bottom: 0.75rem;
}

.section-title {
    font-size: 2.75rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 1rem;
}

.section-description {
    font-size: 1.125rem;
    color: var(--gray-600);
    max-width: 720px;
    margin: 0 auto;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
}

.service-card {
    background: white;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--gray-200);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.service-image {
    position: relative;
    height: 220px;
    overflow: hidden;
}

.service-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scale(1.02);
}

.service-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.0) 0%, rgba(0,0,0,0.55) 100%);
    opacity: 0;
    transition: opacity 0.25s ease;
}

.service-card:hover .service-overlay {
    opacity: 1;
}

.service-actions {
    position: absolute;
    top: 12px;
    right: 12px;
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    width: 40px;
    height: 40px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.3);
    background: rgba(255,255,255,0.15);
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.2s ease;
}

.action-btn:hover {
    transform: translateY(-2px);
    background: rgba(255,255,255,0.25);
}

.service-badge-tag {
    position: absolute;
    bottom: 12px;
    left: 12px;
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid rgba(16, 185, 129, 0.35);
    color: white;
    padding: 0.4rem 0.75rem;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    backdrop-filter: blur(10px);
}

.service-content {
    padding: 1.75rem;
}

.service-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 0.6rem;
}

.service-description {
    color: var(--gray-600);
    margin-bottom: 1.25rem;
}

.service-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-600);
    background: var(--gray-50);
    padding: 0.5rem 1rem;
    border-radius: 999px;
}

.service-features {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.feature {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-600);
    background: var(--gray-50);
    padding: 0.5rem 1rem;
    border-radius: 999px;
}

.feature i {
    color: var(--success);
}

.service-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
}

.stars {
    display: flex;
    gap: 0.25rem;
}

.stars i {
    color: #fbbf24;
    font-size: 0.875rem;
}

.rating-value {
    font-size: 0.875rem;
    color: var(--gray-700);
    font-weight: 700;
}

.review-count {
    font-size: 0.875rem;
    color: var(--gray-500);
}

.service-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.service-benefits {
    padding: 6rem 0;
    background: var(--gray-50);
}

.benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.benefit-card {
    text-align: center;
    padding: 2rem;
}

.benefit-icon {
    width: 80px;
    height: 80px;
    background: var(--gradient-primary);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: white;
    font-size: 2rem;
}

.benefit-card h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 1rem;
}

.benefit-card p {
    color: var(--gray-600);
    line-height: 1.6;
}

.service-coverage {
    padding: 6rem 0;
    background: white;
}

.coverage-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.coverage-text h2 {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 1.5rem;
}

.coverage-text p {
    font-size: 1.125rem;
    color: var(--gray-600);
    margin-bottom: 2rem;
    line-height: 1.6;
}

.coverage-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}

.coverage-column {
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: 14px;
    padding: 1.25rem 1.25rem 0.75rem;
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.coverage-column:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: rgba(49, 130, 206, 0.35);
}

.coverage-column h4 {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 1rem;
}

.coverage-column ul {
    list-style: none;
}

.coverage-column li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    color: var(--gray-600);
}

.coverage-column li i {
    width: 18px;
    display: inline-flex;
    justify-content: center;
}

.coverage-column i {
    color: var(--success);
}

.coverage-map {
    display: flex;
    justify-content: center;
}

.map-card {
    width: 100%;
    background: white;
    border-radius: 15px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--gray-200);
    overflow: hidden;
}

.map-card-header {
    padding: 1.25rem 1.25rem 0.75rem;
    background: var(--gradient-primary);
    color: white;
    text-align: center;
}

.map-card-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    font-size: 1.15rem;
    font-weight: 800;
    line-height: 1.2;
}

.map-card-title i {
    font-size: 1.1rem;
}

.map-card-subtitle {
    margin-top: 0.35rem;
    opacity: 0.9;
}

.map-embed {
    position: relative;
    width: 100%;
    height: 360px;
}

.map-embed iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

.map-card-actions {
    padding: 1rem 1.25rem 1.25rem;
    display: flex;
    justify-content: center;
}

.map-card-actions .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
}

.services-cta {
    padding: 6rem 0;
    background: var(--gradient-primary);
    color: white;
    text-align: center;
}

.cta-content h2 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
}

.cta-content p {
    font-size: 1.25rem;
    opacity: 0.9;
    margin-bottom: 3rem;
    line-height: 1.6;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.cta-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 3rem;
}

.cta-features {
    display: flex;
    gap: 2rem;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-features .feature {
    background: rgba(255, 255, 255, 0.08);
    color: white;
}

.cta-features .feature i {
    color: #fbbf24;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1100;
    padding: 2rem;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 20px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--shadow-2xl);
}

.modal-header {
    padding: 2rem 2rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--gray-200);
}

.modal-header h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--gray-500);
    cursor: pointer;
    transition: color 0.3s ease;
}

.modal-close:hover {
    color: var(--danger);
}

.modal-body {
    padding: 1rem 2rem 2rem;
}

.loading-spinner {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(5px);
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1200;
}

.loading-spinner.active {
    display: flex;
}

.spinner {
    font-size: 3rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.loading-spinner p {
    font-size: 1.125rem;
    color: var(--gray-600);
}

@media (max-width: 1024px) {
    .hero-stats { grid-template-columns: repeat(2, 1fr); }
    .coverage-content { grid-template-columns: 1fr; gap: 3rem; }
    .coverage-list { grid-template-columns: repeat(2, 1fr); }
    .cta-actions { flex-direction: column; align-items: center; }
}

@media (max-width: 768px) {
    .nav-menu { display: none; }
    .mobile-menu-btn { display: flex; }
    .hero-title { font-size: 2.5rem; }
    .section-title { font-size: 2.5rem; }
    .hero-actions { flex-direction: column; }
    .services-grid { grid-template-columns: 1fr; }
    .coverage-list { grid-template-columns: 1fr; }
    .cta-features { flex-direction: column; gap: 1rem; }
}

@media (max-width: 480px) {
    .nav-container { padding: 0 1rem; }
    .container { padding: 0 1rem; }
    .hero-title { font-size: 2rem; }
    .section-title { font-size: 2rem; }
    .service-content { padding: 1.5rem; }
    .service-footer { flex-direction: column; gap: 1rem; align-items: stretch; }
    .btn-book { width: 100%; justify-content: center; }
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    justify-content: center;
}

.service-highlight {
    width: 100%;
    max-width: 520px;
    margin: 0 auto;
}

.highlight-card {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 18px;
    padding: 1.5rem;
    backdrop-filter: blur(14px);
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.25);
}

.highlight-card .card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.highlight-card .card-header i {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.18);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fbbf24;
}

.highlight-card h4 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 800;
    color: white;
}

.highlight-card > p {
    margin: 0 0 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.6;
}

.booking-steps {
    display: grid;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.booking-steps .step {
    display: grid;
    grid-template-columns: 42px 1fr;
    gap: 0.85rem;
    align-items: start;
    padding: 0.85rem;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.16);
}

.step-number {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    background: rgba(251, 191, 36, 0.18);
    border: 1px solid rgba(251, 191, 36, 0.35);
    color: #fbbf24;
    font-weight: 900;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.step-content h5 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 800;
    color: white;
}

.step-content p {
    margin: 0.15rem 0 0;
    color: rgba(255, 255, 255, 0.86);
    font-size: 0.9rem;
}

.booking-time {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.7rem 0.9rem;
    border-radius: 14px;
    background: rgba(16, 185, 129, 0.14);
    border: 1px solid rgba(16, 185, 129, 0.24);
    color: rgba(255, 255, 255, 0.95);
}

.booking-time i {
    color: #34d399;
}

.scroll-indicator {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
}

</style>

<script>
window.addEventListener('load', function() {
    document.documentElement.style.visibility = 'visible';
});
</script>

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
            <a href="services.php" class="nav-link active">Services</a>
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
        <a href="index.php" class="mobile-nav-link">Home</a>
        <a href="services.php" class="mobile-nav-link active">Services</a>
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

<!-- Services Hero Section -->
<section class="services-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="service-badge">
                    <i class="fas fa-star"></i>
                    Premium Services Available 24/7
                </div>
                
                <h1 class="hero-title">
                    Professional <span class="title-accent">Home Services</span>
                    <span class="title-sub">in Addis Ababa</span>
                </h1>
                
                <p class="hero-description">
                    Discover our comprehensive range of professional home services trusted by thousands of homeowners. 
                    From emergency repairs to scheduled maintenance, we deliver exceptional quality with verified, 
                    background-checked technicians across all subcities of Addis Ababa.
                </p>
                
                <div class="hero-stats">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo count($processedServices); ?></div>
                        <div class="stat-label">Services Offered</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">24/7</div>
                        <div class="stat-label">Emergency Support</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">All Areas</div>
                        <div class="stat-label">Addis Ababa Coverage</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">98%</div>
                        <div class="stat-label">Satisfaction Rate</div>
                    </div>
                </div>
                
                <div class="hero-actions">
                    <a href="#services-grid" class="btn btn-primary btn-large scroll-to">
                        <i class="fas fa-search"></i>
                        Explore Services
                    </a>
                    <a href="tel:+251-911-234567" class="btn btn-secondary btn-large">
                        <i class="fas fa-phone"></i>
                        Call Now: +251 911 234567
                    </a>
                </div>
            </div>
            
            <div class="hero-visual">
                <div class="service-highlight">
                    <div class="highlight-card">
                        <div class="card-header">
                            <i class="fas fa-bolt"></i>
                            <h4>Quick & Easy Booking</h4>
                        </div>
                        <p>Book any service in under 2 minutes with our streamlined process</p>
                        <div class="booking-steps">
                            <div class="step">
                                <span class="step-number">1</span>
                                <div class="step-content">
                                    <h5>Choose Service</h5>
                                    <p>Select from <?php echo count($processedServices); ?> professional services</p>
                                </div>
                            </div>
                            <div class="step">
                                <span class="step-number">2</span>
                                <div class="step-content">
                                    <h5>Select Time & Date</h5>
                                    <p>Pick a convenient time slot</p>
                                </div>
                            </div>
                            <div class="step">
                                <span class="step-number">3</span>
                                <div class="step-content">
                                    <h5>Get Confirmed</h5>
                                    <p>Receive instant confirmation</p>
                                </div>
                            </div>
                        </div>
                        <div class="booking-time">
                            <i class="fas fa-clock"></i>
                            Average response time: <strong>15 minutes</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
        <div class="scroll-text">Scroll to Explore</div>
        <div class="scroll-arrow">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</section>

<!-- All Services Section -->
<section class="services-page section" id="services-grid">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Our Services</div>
            <h2 class="section-title">Complete Home Service Solutions</h2>
            <p class="section-description">
                Comprehensive home services delivered by verified professionals across all subcities of Addis Ababa
            </p>
        </div>
        
        <div class="services-grid" id="services-container">
            <?php 
            $displayCount = 0;
            if (!empty($processedServices)): 
                foreach($processedServices as $service): 
                    $displayCount++;
            ?>
            <div class="service-card" data-aos="fade-up" data-aos-delay="<?php echo ($displayCount % 6) * 100; ?>">
                <div class="service-image">
                    <img src="<?php echo $service['image']; ?>" alt="<?php echo htmlspecialchars($service['name']); ?>" class="service-img">
                    <div class="service-overlay">
                        <div class="service-actions">
                            <button class="action-btn quick-view" data-service="<?php echo $service['id']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn favorite" data-service="<?php echo $service['id']; ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="service-badge-tag">
                        <i class="fas fa-check-circle"></i>
                        Verified Professional
                    </div>
                </div>
                <div class="service-content">
                    <h3 class="service-title"><?php echo htmlspecialchars($service['name']); ?></h3>
                    <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                    
                    <div class="service-meta">
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo $service['duration_estimate']; ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>Same Day Service</span>
                        </div>
                    </div>
                    
                    <div class="service-features">
                        <span class="feature">
                            <i class="fas fa-bolt"></i>
                            Quick Response
                        </span>
                        <span class="feature">
                            <i class="fas fa-shield-alt"></i>
                            Quality Guarantee
                        </span>
                    </div>
                    
                    <div class="service-rating">
                        <div class="stars">
                            <?php
                            $fullStars = floor($service['rating']);
                            $hasHalfStar = ($service['rating'] - $fullStars) >= 0.5;
                            
                            for($i = 1; $i <= 5; $i++):
                                if($i <= $fullStars):
                                    echo '<i class="fas fa-star"></i>';
                                elseif($i == $fullStars + 1 && $hasHalfStar):
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                else:
                                    echo '<i class="far fa-star"></i>';
                                endif;
                            endfor;
                            ?>
                        </div>
                        <span class="rating-value"><?php echo number_format($service['rating'], 1); ?></span>
                        <span class="review-count">(<?php echo $service['review_count']; ?> reviews)</span>
                    </div>
                    
                    <div class="service-footer">
                        <a href="login.php" class="btn btn-primary btn-book">
                            <i class="fas fa-calendar-check"></i>
                            Book Service
                        </a>
                        <button class="btn btn-text btn-details" data-service="<?php echo $service['id']; ?>">
                            Details
                        </button>
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
            else: 
            ?>
            <div class="no-services">
                <i class="fas fa-tools"></i>
                <h3>No Services Available</h3>
                <p>Please check back later or contact us for more information.</p>
            </div>
            <?php endif; ?>
        </div>
        
    </div>
</section>

<!-- Service Benefits Section -->
<section class="service-benefits">
    <div class="container">
        <div class="benefits-grid">
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3>Verified Professionals</h3>
                <p>All our technicians are thoroughly vetted in Ethiopia, background-checked, and certified in their respective fields.</p>
            </div>
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-medal"></i>
                </div>
                <h3>Quality Guarantee</h3>
                <p>We stand behind our work with a strong satisfaction guarantee on every service we provide.</p>
            </div>
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3>Rapid Response</h3>
                <p>Emergency visits available 24/7 with average response times under 2 hours in most parts of Addis Ababa.</p>
            </div>
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>Citywide Coverage</h3>
                <p>We serve all subcities of Addis Ababa through our extensive network of local professionals.</p>
            </div>
        </div>
    </div>
</section>
<section class="service-coverage">
    <div class="container">
        <div class="coverage-content">
            <div class="coverage-text">
                <h2>Service Coverage Across Addis Ababa</h2>
                <p>We provide comprehensive home services across all subcities of Addis Ababa. Our network of verified professionals ensures timely service delivery no matter where you're located.</p>
                
                <div class="coverage-list">
                    <div class="coverage-column">
                        <h4>Central Areas</h4>
                        <ul>
                            <li><i class="fas fa-check"></i> Bole</li>
                            <li><i class="fas fa-check"></i> Kazanchis</li>
                            <li><i class="fas fa-check"></i> Piazza</li>
                            <li><i class="fas fa-check"></i> Mercato</li>
                        </ul>
                    </div>
                    <div class="coverage-column">
                        <h4>Eastern Areas</h4>
                        <ul>
                            <li><i class="fas fa-check"></i> CMC</li>
                            <li><i class="fas fa-check"></i> Megenagna</li>
                            <li><i class="fas fa-check"></i> Gotera</li>
                            <li><i class="fas fa-check"></i> Nifas Silk</li>
                        </ul>
                    </div>
                    <div class="coverage-column">
                        <h4>Western Areas</h4>
                        <ul>
                            <li><i class="fas fa-check"></i> Kolfe</li>
                            <li><i class="fas fa-check"></i> Keranyo</li>
                            <li><i class="fas fa-check"></i> Gulele</li>
                            <li><i class="fas fa-check"></i> Addis Ketema</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="coverage-map">
                <div class="map-card">
                    <div class="map-card-header">
                        <div class="map-card-title">
                            <i class="fas fa-map-marked-alt"></i>
                            Addis Ababa Coverage
                        </div>
                        <div class="map-card-subtitle">We serve all areas across the city</div>
                    </div>
                    <div class="map-embed">
                        <iframe
                            title="Addis Ababa Map"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://www.google.com/maps?q=Addis%20Ababa%2C%20Ethiopia&z=12&output=embed"></iframe>
                    </div>
                    <div class="map-card-actions">
                        <a href="contact.php" class="btn btn-outline">
                            <i class="fas fa-headset"></i>
                            Contact for Availability
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="services-cta">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Book a Service?</h2>
            <p>Get professional home services with guaranteed quality and reliable professionals across Addis Ababa.</p>
            <div class="cta-actions">
                <a href="login.php" class="btn btn-primary btn-large">
                    <i class="fas fa-calendar-alt"></i>
                    Book Service Now
                </a>
                <a href="tel:+251-911-234567" class="btn btn-secondary btn-large">
                    <i class="fas fa-phone"></i>
                    Call: +251 911 234567
                </a>
            </div>
            <div class="cta-features">
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <span>100% Satisfaction Guarantee</span>
                </div>
                <div class="feature">
                    <i class="fas fa-clock"></i>
                    <span>24/7 Emergency Service</span>
                </div>
                <div class="feature">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>All Addis Ababa Areas</span>
                </div>
            </div>
</section>

<!-- Service Quick View Modal -->
<div class="modal" id="serviceModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalServiceTitle">Service Details</h3>
            <button class="modal-close" id="modalClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-service-content">
                <div class="service-details">
                    <div class="detail-image">
                        <img id="modalServiceImage" src="assets/photos/photo1.jpg" alt="Service Detail" style="width: 100%; border-radius: 10px;">
                    </div>
                    <div class="detail-content">
                        <h4>Service Description</h4>
                        <p id="modalServiceDescription">Select a service to see details.</p>
                        <h4>What's Included</h4>
                        <ul id="modalServiceIncludes">
                            <li>Professional assessment</li>
                            <li>Quality workmanship</li>
                            <li>Cleanup after completion</li>
                        </ul>

                        <h4>Typical Duration</h4>
                        <p><strong id="modalServiceDuration">1-3 hours</strong></p>
                        <div class="detail-actions">
                            <a href="login.php" class="btn btn-primary" id="modalBookLink">Book This Service</a>
                            <a href="tel:+251-911-234567" class="btn btn-secondary">Call for Information</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hide global footer on Services page only -->
<style>
    .footer-modern {
        display: none !important;
    }
</style>

<script>
// Enhanced JavaScript with more functionality
document.addEventListener('DOMContentLoaded', function() {
    const servicesData = <?php echo json_encode($processedServices, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    const serviceDetails = {
        Plumbing: {
            includes: ['Leak diagnosis & repair', 'Pipe/fixture installation', 'Drain cleaning & blockage removal', 'Water pressure checks', 'Final testing & cleanup']
        },
        Electrical: {
            includes: ['Safety inspection', 'Switch/socket replacement', 'Wiring repairs', 'Lighting installation', 'Load testing & cleanup']
        },
        HVAC: {
            includes: ['System inspection', 'Filter replacement (if available)', 'Cooling/heating diagnostics', 'Minor repairs & calibration', 'Performance testing']
        },
        Carpentry: {
            includes: ['Measurement & assessment', 'Door/cabinet adjustments', 'Furniture repair', 'Hardware installation', 'Cleanup']
        },
        Painting: {
            includes: ['Surface prep', 'Crack/patch work (basic)', 'Priming (if needed)', 'Two-coat painting (as required)', 'Cleanup']
        },
        Cleaning: {
            includes: ['Deep cleaning of key areas', 'Dusting & sanitizing', 'Floor cleaning', 'Bathroom/kitchen cleaning', 'Waste removal']
        },
        Landscaping: {
            includes: ['Garden assessment', 'Trimming & weeding', 'Lawn care', 'Basic planting support', 'Cleanup']
        },
        'Appliance Repair': {
            includes: ['Diagnosis', 'Minor repairs', 'Parts recommendation', 'Function testing', 'Safety checks']
        },
        Roofing: {
            includes: ['Roof inspection', 'Leak detection', 'Minor repairs', 'Sealant application', 'Final inspection']
        },
        Handyman: {
            includes: ['General inspection', 'Small repairs', 'Mounting & assembly', 'Basic maintenance', 'Cleanup']
        }
    };

    // Initialize AOS
    if (window.AOS && typeof window.AOS.init === 'function') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    }

    // Initialize GSAP and ScrollTrigger
    if (window.gsap && window.ScrollTrigger && window.ScrollToPlugin) {
        gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);
    }

    // Hero section animations
    if (window.gsap) {
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
        .from('.hero-stats', {
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
        }, "-=0.3")
        .from('.highlight-card', {
            duration: 1,
            scale: 0.8,
            opacity: 0,
            ease: "back.out(1.7)"
        }, "-=0.5");
    }

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

    // Service card interactions
    const serviceCards = document.querySelectorAll('.service-card');
    serviceCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Favorite button functionality
    const favoriteButtons = document.querySelectorAll('.favorite');
    favoriteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const serviceId = this.getAttribute('data-service');
            this.classList.toggle('active');
            
            if (this.classList.contains('active')) {
                this.innerHTML = '<i class="fas fa-heart"></i>';
                this.style.background = 'var(--danger)';
                this.style.color = 'white';
                showNotification('Service added to favorites!', 'success');
            } else {
                this.innerHTML = '<i class="fas fa-heart"></i>';
                this.style.background = '';
                this.style.color = '';
                showNotification('Service removed from favorites!', 'info');
            }
        });
    });

    // Quick view functionality
    const quickViewButtons = document.querySelectorAll('.quick-view, .btn-details');
    const serviceModal = document.getElementById('serviceModal');
    const modalClose = document.getElementById('modalClose');
    const modalServiceTitle = document.getElementById('modalServiceTitle');
    const modalServiceDescription = document.getElementById('modalServiceDescription');
    const modalServiceImage = document.getElementById('modalServiceImage');
    const modalServiceIncludes = document.getElementById('modalServiceIncludes');
    const modalServiceDuration = document.getElementById('modalServiceDuration');
    const modalBookLink = document.getElementById('modalBookLink');

    function findServiceById(id) {
        const numericId = Number(id);
        return (servicesData || []).find(s => Number(s.id) === numericId) || null;
    }

    function openServiceModal(serviceId) {
        if (!serviceModal) return;

        const service = findServiceById(serviceId);
        if (service) {
            if (modalServiceTitle) modalServiceTitle.textContent = service.name || 'Service Details';
            if (modalServiceDescription) modalServiceDescription.textContent = service.description || 'Professional home service with guaranteed quality.';
            if (modalServiceImage && service.image) modalServiceImage.src = service.image;

            if (modalServiceDuration) {
                modalServiceDuration.textContent = service.duration_estimate || '1-3 hours';
            }

            if (modalServiceIncludes) {
                const details = serviceDetails[service.name] || null;
                const includes = details?.includes || ['Professional assessment', 'Quality workmanship', 'Cleanup after completion'];
                modalServiceIncludes.innerHTML = includes.map(item => `<li>${item}</li>`).join('');
            }

            if (modalBookLink) {
                modalBookLink.href = `login.php?service=${encodeURIComponent(service.id)}`;
            }
        } else {
            if (modalServiceTitle) modalServiceTitle.textContent = 'Service Details';
            if (modalServiceDescription) modalServiceDescription.textContent = 'Service details are not available right now.';

            if (modalServiceDuration) {
                modalServiceDuration.textContent = '1-3 hours';
            }

            if (modalServiceIncludes) {
                modalServiceIncludes.innerHTML = '<li>Professional assessment</li><li>Quality workmanship</li><li>Cleanup after completion</li>';
            }
        }

        serviceModal.classList.add('active');
    }
    
    quickViewButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const serviceId = this.getAttribute('data-service');
            openServiceModal(serviceId);
        });
    });
    
    if (modalClose) {
        modalClose.addEventListener('click', function() {
            serviceModal.classList.remove('active');
        });
    }
    
    // Close modal when clicking outside
    if (serviceModal) {
        serviceModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    }

    // Notification function
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Add styles for notification
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow-xl);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            max-width: 400px;
            z-index: 1300;
            border-left: 4px solid ${type === 'success' ? 'var(--success)' : 'var(--accent)'};
            transform: translateX(150%);
            transition: transform 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Close button functionality
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', function() {
            notification.style.transform = 'translateX(150%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        });
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.transform = 'translateX(150%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }
        }, 5000);
    }

    // Parallax effect for background
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallax = document.querySelector('.bg-image-parallax');
        if (parallax) {
            parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });

    // Add animation to service cards on scroll
    gsap.utils.toArray('.service-card, .benefit-card').forEach(card => {
        gsap.from(card, {
            scrollTrigger: {
                trigger: card,
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
});
</script>

<?php include 'includes/footer.php'; ?>