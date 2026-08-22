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

$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'profession' => trim($_GET['profession'] ?? '')
];

$technicians = $homeownerController->getTechnicians([
    'search' => $filters['search'] ?: null,
    'profession' => $filters['profession'] ?: null
]);

$professions = array_unique(array_filter(array_map(function($tech) {
    return $tech['profession'];
}, $homeownerController->getTechnicians())));
sort($professions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technicians - Homefix Pro</title>
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

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }

        .stat-card:nth-child(2) {
            border-left-color: var(--secondary);
        }

        .stat-card:nth-child(3) {
            border-left-color: var(--success);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            background: var(--gradient-primary);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: var(--gradient-secondary);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: var(--gradient-success);
        }

        .stat-info {
            flex: 1;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray);
            font-weight: 500;
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

        /* Filter Form */
        .filter-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto auto;
            gap: 15px;
            margin-bottom: 30px;
            background: var(--light);
            padding: 25px;
            border-radius: var(--radius);
            border: 1px solid var(--gray-light);
        }

        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 0;
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
            align-self: end;
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

        /* Technician Grid */
        .technician-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        @media (max-width: 768px) {
            .technician-grid {
                grid-template-columns: 1fr;
            }
        }

        .technician-card {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--gray-light);
            position: relative;
            overflow: hidden;
        }

        .technician-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .technician-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-primary);
        }

        .tech-header {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
        }

        .tech-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid rgba(67, 97, 238, 0.2);
            flex-shrink: 0;
        }

        .tech-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tech-info {
            flex: 1;
        }

        .tech-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .tech-profession {
            background: var(--gradient-primary);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .tech-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 10px 0;
        }

        .rating-stars {
            color: #f39c12;
            font-size: 16px;
        }

        .rating-text {
            color: var(--gray);
            font-size: 14px;
            font-weight: 500;
        }

        .tech-contact {
            margin-bottom: 15px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            color: var(--gray);
            font-size: 14px;
        }

        .contact-item i {
            width: 16px;
            color: var(--primary);
        }

        /* Reviews Section */
        .reviews {
            background: var(--light);
            border-radius: var(--radius);
            padding: 15px;
            margin: 15px 0;
            border: 1px solid var(--gray-light);
        }

        .reviews-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .review-item {
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .review-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .reviewer-name {
            font-weight: 600;
            color: var(--dark);
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
            font-style: italic;
        }

        .tech-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .tech-actions .btn {
            flex: 1;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--gray-light);
        }

        .empty-state h4 {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .empty-state p {
            max-width: 400px;
            margin: 0 auto 20px;
        }

        /* Loading Spinner */
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
            
            .tech-header {
                flex-direction: column;
                text-align: center;
            }
            
            .tech-avatar {
                align-self: center;
            }
            
            .tech-actions {
                flex-direction: column;
            }
        }

        /* Animation for elements */
        [data-aos] {
            opacity: 0;
            transition-property: opacity, transform;
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

        /* Status Badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-available {
            background: rgba(76, 201, 240, 0.15);
            color: var(--success);
        }

        .status-busy {
            background: rgba(248, 150, 30, 0.15);
            color: var(--warning);
        }

        .status-offline {
            background: rgba(108, 117, 125, 0.15);
            color: var(--gray);
        }

        .stats-container .stat-card {
            background: #ffffff !important;
        }

        .stats-container .stat-value {
            color: var(--dark) !important;
            opacity: 1 !important;
        }

        .stats-container .stat-label {
            color: var(--gray) !important;
            opacity: 1 !important;
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
                    <h1>Technicians Directory</h1>
                    <p>Browse verified technicians, see their ratings, and contact them directly</p>
                </div>
                <div class="header-actions">
                    <div class="notification-bell">
                        <i class="fa-solid fa-bell"></i>
                        <?php $unreadNotifications = $homeownerController->getUnreadNotificationCount(); ?>
                        <?php if(!empty($unreadNotifications)): ?>
                            <span class="notification-badge"></span>
                        <?php endif; ?>
                    </div>
                    <a href="request_service.php" class="btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        Request Service
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container" data-aos="fade-up">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo count($technicians); ?></div>
                        <div class="stat-label">Available Technicians</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo count($professions); ?></div>
                        <div class="stat-label">Professions</div>
                    </div>
                </div>
            </div>

            <!-- Technicians Directory Card -->
            <div class="card" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header">
                    <div>
                        <h2 class="card-title"><i class="fas fa-user-tie"></i> Find Your Technician</h2>
                        <p class="card-subtitle">Browse our verified technicians and find the perfect professional for your needs</p>
                    </div>
                </div>

                <!-- Filter Form -->
                <form class="filter-form" method="GET">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-search me-2"></i>Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, skill, phone, email..." 
                               value="<?php echo htmlspecialchars($filters['search']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-tools me-2"></i>Profession</label>
                        <select name="profession" class="form-select">
                            <option value="">All Professions</option>
                            <?php foreach($professions as $profession): ?>
                                <option value="<?php echo htmlspecialchars($profession); ?>" 
                                    <?php echo ($filters['profession'] === $profession) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($profession); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <?php if($filters['search'] || $filters['profession']): ?>
                        <a class="btn btn-outline" href="technicians.php">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </form>

                <?php if(count($technicians) === 0): ?>
                    <div class="empty-state" data-aos="fade-up">
                        <i class="fas fa-search"></i>
                        <h4>No Technicians Found</h4>
                        <p>No technicians match your filters right now. Try adjusting the search or check back later.</p>
                        <a href="technicians.php" class="btn">
                            <i class="fas fa-refresh"></i> Reset Filters
                        </a>
                    </div>
                <?php else: ?>
                    <div class="technician-grid">
                        <?php foreach($technicians as $index => $tech): ?>
                            <?php
                                $photoPath = !empty($tech['profile_photo'])
                                    ? '../../assets/uploads/profiles/' . $tech['profile_photo']
                                    : 'https://ui-avatars.com/api/?background=4361ee&color=ffffff&name=' . urlencode($tech['first_name'] . ' ' . $tech['last_name']);
                            ?>
                            <div class="technician-card" data-aos="fade-up" data-aos-delay="<?php echo ($index % 5) * 100; ?>">
                                <div class="tech-header">
                                    <div class="tech-avatar">
                                        <img src="<?php echo $photoPath; ?>" alt="<?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?>">
                                    </div>
                                    <div class="tech-info">
                                        <div class="tech-name"><?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?></div>
                                        <span class="tech-profession"><?php echo htmlspecialchars($tech['profession']); ?></span>
                                        <div class="tech-rating">
                                            <div class="rating-stars">
                                                <?php 
                                                $fullStars = round($tech['average_rating']);
                                                for($i = 1; $i <= 5; $i++): 
                                                    if($i <= $fullStars): 
                                                ?>
                                                    <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; endfor; ?>
                                            </div>
                                            <span class="rating-text">
                                                <?php echo number_format((float)$tech['average_rating'], 1); ?> (<?php echo $tech['rating_count']; ?> reviews)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="tech-contact">
                                    <div class="contact-item">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo htmlspecialchars($tech['phone'] ?: 'Not provided'); ?></span>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo htmlspecialchars($tech['email'] ?: 'Not provided'); ?></span>
                                    </div>
                                </div>
                                
                                <?php 
                                    $reviews = $homeownerController->getTechnicianReviews($tech['id'], 2);
                                ?>
                                
                                <div class="reviews">
                                    <div class="reviews-title">
                                        <i class="fas fa-comments"></i> Recent Feedback
                                    </div>
                                    <?php if(count($reviews) === 0): ?>
                                        <div class="review-item">
                                            <p class="review-comment">No feedback yet.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach($reviews as $review): ?>
                                            <div class="review-item">
                                                <div class="review-header">
                                                    <span class="reviewer-name">
                                                        <?php echo htmlspecialchars(trim($review['homeowner_first_name'] . ' ' . $review['homeowner_last_name'])); ?>
                                                    </span>
                                                    <span class="review-rating">
                                                        <?php 
                                                        for($i = 1; $i <= 5; $i++) {
                                                            echo $i <= $review['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                                <p class="review-comment">
                                                    <?php echo $review['comment'] ? htmlspecialchars($review['comment']) : 'No comment provided.'; ?>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="tech-actions">
                                    <a class="btn btn-sm" href="request_service.php?service_type=<?php echo urlencode($tech['profession']); ?>&technician_id=<?php echo (int)$tech['id']; ?>">
                                        <i class="fas fa-tools"></i> Request Service
                                    </a>
                                    <a class="btn btn-outline btn-sm" href="feedback.php?technician_id=<?php echo (int)$tech['id']; ?>">
                                        <i class="fas fa-star"></i> Give Feedback
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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

        // Add hover effects to cards
        const cards = document.querySelectorAll('.technician-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(-5px)';
            });
        });

        // Button click effects
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });

        // Filter form submission with loading state
        document.querySelector('.filter-form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="spinner me-2"></div> Filtering...';
            submitBtn.disabled = true;
            
            // Allow form to submit normally
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 1000);
        });

        // Search functionality enhancement
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                if (this.value.length > 0) {
                    this.style.background = "rgba(67, 97, 238, 0.05)";
                } else {
                    this.style.background = "";
                }
            });
        }

        // Add click effect to technician cards
        document.querySelectorAll('.technician-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Only trigger if not clicking on a button
                if (!e.target.closest('a') && !e.target.closest('button')) {
                    const requestBtn = this.querySelector('a[href*="request_service"]');
                    if (requestBtn) {
                        requestBtn.click();
                    }
                }
            });
        });

        // Add loading state to request service buttons
        document.querySelectorAll('a[href*="request_service"]').forEach(link => {
            link.addEventListener('click', function(e) {
                const originalText = this.innerHTML;
                this.innerHTML = '<div class="spinner me-2"></div> Redirecting...';
                this.style.pointerEvents = 'none';
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.style.pointerEvents = '';
                }, 2000);
            });
        });
    </script>
</body>
</html>