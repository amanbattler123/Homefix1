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

$message = '';

if(isset($_POST['submit_feedback'])) {
    $data = [
        'technician_id' => $_POST['technician_id'],
        'rating' => $_POST['rating'],
        'comment' => $_POST['comment']
    ];
    
    if($homeownerController->submitFeedback($data)) {
        $message = '<div class="alert success">
            <i class="fas fa-check-circle"></i>
            <span>Feedback submitted successfully!</span>
        </div>';
    } else {
        $message = '<div class="alert error">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Error submitting feedback. Please try again.</span>
        </div>';
    }
}

$filters = [
    'profession' => $_GET['filter_profession'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$technicians = $homeownerController->getTechnicians($filters);

// Get previous ratings
$query = "SELECT r.*, u.first_name, u.last_name, u.profession 
          FROM ratings r 
          JOIN users u ON r.technician_id = u.id 
          WHERE r.homeowner_id = ? 
          ORDER BY r.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$previousRatings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalFeedbacks = count($previousRatings);
$availableTechnicians = count($technicians);
$averageRatingGiven = $totalFeedbacks > 0
    ? round(array_sum(array_column($previousRatings, 'rating')) / $totalFeedbacks, 1)
    : 0;
$uniqueTechniciansRated = $totalFeedbacks > 0
    ? count(array_unique(array_column($previousRatings, 'technician_id')))
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Give Feedback - HomeFix Pro</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary: #7c3aed;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --border: #e2e8f0;
            --radius: 8px;
            --shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: var(--light);
            color: var(--dark);
            line-height: 1.5;
            overflow-x: hidden;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Header */
        .page-header {
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .page-header p {
            color: var(--gray);
            font-size: 14px;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 18px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin: 0 auto 12px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .stat-label {
            color: var(--gray);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        .alert.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 3px solid var(--success);
        }

        .alert.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 3px solid var(--danger);
        }

        .alert i {
            font-size: 18px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Search Section */
        .search-section {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 10px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
        }

        .form-input, .form-select {
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-input {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: white;
        }

        /* Technician Cards */
        .technician-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .technician-card {
            background: white;
            border-radius: var(--radius);
            padding: 16px;
            box-shadow: var(--shadow);
            transition: all 0.2s ease;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .technician-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .tech-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .tech-avatar {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .tech-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tech-info {
            flex: 1;
            min-width: 0;
        }

        .tech-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tech-profession {
            display: inline-block;
            background: #e0e7ff;
            color: var(--primary);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .tech-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 12px 0;
            padding: 10px;
            background: #f8fafc;
            border-radius: 6px;
        }

        .tech-stat {
            text-align: center;
        }

        .stat-value-small {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
        }

        .stat-label-small {
            font-size: 11px;
            color: var(--gray);
            margin-top: 2px;
        }

        .rating-stars {
            color: #fbbf24;
            font-size: 14px;
            letter-spacing: 1px;
            margin: 8px 0;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 30px 15px;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .empty-icon {
            font-size: 36px;
            color: var(--gray);
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 6px;
        }

        .empty-description {
            color: var(--gray);
            font-size: 13px;
            max-width: 250px;
            margin: 0 auto;
        }

        /* Feedback History */
        .feedback-section {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            margin-top: 20px;
            box-shadow: var(--shadow);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .feedback-timeline {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 300px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .feedback-timeline::-webkit-scrollbar {
            width: 4px;
        }

        .feedback-timeline::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 2px;
        }

        .feedback-timeline::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 2px;
        }

        .feedback-item {
            background: #f8fafc;
            border-radius: 6px;
            padding: 12px;
            border-left: 3px solid var(--primary);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .feedback-technician {
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .feedback-profession {
            color: var(--gray);
            font-size: 12px;
            margin-top: 2px;
        }

        .feedback-rating {
            color: #fbbf24;
            font-size: 12px;
        }

        .feedback-date {
            color: var(--gray);
            font-size: 12px;
            margin-bottom: 6px;
        }

        .feedback-comment {
            color: var(--dark);
            font-size: 13px;
            line-height: 1.4;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius);
            width: 100%;
            max-width: 450px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            color: var(--gray);
            cursor: pointer;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: background 0.2s ease;
        }

        .modal-close:hover {
            background: #f1f5f9;
        }

        .modal-body {
            padding: 16px;
        }

        .modal-technician {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .modal-rating {
            margin-bottom: 20px;
        }

        .rating-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--dark);
            font-size: 14px;
        }

        .rating-stars-input {
            display: flex;
            gap: 6px;
            font-size: 20px;
            color: #e5e7eb;
            margin-bottom: 6px;
        }

        .rating-stars-input .star {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .rating-stars-input .star:hover,
        .rating-stars-input .star.active {
            color: #fbbf24;
            transform: scale(1.1);
        }

        .rating-value {
            font-size: 13px;
            color: var(--gray);
        }

        .form-textarea {
            width: 100%;
            min-height: 80px;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 13px;
            resize: vertical;
            transition: all 0.2s ease;
        }

        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-block {
            flex: 1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .technician-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .modal-actions {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header h1 {
                font-size: 20px;
            }
            
            .technician-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <?php echo $message; ?>

            <div class="page-header">
                <h1>Give Feedback</h1>
                <p>Rate and review your service experiences</p>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comment"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalFeedbacks; ?></div>
                    <div class="stat-label">Feedbacks Given</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $uniqueTechniciansRated; ?></div>
                    <div class="stat-label">Technicians Rated</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-value"><?php echo $averageRatingGiven; ?>/5</div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-value"><?php echo $availableTechnicians; ?></div>
                    <div class="stat-label">Available Pros</div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <h2 class="section-title">Find Technicians to Rate</h2>
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="form-input" 
                           placeholder="Search by name or profession..." 
                           value="<?php echo htmlspecialchars($filters['search']); ?>">
                    <select name="filter_profession" class="form-select">
                        <option value="">All Professions</option>
                        <?php 
                        $uniqueProfessions = [];
                        foreach($homeownerController->getTechnicians(['profession' => null]) as $tec):
                            if(!empty($tec['profession']) && !in_array($tec['profession'], $uniqueProfessions)):
                                $uniqueProfessions[] = $tec['profession'];
                        ?>
                            <option value="<?php echo htmlspecialchars($tec['profession']); ?>" 
                                    <?php echo ($filters['profession'] === $tec['profession']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tec['profession']); ?>
                            </option>
                        <?php endif; endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>

                <?php if(count($technicians) > 0): ?>
                    <div class="technician-grid">
                        <?php foreach($technicians as $tech): 
                            $initials = strtoupper(substr($tech['first_name'],0,1) . substr($tech['last_name'],0,1));
                            $ratingStars = str_repeat('★', round($tech['average_rating'])) . str_repeat('☆', 5 - round($tech['average_rating']));
                        ?>
                            <div class="technician-card" 
                                 onclick="openFeedbackModal(<?php echo $tech['id']; ?>, 
                                                           '<?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name'], ENT_QUOTES); ?>',
                                                           '<?php echo htmlspecialchars($tech['profession'], ENT_QUOTES); ?>',
                                                           <?php echo round($tech['average_rating'], 1); ?>,
                                                           <?php echo $tech['rating_count']; ?>,
                                                           '<?php echo !empty($tech['profile_photo']) ? '../../assets/uploads/profiles/' . htmlspecialchars($tech['profile_photo'], ENT_QUOTES) : ''; ?>',
                                                           '<?php echo $initials; ?>')">
                                <div class="tech-header">
                                    <div class="tech-avatar">
                                        <?php if(!empty($tech['profile_photo'])): ?>
                                            <img src="../../assets/uploads/profiles/<?php echo htmlspecialchars($tech['profile_photo']); ?>" 
                                                 alt="<?php echo htmlspecialchars($tech['first_name']); ?>">
                                        <?php else: ?>
                                            <?php echo $initials; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tech-info">
                                        <div class="tech-name"><?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?></div>
                                        <span class="tech-profession"><?php echo htmlspecialchars($tech['profession']); ?></span>
                                    </div>
                                </div>
                                <div class="rating-stars">
                                    <?php echo $ratingStars; ?>
                                    <span style="color: var(--gray); font-size: 12px; margin-left: 5px;">
                                        <?php echo round($tech['average_rating'], 1); ?> (<?php echo $tech['rating_count']; ?>)
                                    </span>
                                </div>
                                <div class="tech-stats">
                                    <div class="tech-stat">
                                        <div class="stat-value-small">#<?php echo $tech['id']; ?></div>
                                        <div class="stat-label-small">ID</div>
                                    </div>
                                    <div class="tech-stat">
                                        <div class="stat-value-small"><?php echo $tech['rating_count']; ?></div>
                                        <div class="stat-label-small">Reviews</div>
                                    </div>
                                </div>
                                <button class="btn btn-secondary" style="width: 100%; font-size: 13px; padding: 8px 16px;">
                                    <i class="fas fa-star"></i> Rate Technician
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="empty-title">No Technicians Found</h3>
                        <p class="empty-description">
                            Try adjusting your search or filter to find technicians.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Feedback History -->
            <div class="feedback-section">
                <div class="section-header">
                    <h2 class="section-title">Your Feedback History</h2>
                    <div style="background: var(--primary); color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                        <?php echo $totalFeedbacks; ?> reviews
                    </div>
                </div>

                <?php if(count($previousRatings) > 0): ?>
                    <div class="feedback-timeline">
                        <?php foreach($previousRatings as $rating): 
                            $ratingStars = str_repeat('★', $rating['rating']) . str_repeat('☆', 5 - $rating['rating']);
                        ?>
                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <div>
                                        <div class="feedback-technician">
                                            <?php echo htmlspecialchars($rating['first_name'] . ' ' . $rating['last_name']); ?>
                                        </div>
                                        <div class="feedback-profession">
                                            <?php echo htmlspecialchars($rating['profession']); ?>
                                        </div>
                                    </div>
                                    <div class="feedback-rating">
                                        <?php echo $ratingStars; ?>
                                        <span style="color: var(--gray); font-size: 11px;">
                                            (<?php echo $rating['rating']; ?>/5)
                                        </span>
                                    </div>
                                </div>
                                <div class="feedback-date">
                                    <?php echo date('M j, Y', strtotime($rating['created_at'])); ?>
                                </div>
                                <?php if($rating['comment']): ?>
                                    <div class="feedback-comment">
                                        <?php echo htmlspecialchars($rating['comment']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-comment"></i>
                        </div>
                        <h3 class="empty-title">No Feedback Yet</h3>
                        <p class="empty-description">
                            Your feedback history will appear here once you start rating technicians.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div id="feedback-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Rate Technician</h3>
                <button class="modal-close" onclick="closeFeedbackModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-technician">
                    <div id="modal-avatar" class="tech-avatar">
                        <!-- Avatar will be set by JavaScript -->
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 15px;" id="modal-name"></div>
                        <div style="color: var(--gray); font-size: 13px;" id="modal-profession"></div>
                        <div style="font-size: 12px; color: var(--gray); margin-top: 2px;" id="modal-current-rating"></div>
                    </div>
                </div>

                <form method="POST" id="feedback-form">
                    <input type="hidden" name="technician_id" id="modal-tech-id">
                    
                    <div class="modal-rating">
                        <label class="rating-label">Your Rating</label>
                        <div class="rating-stars-input" id="rating-stars">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                        <div class="rating-value" id="rating-text">Select a rating</div>
                        <input type="hidden" name="rating" id="modal-rating-value" required>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="rating-label">Your Feedback (Optional)</label>
                        <textarea name="comment" class="form-textarea" 
                                  placeholder="Share your experience with this technician..."></textarea>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary btn-block" onclick="closeFeedbackModal()">
                            Cancel
                        </button>
                        <button type="submit" name="submit_feedback" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Submit Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Rating stars interaction
        const stars = document.querySelectorAll('#rating-stars .star');
        const ratingValue = document.getElementById('modal-rating-value');
        const ratingText = document.getElementById('rating-text');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                ratingValue.value = value;
                
                stars.forEach(s => {
                    if(s.getAttribute('data-value') <= value) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
                
                // Update rating text
                const ratings = {
                    1: "Poor",
                    2: "Fair",
                    3: "Good",
                    4: "Very Good",
                    5: "Excellent"
                };
                ratingText.textContent = `${value}/5 - ${ratings[value]}`;
            });
        });

        // Open feedback modal
        function openFeedbackModal(techId, techName, techProfession, techRating, techReviews, techPhoto, techInitials) {
            // Set modal content
            const modalAvatar = document.getElementById('modal-avatar');
            if (techPhoto) {
                modalAvatar.innerHTML = `<img src="${techPhoto}" alt="${techName}">`;
            } else {
                modalAvatar.textContent = techInitials;
                modalAvatar.style.background = '#2563eb';
                modalAvatar.style.color = 'white';
            }
            
            document.getElementById('modal-name').textContent = techName;
            document.getElementById('modal-profession').textContent = techProfession;
            document.getElementById('modal-current-rating').textContent = 
                `Current rating: ${techRating}/5 (${techReviews} reviews)`;
            
            document.getElementById('modal-tech-id').value = techId;
            
            // Reset rating
            stars.forEach(s => s.classList.remove('active'));
            ratingValue.value = '';
            ratingText.textContent = 'Select a rating';
            
            // Show modal
            document.getElementById('feedback-modal').style.display = 'flex';
        }

        // Close feedback modal
        function closeFeedbackModal() {
            document.getElementById('feedback-modal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('feedback-modal');
            if (event.target === modal) {
                closeFeedbackModal();
            }
        };

        // Handle form submission
        document.getElementById('feedback-form').addEventListener('submit', function(e) {
            if (!ratingValue.value) {
                e.preventDefault();
                alert('Please select a rating before submitting.');
                return;
            }
        });

        // Close alert messages
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                }, 4000);
            });
        });
    </script>
</body>
</html>