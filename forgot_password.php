<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

session_start();
require_once 'includes/config.php';
require_once 'models/Database.php';

$pageTitle = "Forgot Password - HomeFix Pro | Request Password Reset";
$pageDescription = "Request a password reset for your HomeFix Pro account by submitting your details for admin verification.";
$pageKeywords = "forgot password, reset password, account recovery, HomeFix Pro";

$message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hasError = false;

    if($name === '' || $email === ''){
        $message = '<div class="alert alert-danger">Please fill in all required fields.</div>';
        $hasError = true;
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = '<div class="alert alert-danger">Please enter a valid email address.</div>';
        $hasError = true;
    }

    // Required: National ID image
    $nationalIdPath = null;
    $nationalIdName = null;

    if(!isset($_FILES['national_id']) || $_FILES['national_id']['error'] === UPLOAD_ERR_NO_FILE){
        $message = '<div class="alert alert-danger">Please upload a photo of your National ID.</div>';
        $hasError = true;
    } elseif($_FILES['national_id']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        $type = mime_content_type($_FILES['national_id']['tmp_name']);
        $size = $_FILES['national_id']['size'];

        if(!in_array($type, $allowed)){
            $message = '<div class="alert alert-danger">National ID must be a JPG, PNG, or GIF image.</div>';
            $hasError = true;
        } elseif($size > $maxSize){
            $message = '<div class="alert alert-danger">National ID image must be smaller than 2MB.</div>';
            $hasError = true;
        } else {
            $uploadDir = __DIR__ . '/uploads/forgot_password';
            if(!is_dir($uploadDir)){
                mkdir($uploadDir, 0777, true);
            }
            $extension = pathinfo($_FILES['national_id']['name'], PATHINFO_EXTENSION);
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($_FILES['national_id']['name'], PATHINFO_FILENAME));
            $nationalIdName = $safeName . '_nid_' . time() . '.' . $extension;
            $destination = $uploadDir . '/' . $nationalIdName;

            if(move_uploaded_file($_FILES['national_id']['tmp_name'], $destination)){
                $nationalIdPath = $destination;
            } else {
                $message = '<div class="alert alert-danger">Failed to upload National ID image. Please try again.</div>';
                $hasError = true;
            }
        }
    } else {
        $message = '<div class="alert alert-danger">Error uploading National ID image. Please try again.</div>';
        $hasError = true;
    }

    // Optional: profile photo
    $photoPath = null;
    $photoName = null;

    if(isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE){
        if($_FILES['profile_photo']['error'] === UPLOAD_ERR_OK){
            $allowed = ['image/jpeg','image/png','image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            $type = mime_content_type($_FILES['profile_photo']['tmp_name']);
            $size = $_FILES['profile_photo']['size'];

            if(!in_array($type, $allowed)){
                $message = '<div class="alert alert-danger">Profile photo must be a JPG, PNG, or GIF image.</div>';
                $hasError = true;
            } elseif($size > $maxSize){
                $message = '<div class="alert alert-danger">Profile photo must be smaller than 2MB.</div>';
                $hasError = true;
            } else {
                $uploadDir = __DIR__ . '/uploads/forgot_password';
                if(!is_dir($uploadDir)){
                    mkdir($uploadDir, 0777, true);
                }
                $extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($_FILES['profile_photo']['name'], PATHINFO_FILENAME));
                $photoName = $safeName . '_' . time() . '.' . $extension;
                $destination = $uploadDir . '/' . $photoName;

                if(move_uploaded_file($_FILES['profile_photo']['tmp_name'], $destination)){
                    $photoPath = $destination;
                } else {
                    $message = '<div class="alert alert-danger">Failed to upload profile photo. Please try again.</div>';
                    $hasError = true;
                }
            }
        } else {
            $message = '<div class="alert alert-danger">Error uploading profile photo. Please try again.</div>';
            $hasError = true;
        }
    }

    if(!$hasError){
        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'amanuelaman63@gmail.com';
            $mail->Password = 'aipbdmzunoznaqjw';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('amanuelaman63@gmail.com', 'Password Reset Request');
            $mail->addAddress('amanuelaman63@gmail.com');
            $mail->addReplyTo($email, $name);

            // Attach required National ID image
            if($nationalIdPath && file_exists($nationalIdPath)){
                $mail->addAttachment($nationalIdPath, $nationalIdName ?: 'national_id');
            }

            // Attach optional profile photo
            if($photoPath && file_exists($photoPath)){
                $mail->addAttachment($photoPath, $photoName ?: 'profile_photo');
            }

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request from ' . $name;
            $body  = '<h3>Password Reset Request</h3>';
            $body .= '<p><strong>Name:</strong> ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</p>';
            $body .= '<p><strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>';
            $body .= '<p>The user has uploaded their National ID image and (optionally) a profile photo as attachments.</p>';
            $body .= '<p>Please verify their identity and handle the reset manually.</p>';
            $mail->Body = $body;

            $mail->send();

            $message = '<div class="alert alert-success">Your request has been sent to the administrator. You will be contacted soon.</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Your request could not be sent. Please try again later.</div>';
        }
    }
}
?>

<style>
/* Basic reuse of premium styles for consistency */
:root {
    --primary: #1a365d;
    --primary-dark: #0f2547;
    --primary-light: #2d4a7a;
    --secondary: #2b6cb0;
    --accent: #3182ce;
    --accent-light: #63b3ed;
    --success: #38a169;
    --danger: #e53e3e;
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

.premium-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -2;
    overflow: hidden;
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
    background-image: radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 2px, transparent 0), radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.05) 1px, transparent 0);
    background-size: 50px 50px, 30px 30px;
}

.bg-image-parallax {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 120%;
    background-image: url('https://images.unsplash.com/photo-1581578731548-c64695cc6952?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    filter: brightness(0.3);
    transform: translateZ(0);
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
}

.floating-element.el-1 { top: 20%; left: 10%; }
.floating-element.el-2 { top: 60%; left: 85%; }
.floating-element.el-3 { top: 80%; left: 15%; }
.floating-element.el-4 { top: 40%; left: 80%; }

.reset-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
}

.reset-form {
    width: 100%;
    max-width: 500px;
    padding: 3rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border: 1px solid var(--gray-200);
}

.reset-form h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
    text-align: center;
}

.reset-form > p {
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

.btn-primary {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%);
    color: white;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
}

.btn-primary:hover {
    transform: translateY(-2px);
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
</style>

<div class="premium-background">
    <div class="bg-gradient-overlay"></div>
    <div class="bg-pattern"></div>
    <div class="bg-image-parallax"></div>
    <div class="floating-elements">
        <div class="floating-element el-1"><i class="fas fa-tools"></i></div>
        <div class="floating-element el-2"><i class="fas fa-user-shield"></i></div>
        <div class="floating-element el-3"><i class="fas fa-key"></i></div>
        <div class="floating-element el-4"><i class="fas fa-lock"></i></div>
    </div>
</div>

<div class="reset-container">
    <div class="reset-form">
        <h2>Forgot Password</h2>
        <p>Fill in the form below and our admin will contact you to reset your password.</p>
        <?php echo $message; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address (same as your registered account)</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter the email you used to register" required>
                <small style="display:block;margin-top:0.25rem;color:var(--gray-600);font-size:0.8rem;">
                    Please use the exact email address you used when creating your HomeFix Pro account.
                </small>
            </div>
            <div class="form-group">
                <label for="national_id">National ID (image, JPG/PNG/GIF, max 2MB)</label>
                <input type="file" id="national_id" name="national_id" class="form-control" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="profile_photo">Profile Photo (JPG, PNG, GIF, max 2MB)</label>
                <input type="file" id="profile_photo" name="profile_photo" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn-primary">Submit Request</button>
            <div class="form-group" style="margin-top: 1rem; text-align: center;">
                <a href="login.php" style="font-size: 0.9rem; color: var(--primary); text-decoration: none;">&larr; Back to Login</a>
            </div>
        </form>
    </div>
</div>
