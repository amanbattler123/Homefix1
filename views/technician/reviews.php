<?php
session_start();
require_once '../../includes/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'technician') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../controllers/TechnicianController.php';

$conn = getDBConnection();
$technicianController = new TechnicianController($conn);
$technicianController->markReviewsSeen();
$reviews = $technicianController->getReviews();

// Calculate average rating
$totalRating = 0;
$ratingCount = count($reviews);
foreach($reviews as $review) {
    $totalRating += (int)$review['rating'];
}
$averageRating = $ratingCount > 0 ? round($totalRating / $ratingCount, 1) : 0;

// Rating distribution
$ratingDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach($reviews as $review) {
    $rating = (int)$review['rating'];
    if(isset($ratingDistribution[$rating])) {
        $ratingDistribution[$rating]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - HomeFix Pro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Using landing.php color scheme */
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --success: #4cc9f0;
            --card-bg: rgba(255, 255, 255, 0.93);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            
            /* Additional colors */
            --sidebar-bg: #16213e;
            --sidebar-dark: #0f172a;
            --sidebar-light: rgba(255, 255, 255, 0.1);
            --sidebar-text: #ffffff;
            --sidebar-text-muted: rgba(255, 255, 255, 0.7);
            --sidebar-border: rgba(255, 255, 255, 0.15);
            --star-color: #ffd700;
            --star-empty: rgba(255, 255, 255, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: var(--dark);
            min-height: 100vh;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 280px;
        }

        .header {
            margin-bottom: 30px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(90deg, var(--success), white);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            max-width: 600px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--sidebar-border);
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .stat-card h3 {
            color: white;
            font-size: 1.1rem;
            margin-bottom: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-card h3 i {
            color: var(--success);
        }

        .rating-display {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .rating-number {
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            line-height: 1;
        }

        .rating-stars {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .star-rating {
            display: flex;
            gap: 3px;
        }

        .star-rating i {
            font-size: 1.4rem;
            color: var(--star-color);
        }

        .rating-count {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
        }

        /* Rating Distribution */
        .distribution-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 12px;
        }

        .distribution-label {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 100px;
            color: white;
        }

        .distribution-stars {
            display: flex;
            gap: 2px;
        }

        .distribution-stars i {
            color: var(--star-color);
            font-size: 1rem;
        }

        .distribution-bar {
            flex: 1;
            height: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }

        .distribution-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 5px;
            transition: width 1s ease;
        }

        .distribution-count {
            min-width: 40px;
            text-align: right;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 600;
        }

        /* Reviews List */
        .reviews-container {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid var(--sidebar-border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .reviews-header {
            padding: 25px 30px;
            border-bottom: 1px solid var(--sidebar-border);
            background: rgba(67, 97, 238, 0.1);
        }

        .reviews-header h2 {
            color: white;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reviews-header h2 i {
            color: var(--success);
        }

        .reviews-list {
            max-height: 600px;
            overflow-y: auto;
            padding: 20px 30px;
        }

        .reviews-list::-webkit-scrollbar {
            width: 6px;
        }

        .reviews-list::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }

        .reviews-list::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 3px;
        }

        .review-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid var(--sidebar-border);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .review-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .review-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(180deg, var(--primary), var(--accent));
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .review-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .review-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .review-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .user-info h4 {
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .user-info .date {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .review-rating {
            display: flex;
            gap: 3px;
        }

        .review-rating i {
            font-size: 1.3rem;
            color: var(--star-color);
        }

        .review-rating i.empty {
            color: var(--star-empty);
        }

        .review-content {
            margin-bottom: 15px;
        }

        .review-text {
            color: white;
            line-height: 1.6;
            font-size: 1rem;
            position: relative;
            padding-left: 15px;
        }

        .review-text::before {
            content: '"';
            position: absolute;
            left: 0;
            top: -5px;
            font-size: 2rem;
            color: var(--success);
            font-family: Georgia, serif;
            opacity: 0.5;
        }

        .review-text::after {
            content: '"';
            position: absolute;
            right: 0;
            bottom: -10px;
            font-size: 2rem;
            color: var(--success);
            font-family: Georgia, serif;
            opacity: 0.5;
        }

        .review-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid var(--sidebar-border);
        }

        .review-job {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .review-job i {
            color: var(--success);
        }

        .review-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 15px;
            border-radius: 10px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 30px;
            color: rgba(255, 255, 255, 0.7);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--success);
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: white;
        }

        .empty-state p {
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
                padding-top: 80px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .review-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .review-footer {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .review-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .review-item {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .rating-display {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .distribution-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .distribution-bar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-star"></i> Customer Reviews</h1>
                <p>Feedback you've received from homeowners about your service quality.</p>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><i class="fas fa-chart-line"></i> Overall Rating</h3>
                    <div class="rating-display">
                        <div class="rating-number"><?php echo $averageRating; ?></div>
                        <div class="rating-stars">
                            <div class="star-rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= floor($averageRating) ? 'filled' : ($i - 0.5 <= $averageRating ? 'fas fa-star-half-alt' : 'far fa-star'); ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="rating-count">Based on <?php echo $ratingCount; ?> review<?php echo $ratingCount !== 1 ? 's' : ''; ?></div>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <h3><i class="fas fa-chart-bar"></i> Rating Distribution</h3>
                    <?php foreach($ratingDistribution as $stars => $count): ?>
                        <div class="distribution-item">
                            <div class="distribution-label">
                                <div class="distribution-stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $stars ? 'filled' : 'empty'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="distribution-bar">
                                <?php 
                                    $percentage = $ratingCount > 0 ? ($count / $ratingCount) * 100 : 0;
                                ?>
                                <div class="distribution-fill" style="width: <?php echo $percentage; ?>%;"></div>
                            </div>
                            <div class="distribution-count"><?php echo $count; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="reviews-container">
                <div class="reviews-header">
                    <h2><i class="fas fa-comments"></i> All Reviews (<?php echo $ratingCount; ?>)</h2>
                </div>
                
                <div class="reviews-list">
                    <?php if(count($reviews) > 0): ?>
                        <?php foreach($reviews as $review): ?>
                            <div class="review-item" data-rating="<?php echo $review['rating']; ?>">
                                <div class="review-header">
                                    <div class="review-user">
                                        <div class="review-avatar">
                                            <?php if(!empty($review['profile_photo'])): ?>
                                                <img src="<?php echo SITE_URL . '/assets/uploads/profiles/' . htmlspecialchars($review['profile_photo']); ?>" alt="<?php echo htmlspecialchars($review['first_name']); ?>">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($review['first_name'], 0, 1) . substr($review['last_name'], 0, 1)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="user-info">
                                            <h4><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h4>
                                            <div class="date">Reviewed on <?php echo date('F j, Y', strtotime($review['created_at'])); ?></div>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <?php 
                                            $rating = (int)$review['rating'];
                                            for($i = 1; $i <= 5; $i++): 
                                        ?>
                                            <i class="fas fa-star <?php echo $i <= $rating ? 'filled' : 'empty'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="review-content">
                                    <div class="review-text">
                                        <?php echo nl2br(htmlspecialchars($review['comment'] ?? 'No comment provided.')); ?>
                                    </div>
                                </div>
                                
                                <div class="review-footer">
                                    <div class="review-job">
                                        <i class="fas fa-briefcase"></i>
                                        <span>Service provided on <?php echo date('M j, Y', strtotime($review['created_at'])); ?></span>
                                    </div>
                                    <div class="review-actions">
                                        <button class="action-btn" onclick="shareReview(<?php echo $review['id']; ?>)">
                                            <i class="fas fa-share"></i> Share
                                        </button>
                                        <button class="action-btn" onclick="reportReview(<?php echo $review['id']; ?>)">
                                            <i class="fas fa-flag"></i> Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-star"></i>
                            <h3>No Reviews Yet</h3>
                            <p>You haven't received any reviews from homeowners yet. Keep providing excellent service, and reviews will start coming in!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate rating bars
            document.querySelectorAll('.distribution-fill').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 300);
            });

            // Filter reviews by rating
            const filterReviews = (rating) => {
                const reviews = document.querySelectorAll('.review-item');
                reviews.forEach(review => {
                    if(rating === 'all' || review.dataset.rating == rating) {
                        review.style.display = 'block';
                    } else {
                        review.style.display = 'none';
                    }
                });
            };

            // Add rating filter buttons (optional feature)
            const ratingFilters = document.createElement('div');
            ratingFilters.className = 'rating-filters';
            ratingFilters.innerHTML = `
                <button onclick="filterReviews('all')">All</button>
                <button onclick="filterReviews(5)">5★</button>
                <button onclick="filterReviews(4)">4★</button>
                <button onclick="filterReviews(3)">3★</button>
                <button onclick="filterReviews(2)">2★</button>
                <button onclick="filterReviews(1)">1★</button>
            `;

            // Scroll to top when clicking a review (for mobile)
            document.querySelectorAll('.review-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    if(window.innerWidth <= 768 && !e.target.closest('.review-actions')) {
                        this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            });

            // Add smooth hover effect to rating stars
            document.querySelectorAll('.review-rating i.filled').forEach(star => {
                star.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.2)';
                    this.style.transition = 'transform 0.2s ease';
                });
                
                star.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });

            // Share review function
            window.shareReview = function(reviewId) {
                const review = document.querySelector(`.review-item[data-review-id="${reviewId}"]`);
                if(review) {
                    const text = review.querySelector('.review-text').textContent;
                    const rating = review.dataset.rating;
                    
                    if(navigator.share) {
                        navigator.share({
                            title: `Customer Review - ${rating}★`,
                            text: text,
                            url: window.location.href
                        });
                    } else {
                        alert('Share feature is not supported in your browser');
                    }
                }
            };

            // Report review function
            window.reportReview = function(reviewId) {
                const reason = prompt('Please briefly describe why you are reporting this review:');
                if(reason && reason.trim()) {
                    // In a real app, you would send this to your server
                    console.log('Reporting review', reviewId, 'for reason:', reason);
                    alert('Thank you for your report. Our team will review it shortly.');
                }
            };

            // Filter reviews function
            window.filterReviews = filterReviews;

            // Initialize all reviews as visible
            filterReviews('all');
        });
    </script>
</body>
</html>