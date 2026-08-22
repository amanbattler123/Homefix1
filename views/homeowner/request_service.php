<?php
session_start();
require_once '../../includes/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'homeowner') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../controllers/HomeownerController.php';

$conn = getDBConnection();
$homeownerController = new HomeownerController($conn);

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

if($isAjax) {
    header('Content-Type: application/json');
    $serviceTypeQuery = $_GET['service_type'] ?? null;
    if(!$serviceTypeQuery) {
        echo json_encode(['technicians' => []]);
        exit();
    }

    $technicians = array_map(function($tech) use ($homeownerController) {
        $reviews = $homeownerController->getTechnicianReviews($tech['id'], 3);
        return [
            'id' => (int)$tech['id'],
            'first_name' => $tech['first_name'],
            'last_name' => $tech['last_name'],
            'profession' => $tech['profession'],
            'phone' => $tech['phone'],
            'email' => $tech['email'],
            'average_rating' => round((float)$tech['average_rating'], 1),
            'rating_count' => (int)$tech['rating_count'],
            'reviews' => array_map(function($review) {
                return [
                    'homeowner_name' => trim(($review['homeowner_first_name'] ?? '') . ' ' . ($review['homeowner_last_name'] ?? '')),
                    'rating' => (int)$review['rating'],
                    'comment' => $review['comment'],
                    'created_at' => $review['created_at']
                ];
            }, $reviews)
        ];
    }, $homeownerController->getTechniciansByService($serviceTypeQuery));

    echo json_encode(['technicians' => $technicians]);
    exit();
}

$message = '';
$userProfile = $homeownerController->getUserProfile();
$services = ['Plumbing', 'Electrical', 'HVAC', 'Painting', 'Cleaning', 'Landscaping', 'Appliance Repair', 'Roofing', 'Handyman'];

$presetService = $_GET['service_type'] ?? '';
$presetTechnician = isset($_GET['technician_id']) ? (int)$_GET['technician_id'] : null;
$selectedService = $_POST['service_type'] ?? $presetService;
$selectedTechnician = $_POST['technician_id'] ?? ($presetTechnician ?: '');

if(isset($_POST['create_request'])) {
    $data = [
        'service_type' => $_POST['service_type'],
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'address' => $_POST['address'],
        'subcity' => $_POST['subcity'],
        'woreda' => $_POST['woreda'],
        'preferred_date' => $_POST['preferred_date'],
        'preferred_time' => $_POST['preferred_time'],
        'budget' => null,
        'technician_id' => !empty($_POST['technician_id']) ? (int)$_POST['technician_id'] : null
    ];
    
    if($homeownerController->createServiceRequest($data)) {
        $message = '<div class="alert alert-success" data-aos="fade-down">Service request created successfully!</div>';
        $_POST = array();
    } else {
        $message = '<div class="alert alert-danger" data-aos="fade-down">Error creating service request.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Service - Homefix Pro</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --dark: #1e1e2c;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            --gradient-secondary: linear-gradient(135deg, #7209b7 0%, #3a0ca3 100%);
            --gradient-success: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);
            --gradient-warning: linear-gradient(135deg, #f8961e 0%, #f3722c 100%);
            --gradient-dark: linear-gradient(135deg, #1e1e2c 0%, #2d2d44 100%);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 15px 50px rgba(0, 0, 0, 0.12);
            --radius: 16px;
            --radius-lg: 24px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: var(--dark);
            min-height: 100vh;
            line-height: 1.6;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            overflow-y: auto;
        }

        /* Header Styles */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 25px 30px;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
        }

        .welcome-section h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-section p {
            color: var(--gray);
            font-size: 16px;
            max-width: 500px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 14px 28px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            box-shadow: var(--shadow);
            text-decoration: none;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .notification-bell {
            position: relative;
            background: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            cursor: pointer;
            transition: var(--transition);
        }

        .notification-bell:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .notification-bell i {
            font-size: 20px;
            color: var(--gray);
        }

        .notification-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: var(--danger);
            width: 10px;
            height: 10px;
            border-radius: 50%;
            font-size: 0;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 35px;
            transition: var(--transition);
            margin-bottom: 30px;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .card-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-title i {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-subtitle {
            color: var(--gray);
            font-size: 16px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray-light);
            border-radius: var(--radius);
            font-size: 15px;
            transition: var(--transition);
            background-color: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray-light);
            border-radius: var(--radius);
            font-size: 15px;
            transition: var(--transition);
            background-color: white;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 28px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        .btn-sm {
            padding: 10px 20px;
            font-size: 14px;
        }

        /* Alert Styles */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(76, 201, 240, 0.15);
            color: var(--dark);
            border-left: 4px solid var(--success);
        }

        .alert-danger {
            background: rgba(247, 37, 133, 0.15);
            color: var(--dark);
            border-left: 4px solid var(--danger);
        }

        .alert-warning {
            background: rgba(248, 150, 30, 0.15);
            color: var(--dark);
            border-left: 4px solid var(--warning);
        }

        .alert i {
            font-size: 18px;
        }

        /* Technician Cards */
        .technician-list {
            background: var(--light);
            border-radius: var(--radius);
            padding: 20px;
            border: 1px solid var(--gray-light);
            max-height: 500px;
            overflow-y: auto;
        }

        .technician-card {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid var(--gray-light);
            transition: var(--transition);
        }

        .technician-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            border-color: var(--primary-light);
        }

        .technician-card:last-child {
            margin-bottom: 0;
        }

        .technician-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .technician-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .technician-profession {
            color: var(--primary);
            font-weight: 500;
            font-size: 14px;
        }

        .technician-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(248, 150, 30, 0.1);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .rating-stars {
            color: #f39c12;
        }

        .rating-count {
            color: var(--gray);
            font-weight: 500;
        }

        .technician-contact {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 14px;
            color: var(--gray);
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .technician-reviews {
            background: var(--light);
            border-radius: var(--radius);
            padding: 15px;
            margin-top: 15px;
        }

        .reviews-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark);
            font-size: 14px;
        }

        .review-item {
            padding: 10px;
            background: white;
            border-radius: var(--radius);
            margin-bottom: 10px;
            border-left: 3px solid var(--primary);
        }

        .review-item:last-child {
            margin-bottom: 0;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .reviewer-name {
            font-weight: 600;
            font-size: 13px;
        }

        .review-rating {
            color: #f39c12;
            font-size: 13px;
        }

        .review-comment {
            color: var(--gray);
            font-size: 13px;
            line-height: 1.4;
        }

        .review-date {
            font-size: 12px;
            color: var(--gray);
            margin-top: 5px;
        }

        .no-reviews {
            text-align: center;
            color: var(--gray);
            font-style: italic;
            padding: 20px;
        }

        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: var(--primary);
        }

        .spinner {
            border: 2px solid rgba(67, 97, 238, 0.3);
            border-radius: 50%;
            border-top: 2px solid var(--primary);
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Form Layout */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 280px;
                padding: 25px;
            }
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .btn-primary, .btn-secondary {
                flex: 1;
                justify-content: center;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .technician-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .technician-contact {
                flex-direction: column;
                gap: 8px;
            }
        }

        /* Animation for elements */
        [data-aos] {
            opacity: 0;
            transition-property: opacity, transform;
        }

        [data-aos].aos-animate {
            opacity: 1;
        }

        /* Menu Toggle for Mobile */
        .menu-toggle {
            display: none;
            background: var(--gradient-primary);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            box-shadow: var(--shadow);
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1001;
        }

        @media (max-width: 992px) {
            .menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        /* Custom Scrollbar */
        .technician-list::-webkit-scrollbar {
            width: 6px;
        }

        .technician-list::-webkit-scrollbar-track {
            background: var(--gray-light);
            border-radius: 3px;
        }

        .technician-list::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 3px;
        }

        .technician-list::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }
    </style>
</head>
<body class="homeowner-body">
    <div class="dashboard">
        <!-- Include the sidebar component -->
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <!-- Header -->
            <div class="dashboard-header">
                <div class="welcome-section">
                    <h1>Request New Service</h1>
                    <p>Fill out the form below to request a home service from our professional technicians</p>
                </div>
                <div class="header-actions">
                    <div class="notification-bell">
                        <i class="fa-solid fa-bell"></i>
                        <?php $unreadNotifications = $homeownerController->getUnreadNotificationCount(); ?>
                        <?php if(!empty($unreadNotifications)): ?>
                            <span class="notification-badge"></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Display Messages -->
            <?php if(!empty($message)): ?>
                <?php echo $message; ?>
            <?php endif; ?>

            <!-- Service Request Form Card -->
            <div class="card" data-aos="fade-up">
                <div class="card-header">
                    <div>
                        <h2 class="card-title"><i class="fas fa-tools"></i> Service Request Form</h2>
                        <p class="card-subtitle">Provide details about the service you need and choose your preferred technician</p>
                    </div>
                </div>
                
                <form method="POST" id="service-request-form">
                    <input type="hidden" name="create_request" value="1">
                    
                    <div class="form-group" data-aos="fade-up" data-aos-delay="100">
                        <label class="form-label"><i class="fas fa-concierge-bell me-2"></i>Service Type *</label>
                        <select name="service_type" id="service-type" class="form-select" required>
                            <option value="">Select Service Type</option>
                            <?php foreach($services as $service): ?>
                                <option value="<?php echo $service; ?>" <?php echo ($selectedService === $service) ? 'selected' : ''; ?>>
                                    <?php echo $service; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" data-aos="fade-up" data-aos-delay="150">
                        <label class="form-label"><i class="fas fa-user-hard-hat me-2"></i>Choose Technician</label>
                        <select name="technician_id" id="technician-select" class="form-select">
                            <option value="">No preference - System will assign the best technician</option>
                        </select>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle me-1"></i> Selecting a technician is optional. Leave blank to let our system assign the best available technician for your service.
                        </small>
                    </div>

                    <div class="form-group" data-aos="fade-up" data-aos-delay="200">
                        <label class="form-label"><i class="fas fa-users me-2"></i>Available Technicians</label>
                        <div id="technicians-container" class="technician-list">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-search fa-2x mb-3"></i>
                                <p>Select a service type to view matching technicians</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" data-aos="fade-up" data-aos-delay="250">
                        <label class="form-label"><i class="fas fa-heading me-2"></i>Service Title *</label>
                        <input type="text" name="title" class="form-control" required 
                               placeholder="Brief description of the service needed (e.g., 'Kitchen Sink Leak Repair')"
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group" data-aos="fade-up" data-aos-delay="300">
                        <label class="form-label"><i class="fas fa-file-alt me-2"></i>Detailed Description *</label>
                        <textarea name="description" class="form-control" rows="4" required 
                                  placeholder="Please describe the service you need in detail. Include any specific issues, requirements, or preferences..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group" data-aos="fade-up" data-aos-delay="350">
                            <label class="form-label"><i class="fas fa-home me-2"></i>Full Address *</label>
                            <input type="text" name="address" class="form-control" required 
                                   value="<?php echo htmlspecialchars(($_POST['address'] ?? $userProfile['address']) ?? ''); ?>">
                        </div>
                        
                        <div class="form-group" data-aos="fade-up" data-aos-delay="400">
                            <label class="form-label"><i class="fas fa-map-marker-alt me-2"></i>Subcity *</label>
                            <input type="text" name="subcity" class="form-control" required 
                                   value="<?php echo htmlspecialchars(($_POST['subcity'] ?? $userProfile['subcity']) ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" data-aos="fade-up" data-aos-delay="450">
                            <label class="form-label"><i class="fas fa-map-pin me-2"></i>Woreda *</label>
                            <input type="text" name="woreda" class="form-control" required 
                                   value="<?php echo htmlspecialchars(($_POST['woreda'] ?? $userProfile['woreda']) ?? ''); ?>">
                        </div>
                        
                        <div class="form-group" data-aos="fade-up" data-aos-delay="500">
                            <label class="form-label"><i class="fas fa-calendar-alt me-2"></i>Preferred Date</label>
                            <input type="date" name="preferred_date" class="form-control" 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo htmlspecialchars($_POST['preferred_date'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group" data-aos="fade-up" data-aos-delay="550">
                        <label class="form-label"><i class="fas fa-clock me-2"></i>Preferred Time</label>
                        <input type="time" name="preferred_time" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['preferred_time'] ?? ''); ?>">
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle me-1"></i> Date and time preferences are not guaranteed but will be considered when scheduling
                        </small>
                    </div>
                    
                    <button type="submit" class="btn mt-4" id="submit-btn" data-aos="fade-up" data-aos-delay="600">
                        <i class="fas fa-paper-plane me-2"></i> Submit Service Request
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animation library
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
            });
        }

        const serviceSelect = document.getElementById('service-type');
        const container = document.getElementById('technicians-container');
        const technicianSelect = document.getElementById('technician-select');
        const presetTechnicianId = <?php echo $selectedTechnician ? (int)$selectedTechnician : 'null'; ?>;

        const renderStars = (avg) => {
            const fullStars = Math.floor(avg);
            const hasHalfStar = avg % 1 >= 0.5;
            const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
            
            return '★'.repeat(fullStars) + (hasHalfStar ? '½' : '') + '☆'.repeat(emptyStars);
        };

        const formatDate = (dateString) => {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-US', options);
        };

        const updateSelectOptions = (technicians) => {
            technicianSelect.innerHTML = '<option value="">No preference - System will assign the best technician</option>' + 
                technicians.map(tech => `
                    <option value="${tech.id}">${tech.first_name} ${tech.last_name} - ${tech.profession} (${tech.average_rating.toFixed(1)}★)</option>
                `).join('');

            if(presetTechnicianId) {
                technicianSelect.value = presetTechnicianId;
            }
        };

        serviceSelect.addEventListener('change', () => {
            const value = serviceSelect.value;
            if(!value) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-3"></i>
                        <p>Select a service type to view matching technicians</p>
                    </div>
                `;
                technicianSelect.innerHTML = '<option value="">No preference - System will assign the best technician</option>';
                return;
            }
            
            container.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <span>Loading available technicians...</span>
                </div>
            `;
            
            fetch(`request_service.php?ajax=1&service_type=${encodeURIComponent(value)}`)
                .then(response => response.json())
                .then(data => {
                    const technicians = data.technicians || [];
                    updateSelectOptions(technicians);
                    
                    if(technicians.length === 0) {
                        container.innerHTML = `
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>No technicians available</strong>
                                    <p>There are currently no technicians available for "${value}" services. You can still submit your request and we'll notify you when a technician becomes available.</p>
                                </div>
                            </div>
                        `;
                        return;
                    }
                    
                    container.innerHTML = technicians.map(tech => {
                        const reviewsMarkup = tech.reviews.length > 0
                            ? tech.reviews.map(review => `
                                <div class="review-item">
                                    <div class="review-header">
                                        <span class="reviewer-name">${review.homeowner_name || 'Homeowner'}</span>
                                        <span class="review-rating">${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}</span>
                                    </div>
                                    <div class="review-comment">${review.comment ? review.comment : 'No comment provided.'}</div>
                                    <div class="review-date">${formatDate(review.created_at)}</div>
                                </div>
                              `).join('')
                            : '<div class="no-reviews">No reviews yet</div>';

                        return `
                            <div class="technician-card" data-technician-id="${tech.id}">
                                <div class="technician-header">
                                    <div>
                                        <div class="technician-name">${tech.first_name} ${tech.last_name}</div>
                                        <div class="technician-profession">${tech.profession}</div>
                                    </div>
                                    <div class="technician-rating">
                                        <span class="rating-stars">${renderStars(tech.average_rating)}</span>
                                        <span class="rating-count">${tech.average_rating.toFixed(1)} (${tech.rating_count})</span>
                                    </div>
                                </div>
                                <div class="technician-contact">
                                    <div class="contact-item">
                                        <i class="fas fa-phone"></i>
                                        <span>${tech.phone || 'Not provided'}</span>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <span>${tech.email || 'Not provided'}</span>
                                    </div>
                                </div>
                                <div class="technician-reviews">
                                    <div class="reviews-title">Recent Reviews</div>
                                    ${reviewsMarkup}
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="selectTechnician(${tech.id}, '${tech.first_name} ${tech.last_name}')">
                                        <i class="fas fa-check me-1"></i> Select ${tech.first_name}
                                    </button>
                                </div>
                            </div>
                        `;
                    }).join('');
                })
                .catch(() => {
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>Unable to load technicians</strong>
                                <p>Please check your connection and try again.</p>
                            </div>
                        </div>
                    `;
                });
        });

        function selectTechnician(technicianId, technicianName) {
            technicianSelect.value = technicianId;
            
            // Highlight the selected technician card
            document.querySelectorAll('.technician-card').forEach(card => {
                card.style.borderColor = card.dataset.technicianId == technicianId ? 'var(--primary)' : 'var(--gray-light)';
                card.style.background = card.dataset.technicianId == technicianId ? 'rgba(67, 97, 238, 0.05)' : 'white';
            });
            
            // Show confirmation
            showToast(`Selected technician: ${technicianName}`, 'success');
        }

        function showToast(message, type) {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `alert alert-${type}`;
            toast.style.position = 'fixed';
            toast.style.bottom = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '1000';
            toast.style.minWidth = '300px';
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(toast);
            
            // Remove toast after 3 seconds
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Auto-load technicians if service is pre-selected
        if(serviceSelect.value) {
            const event = new Event('change');
            serviceSelect.dispatchEvent(event);
        }

        // Form submission handler
        document.getElementById('service-request-form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<div class="spinner me-2"></div> Creating Request...';
            submitBtn.disabled = true;
            
            // Allow form to submit normally
        });
    </script>
</body>
</html>