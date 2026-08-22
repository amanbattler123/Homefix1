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

$message = '';
$passwordMessage = '';

function buildMediaUrls($profile, $basePath = '') {
    return [
        !empty($profile['profile_photo'])
            ? SITE_URL . '/assets/uploads/profiles/' . rawurlencode($profile['profile_photo'])
            : '',
        !empty($profile['certification_file'])
            ? SITE_URL . '/assets/uploads/certifications/' . rawurlencode($profile['certification_file'])
            : '',
        !empty($profile['certification_file'])
            ? basename($profile['certification_file'])
            : '',
        !empty($profile['residence_id_file'])
            ? SITE_URL . '/assets/uploads/residence_ids/' . rawurlencode($profile['residence_id_file'])
            : '',
        !empty($profile['residence_id_file'])
            ? basename($profile['residence_id_file'])
            : ''
    ];
}

if(isset($_POST['update_profile'])) {
    $data = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'subcity' => $_POST['subcity'],
        'woreda' => $_POST['woreda'],
        'profession' => $_POST['profession'],
        'bank_account' => $_POST['bank_account'],
        'tele_birr' => $_POST['tele_birr']
    ];
    
    $result = $technicianController->updateProfile($data, $_FILES);
    if($result['success']) {
        $message = '<div class="alert alert-success" data-aos="fade-down">Profile updated successfully!</div>';
        $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
        if(isset($result['profile_photo'])) {
            $_SESSION['profile_photo'] = $result['profile_photo'];
        }
        $profile = $technicianController->getUserProfile(); // Refresh profile data
    } else {
        $errorText = isset($result['message']) ? htmlspecialchars($result['message']) : 'Error updating profile.';
        $message = '<div class="alert alert-danger" data-aos="fade-down">' . $errorText . '</div>';
    }
}

if(isset($_POST['change_password'])) {
    $current = trim($_POST['current_password'] ?? '');
    $new = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if(empty($current) || empty($new) || empty($confirm)) {
        $passwordMessage = '<div class="alert alert-danger" data-aos="fade-down">All password fields are required.</div>';
    } elseif($new !== $confirm) {
        $passwordMessage = '<div class="alert alert-danger" data-aos="fade-down">New passwords do not match.</div>';
    } elseif(strlen($new) < 6) {
        $passwordMessage = '<div class="alert alert-danger" data-aos="fade-down">New password must be at least 6 characters long.</div>';
    } else {
        $result = $technicianController->changePassword($current, $new);
        if($result['success']) {
            $passwordMessage = '<div class="alert alert-success" data-aos="fade-down">Password updated successfully!</div>';
        } else {
            $passwordMessage = '<div class="alert alert-danger" data-aos="fade-down">' . htmlspecialchars($result['message']) . '</div>';
        }
    }
}

$profile = $technicianController->getUserProfile();
[$profilePhotoUrl, $certificationUrl, $certificationFileName, $residenceIdUrl, $residenceIdFileName] = buildMediaUrls($profile);

// Calculate profile completion based on stored data
$completionItems = 0;
$completedItems = 0;

// Required text fields
$requiredFields = [
    'first_name',
    'last_name',
    'phone',
    'address',
    'subcity',
    'woreda',
    'profession',
    'tele_birr'
];

foreach ($requiredFields as $field) {
    $completionItems++;
    if (!empty($profile[$field])) {
        $completedItems++;
    }
}

// Document fields
$documentFields = [
    'profile_photo',
    'certification_file',
    'residence_id_file'
];

foreach ($documentFields as $field) {
    $completionItems++;
    if (!empty($profile[$field])) {
        $completedItems++;
    }
}

$profileCompletion = $completionItems > 0
    ? (int)round(($completedItems / $completionItems) * 100)
    : 0;

if ($profileCompletion > 100) {
    $profileCompletion = 100;
}

$uploadedDocuments = 0;
foreach ($documentFields as $field) {
    if (!empty($profile[$field])) {
        $uploadedDocuments++;
    }
}
$totalDocuments = count($documentFields);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Homefix Pro</title>
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
            top: 10px;
            right: 10px;
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
            color: #000;
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

        .btn-secondary {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            border: none;
            padding: 12px 24px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .btn-secondary:hover {
            background: rgba(67, 97, 238, 0.2);
            transform: translateY(-2px);
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

        .form-control:disabled {
            background-color: var(--gray-light);
            color: var(--gray);
            cursor: not-allowed;
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

        .alert i {
            font-size: 18px;
        }

        /* Profile Media Section */
        .profile-media {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 35px;
        }

        .media-item {
            background: var(--light);
            border-radius: var(--radius);
            padding: 25px;
            transition: var(--transition);
            border: 1px solid var(--gray-light);
        }

        .media-item:hover {
            background: white;
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        .media-item label {
            font-weight: 600;
            display: block;
            margin-bottom: 15px;
            color: var(--dark);
            font-size: 16px;
        }

        .media-preview {
            background: white;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: var(--transition);
        }

        .media-preview:hover {
            border-color: var(--primary-light);
        }

        .media-preview img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: var(--radius);
            margin-bottom: 15px;
            box-shadow: var(--shadow);
        }

        .media-preview p {
            color: var(--gray);
            margin-bottom: 10px;
        }

        .media-preview a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .media-preview a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 20px;
            background: white;
            border: 2px dashed var(--gray);
            border-radius: var(--radius);
            color: var(--gray);
            font-weight: 500;
            transition: var(--transition);
            width: 100%;
            cursor: pointer;
        }

        .file-input-button:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(67, 97, 238, 0.05);
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .media-item small {
            display: block;
            margin-top: 10px;
            color: var(--gray);
            font-size: 13px;
            line-height: 1.4;
        }

        /* Profile Info Grid */
        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }

        .password-section {
            margin-top: 40px;
        }

        /* Password Wrapper */
        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        /* Password Strength Indicator */
        .password-strength {
            margin-top: 10px;
        }

        .strength-text {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .strength-bar {
            height: 6px;
            background: var(--gray-light);
            border-radius: 3px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            border-radius: 3px;
            transition: var(--transition);
        }

        .strength-weak {
            width: 25%;
            background: var(--danger);
        }

        .strength-medium {
            width: 50%;
            background: var(--warning);
        }

        .strength-strong {
            width: 75%;
            background: var(--success);
        }

        .strength-very-strong {
            width: 100%;
            background: #06d6a0;
        }

        /* Document Preview */
        .document-preview {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--gray-light);
            transition: var(--transition);
        }

        .document-preview:hover {
            border-color: var(--primary-light);
            transform: translateY(-2px);
        }

        .document-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            background: var(--gradient-primary);
        }

        .document-info {
            flex: 1;
        }

        .document-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .document-actions {
            display: flex;
            gap: 10px;
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
            
            .stats-container {
                grid-template-columns: 1fr;
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
            
            .profile-media, .profile-info {
                grid-template-columns: 1fr;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
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

        /* Loading Spinner */
        .spinner {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 2px solid white;
            width: 18px;
            height: 18px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 30px;
            left: 30px;
            background: white;
            border-radius: var(--radius);
            padding: 16px 20px;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 400px;
            z-index: 1000;
            transform: translateY(100px);
            opacity: 0;
            transition: var(--transition);
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast.success {
            border-left: 4px solid var(--success);
        }

        .toast.error {
            border-left: 4px solid var(--danger);
        }

        .toast-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
        }

        .toast.success .toast-icon {
            background: var(--success);
        }

        .toast.error .toast-icon {
            background: var(--danger);
        }

        .toast-message {
            flex: 1;
            font-weight: 500;
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 16px;
            transition: var(--transition);
        }

        .toast-close:hover {
            color: var(--dark);
        }

        /* Utility Classes */
        .d-none {
            display: none !important;
        }

        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 1rem; }
        .mt-4 { margin-top: 1.5rem; }
        .mt-5 { margin-top: 3rem; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .mb-5 { margin-bottom: 3rem; }
        .me-1 { margin-right: 0.25rem; }
        .me-2 { margin-right: 0.5rem; }
        .me-3 { margin-right: 1rem; }
        .me-4 { margin-right: 1.5rem; }
        .me-5 { margin-right: 3rem; }
        .ms-1 { margin-left: 0.25rem; }
        .ms-2 { margin-left: 0.5rem; }
        .ms-3 { margin-left: 1rem; }
        .ms-4 { margin-left: 1.5rem; }
        .ms-5 { margin-left: 3rem; }
        .text-muted { color: var(--gray); }
        .text-success { color: #06d6a0; }
        .text-danger { color: var(--danger); }
        .d-block { display: block; }
        .d-flex { display: flex; }
        .align-items-center { align-items: center; }
        .justify-content-center { justify-content: center; }
        .p-4 { padding: 1.5rem; }
        .m-0 { margin: 0; }
    </style>
</head>
<body class="technician-body">
    <div class="dashboard">
        <!-- Include the sidebar component -->
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <!-- Header -->
            <div class="dashboard-header">
                <div class="welcome-section">
                    <h1>My Profile</h1>
                    <p>Keep your professional details up to date</p>
                </div>
                <div class="header-actions">
                    <div class="notification-bell">
                        <i class="fa-solid fa-bell"></i>
                        <?php $unreadNotifications = $technicianController->getUnreadNotificationCount(); ?>
                        <?php if(!empty($unreadNotifications)): ?>
                            <span class="notification-badge"></span>
                        <?php endif; ?>
                    </div>

                    <a href="jobs.php" class="btn-primary">
                        <i class="fa-solid fa-briefcase"></i>
                        View Jobs
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
                        <div class="stat-value" id="completion-stat"><?php echo $profileCompletion; ?>%</div>
                        <div class="stat-label" style="color:#000;">Profile Complete</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">Verified</div>
                        <div class="stat-label" style="color:#000;">Account Status</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" style="color: #000;"><?php echo $uploadedDocuments . '/' . $totalDocuments; ?></div>
                        <div class="stat-label" style="color: #000;">Documents Uploaded</div>
                    </div>
                </div>
            </div>

            <!-- Messages Container -->
            <div id="message-container">
                <?php 
                // Display messages if they exist
                if (!empty($message)) {
                    echo $message;
                }
                ?>
            </div>

            <!-- Professional Information Card -->
            <div class="card" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header">
                    <div>
                        <h2 class="card-title"><i class="fas fa-user-tie"></i> Professional Information</h2>
                        <p class="card-subtitle">Update your professional details and documents</p>
                    </div>
                </div>
                
                <form method="POST" enctype="multipart/form-data" id="profile-form">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="profile-media">
                        <!-- Profile Photo -->
                        <div class="media-item" data-aos="fade-right" data-aos-delay="250">
                            <label><i class="fas fa-camera me-2"></i>Profile Photo</label>
                            <div class="media-preview">
                                <?php if(!empty($profile['profile_photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($profilePhotoUrl); ?>" alt="Profile photo" id="profile-preview" />
                                    <p>Current profile photo</p>
                                    <a href="<?php echo htmlspecialchars($profilePhotoUrl); ?>" target="_blank">View full size</a>
                                <?php else: ?>
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23f1f5f9'/%3E%3Ccircle cx='100' cy='80' r='50' fill='%23cbd5e0'/%3E%3Cpath d='M30,200 L170,200 L170,140 C170,110 140,110 100,110 C60,110 30,110 30,140 Z' fill='%23cbd5e0'/%3E%3C/svg%3E" alt="Default profile" id="profile-preview" />
                                    <p>No profile photo uploaded yet</p>
                                <?php endif; ?>
                            </div>
                            <div class="file-input-wrapper">
                                <div class="file-input-button">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    Choose Profile Photo
                                </div>
                                <input type="file" name="profile_photo" accept="image/*" id="profile-photo-input">
                            </div>
                            <small>JPG, PNG, GIF up to 2MB. Professional headshot recommended.</small>
                        </div>

                        <!-- Certification Document -->
                        <div class="media-item" data-aos="fade-left" data-aos-delay="250">
                            <label><i class="fas fa-certificate me-2"></i>Certification Document</label>
                            <div class="media-preview">
                                <?php if(!empty($profile['certification_file'])): ?>
                                    <div class="document-preview">
                                        <div class="document-icon">
                                            <i class="fas fa-file-certificate"></i>
                                        </div>
                                        <div class="document-info">
                                            <div class="document-name">Professional Certification</div>
                                            <div class="document-actions">
                                                <a href="<?php echo htmlspecialchars($certificationUrl); ?>" target="_blank" class="btn btn-outline">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </a>
                                                <a href="<?php echo htmlspecialchars($certificationUrl); ?>" download="<?php echo htmlspecialchars($certificationFileName); ?>" class="btn btn-outline">
                                                    <i class="fas fa-download me-1"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center p-4" style="background: var(--light); border-radius: var(--radius);">
                                        <i class="fas fa-file-upload fa-2x text-muted me-3"></i>
                                        <p class="text-muted m-0">No certification uploaded yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="file-input-wrapper">
                                <div class="file-input-button">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    Choose Certification
                                </div>
                                <input type="file" name="certification" accept=".pdf,.jpg,.jpeg,.png" id="certification-input">
                            </div>
                            <small>Upload your professional certification or license. PDF, JPG, PNG up to 5MB.</small>
                        </div>

                        <!-- Residence ID Document -->
                        <div class="media-item" data-aos="fade-up" data-aos-delay="250">
                            <label><i class="fas fa-id-card me-2"></i>Residence ID Document</label>
                            <div class="media-preview">
                                <?php if(!empty($profile['residence_id_file'])): ?>
                                    <div class="document-preview">
                                        <div class="document-icon">
                                            <i class="fas fa-file-pdf"></i>
                                        </div>
                                        <div class="document-info">
                                            <div class="document-name">Residence ID Document</div>
                                            <div class="document-actions">
                                                <a href="<?php echo htmlspecialchars($residenceIdUrl); ?>" target="_blank" class="btn btn-outline">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </a>
                                                <a href="<?php echo htmlspecialchars($residenceIdUrl); ?>" download="<?php echo htmlspecialchars($residenceIdFileName); ?>" class="btn btn-outline">
                                                    <i class="fas fa-download me-1"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center p-4" style="background: var(--light); border-radius: var(--radius);">
                                        <i class="fas fa-file-upload fa-2x text-muted me-3"></i>
                                        <p class="text-muted m-0">No residence ID uploaded yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="file-input-wrapper">
                                <div class="file-input-button">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    Choose ID Document
                                </div>
                                <input type="file" name="residence_id" accept=".pdf,.jpg,.jpeg,.png" id="residence-id-input">
                            </div>
                            <small>Upload your government-issued ID. PDF, JPG, PNG up to 5MB.</small>
                        </div>
                    </div>

                    <!-- Professional Information Form -->
                    <div class="profile-info">
                        <div class="form-group" data-aos="fade-up" data-aos-delay="300">
                            <label class="form-label"><i class="fas fa-signature me-2"></i>First Name *</label>
                            <input type="text" name="first_name" class="form-control" required 
                                   value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group" data-aos="fade-up" data-aos-delay="350">
                            <label class="form-label"><i class="fas fa-signature me-2"></i>Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required 
                                   value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group" data-aos="fade-up" data-aos-delay="400">
                            <label class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" disabled>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-info-circle me-1"></i> Email cannot be changed. Contact support if needed.
                            </small>
                        </div>
                        
                        <div class="form-group" data-aos="fade-up" data-aos-delay="450">
                            <label class="form-label"><i class="fas fa-phone me-2"></i>Phone *</label>
                            <input type="text" name="phone" class="form-control" required
                                   value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group" data-aos="fade-up" data-aos-delay="500">
                            <label class="form-label"><i class="fas fa-home me-2"></i>Address *</label>
                            <input type="text" name="address" class="form-control" required 
                                   value="<?php echo htmlspecialchars($profile['address'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group" data-aos="fade-up" data-aos-delay="550">
                            <label class="form-label"><i class="fas fa-map-marker-alt me-2"></i>Subcity *</label>
                            <input type="text" name="subcity" class="form-control" required 
                                   value="<?php echo htmlspecialchars($profile['subcity'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group" data-aos="fade-up" data-aos-delay="600">
                            <label class="form-label"><i class="fas fa-map-pin me-2"></i>Woreda *</label>
                            <input type="text" name="woreda" class="form-control" required 
                                   value="<?php echo htmlspecialchars($profile['woreda'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group" data-aos="fade-up" data-aos-delay="650">
                            <label class="form-label"><i class="fas fa-briefcase me-2"></i>Profession *</label>
                            <input type="text" name="profession" class="form-control" required 
                                   value="<?php echo htmlspecialchars($profile['profession'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group" data-aos="fade-up" data-aos-delay="700">
                            <label class="form-label"><i class="fas fa-university me-2"></i>Bank Account</label>
                            <input type="text" name="bank_account" class="form-control"
                                   value="<?php echo htmlspecialchars($profile['bank_account'] ?? ''); ?>">
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-info-circle me-1"></i> For receiving payments (optional)
                            </small>
                        </div>
                        
                        <div class="form-group" data-aos="fade-up" data-aos-delay="750">
                            <label class="form-label"><i class="fas fa-mobile-alt me-2"></i>Tele Birr *</label>
                            <input type="text" name="tele_birr" class="form-control" required 
                                   value="<?php echo htmlspecialchars($profile['tele_birr'] ?? ''); ?>">
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-info-circle me-1"></i> For receiving mobile payments
                            </small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn mt-4" id="update-profile-btn" data-aos="fade-up" data-aos-delay="800">
                        <i class="fas fa-save me-2"></i> Update Profile
                    </button>
                </form>
            </div>

            <!-- Password Change Section -->
            <div id="password-message-container">
                <?php 
                if (!empty($passwordMessage)) {
                    echo $passwordMessage;
                }
                ?>
            </div>

            <!-- Change Password Card -->
            <div class="card password-section" data-aos="fade-up" data-aos-delay="300">
                <div class="card-header">
                    <div>
                        <h2 class="card-title"><i class="fas fa-lock"></i> Change Password</h2>
                        <p class="card-subtitle">Update your password to keep your account secure</p>
                    </div>
                </div>
                
                <form method="POST" id="password-form">
                    <input type="hidden" name="change_password" value="1">

                    <div class="profile-info">
                        <div class="form-group" data-aos="fade-up" data-aos-delay="350">
                            <label class="form-label"><i class="fas fa-key me-2"></i>Current Password *</label>
                            <div class="password-wrapper">
                                <input type="password" name="current_password" class="form-control" required id="current-password">
                                <button type="button" class="password-toggle" onclick="togglePassword('current-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group" data-aos="fade-up" data-aos-delay="400">
                            <label class="form-label"><i class="fas fa-key me-2"></i>New Password *</label>
                            <div class="password-wrapper">
                                <input type="password" name="new_password" class="form-control" required id="new-password">
                                <button type="button" class="password-toggle" onclick="togglePassword('new-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength mt-3">
                                <div class="strength-text">
                                    <span>Password strength:</span>
                                    <span id="password-strength-text">None</span>
                                </div>
                                <div class="strength-bar">
                                    <div class="strength-fill" id="password-strength-bar"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" data-aos="fade-up" data-aos-delay="450">
                            <label class="form-label"><i class="fas fa-key me-2"></i>Confirm New Password *</label>
                            <div class="password-wrapper">
                                <input type="password" name="confirm_password" class="form-control" required id="confirm-password">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="password-match" class="d-none mt-2">
                                <i class="fas fa-check text-success me-1"></i>
                                <span class="text-success">Passwords match</span>
                            </div>
                            <div id="password-mismatch" class="d-none mt-2">
                                <i class="fas fa-times text-danger me-1"></i>
                                <span class="text-danger">Passwords do not match</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn mt-4" id="update-password-btn" data-aos="fade-up" data-aos-delay="500">
                        <i class="fas fa-lock me-2"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <div class="toast-icon">
            <i class="fas fa-check"></i>
        </div>
        <div class="toast-message" id="toast-message">Profile updated successfully!</div>
        <button class="toast-close" id="toast-close">
            <i class="fas fa-times"></i>
        </button>
    </div>

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

        // Image preview functionality
        document.getElementById('profile-photo-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-preview').src = e.target.result;
                    showToast('Profile photo updated!', 'success');
                }
                reader.readAsDataURL(file);
            }
        });

        // Document preview for certification
        document.getElementById('certification-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                showToast('Certification document selected!', 'success');
            }
        });

        // Document preview for residence ID
        document.getElementById('residence-id-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                showToast('Residence ID document selected!', 'success');
            }
        });

        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength indicator
        document.getElementById('new-password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            
            // Calculate password strength
            let strength = 0;
            let criteria = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                numbers: /[0-9]/.test(password),
                symbols: /[^A-Za-z0-9]/.test(password)
            };
            
            // Score calculation
            if (criteria.length) strength += 20;
            if (criteria.uppercase) strength += 20;
            if (criteria.lowercase) strength += 20;
            if (criteria.numbers) strength += 20;
            if (criteria.symbols) strength += 20;
            
            // Update UI
            strengthBar.className = 'strength-fill';
            
            if (strength === 0) {
                strengthText.textContent = 'None';
            } else if (strength < 40) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Weak';
            } else if (strength < 60) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Medium';
            } else if (strength < 80) {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Strong';
            } else {
                strengthBar.classList.add('strength-very-strong');
                strengthText.textContent = 'Very Strong';
            }
            
            // Check if passwords match
            checkPasswordMatch();
        });

        // Check password match
        function checkPasswordMatch() {
            const password = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const matchIndicator = document.getElementById('password-match');
            const mismatchIndicator = document.getElementById('password-mismatch');
            
            if (password && confirmPassword) {
                if (password === confirmPassword) {
                    matchIndicator.classList.remove('d-none');
                    mismatchIndicator.classList.add('d-none');
                } else {
                    matchIndicator.classList.add('d-none');
                    mismatchIndicator.classList.remove('d-none');
                }
            } else {
                matchIndicator.classList.add('d-none');
                mismatchIndicator.classList.add('d-none');
            }
        }

        // Check password match on confirm password input
        document.getElementById('confirm-password').addEventListener('input', checkPasswordMatch);

        // Form submission with loading state
        document.getElementById('profile-form').addEventListener('submit', function() {
            const btn = document.getElementById('update-profile-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<div class="spinner me-2"></div> Updating...';
            btn.disabled = true;
        });

        document.getElementById('password-form').addEventListener('submit', function() {
            const btn = document.getElementById('update-password-btn');
            if(btn.dataset.loading === 'true') {
                return;
            }
            btn.dataset.loading = 'true';
            const originalText = btn.innerHTML;
            btn.innerHTML = '<div class="spinner me-2"></div> Updating...';
            btn.disabled = true;
            // Let the form continue submitting to the backend
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.dataset.loading = 'false';
            }, 2000);
        });

        // Toast notification function
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            const toastIcon = toast.querySelector('.toast-icon i');
            
            toastMessage.textContent = message;
            toast.className = 'toast ' + type;
            toastIcon.className = type === 'success' ? 'fas fa-check' : 'fas fa-exclamation-triangle';
            
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 5000);
        }

        // Close toast
        document.getElementById('toast-close').addEventListener('click', function() {
            document.getElementById('toast').classList.remove('show');
        });

        // Initialize helpers on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize password strength indicator
            checkPasswordMatch();
            
            // Add fade-in animation for existing alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach((alert, index) => {
                alert.style.animation = `fadeIn 0.5s ease ${index * 0.1}s forwards`;
            });
        });

        // Add fadeIn animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>