<?php
ob_start(); // Start output buffering to prevent header errors
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HomeFix Pro - Your trusted partner for professional home repair and maintenance services">
    <meta name="keywords" content="home repair, maintenance, plumbing, electrical, handyman services">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/header.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <!-- Main Header Section -->
    <header class="main-header">
        <div class="container">
            <nav class="navbar">
                <!-- Brand Logo -->
                <div class="nav-brand">
                    <a href="<?php echo SITE_URL; ?>/index.php" class="logo-link">
                        <div class="logo-container">
                            <i class="fas fa-tools logo-icon"></i>
                            <span class="logo-text">HomeFix Pro</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/index.php" class="nav-link <?php echo (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                            <i class="fas fa-home nav-icon"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/services.php" class="nav-link <?php echo (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'services.php') ? 'active' : ''; ?>">
                            <i class="fas fa-concierge-bell nav-icon"></i>
                            <span>Services</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/about.php" class="nav-link <?php echo (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : ''; ?>">
                            <i class="fas fa-users nav-icon"></i>
                            <span>About Us</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/contact.php" class="nav-link <?php echo (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>">
                            <i class="fas fa-envelope nav-icon"></i>
                            <span>Contact Us</span>
                        </a>
                    </li>

                    <!-- User Authentication Section -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- Dashboard Link Based on User Role -->
                        <?php if($_SESSION['user_role'] == 'admin'): ?>
                            <li class="nav-item">
                                <a href="<?php echo SITE_URL; ?>/views/admin/dashboard.php" class="nav-link dashboard-link <?php echo (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                                    <i class="fas fa-tachometer-alt nav-icon"></i>
                                    <span>Admin Dashboard</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a href="<?php echo SITE_URL; ?>/dashboard.php" class="nav-link dashboard-link <?php echo (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                                    <i class="fas fa-tachometer-alt nav-icon"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- User Profile Section -->
                        <li class="nav-item user-profile-container">
                            <div class="user-profile">
                                <?php if(isset($_SESSION['profile_photo']) && !empty($_SESSION['profile_photo'])): ?>
                                    <img src="<?php echo SITE_URL; ?>/assets/uploads/profiles/<?php echo $_SESSION['profile_photo']; ?>" 
                                         alt="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?>'s Profile Picture" 
                                         class="nav-profile-img">
                                <?php else: ?>
                                    <div class="nav-profile-img default">
                                        <span class="profile-initial"><?php echo isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U'; ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="user-info">
                                    <span class="user-name"><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?></span>
                                    <span class="user-role"><?php echo isset($_SESSION['user_role']) ? ucfirst($_SESSION['user_role']) : 'User'; ?></span>
                                </div>
                            </div>
                        </li>

                        <!-- Logout Button -->
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/logout.php" class="nav-link logout-link">
                                <i class="fas fa-sign-out-alt nav-icon"></i>
                                <span>Logout</span>
                            </a>
                        </li>

                    <?php else: ?>
                        <!-- Authentication Buttons for Guests -->
                        <li class="nav-item auth-section">
                            <div class="auth-buttons">
                                <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-outline login-btn">
                                    <i class="fas fa-sign-in-alt"></i>
                                    <span>Login</span>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary register-btn">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Register</span>
                                </a>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <script src="<?php echo SITE_URL; ?>/assets/js/header.js"></script>