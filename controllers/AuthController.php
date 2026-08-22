<?php
require_once 'models/User.php';
require_once 'includes/Mailer.php';

class AuthController {
    private $user;
    
    public function __construct($db) {
        $this->user = new User($db);
    }
    
    // Handle residence ID upload
    private function uploadResidenceID($file) {
        $target_dir = "assets/uploads/residence_ids/";
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . '_residence_' . basename($file["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file type
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        if(!in_array($file_type, $allowed_types)) {
            return ['success' => false, 'message' => 'Only PDF, JPG, JPEG, PNG files are allowed for residence ID.'];
        }

        // Check file size (5MB max)
        if($file["size"] > 5000000) {
            return ['success' => false, 'message' => 'Residence ID file is too large. Maximum size is 5MB.'];
        }

        if(move_uploaded_file($file["tmp_name"], $target_file)) {
            return ['success' => true, 'file_name' => $file_name];
        }

        return ['success' => false, 'message' => 'Error uploading residence ID.'];
    }

    public function register($data, $files) {
        // Basic fields
        $this->user->first_name = $data['first_name'];
        $this->user->last_name = $data['last_name'];
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        $this->user->phone = $data['phone'];
        $this->user->address = $data['address'];
        $this->user->subcity = $data['subcity'];
        $this->user->woreda = $data['woreda'];
        $this->user->role = $data['role'];
        $this->user->profession = $data['profession'] ?? '';
        // Optional payment details (primarily for technicians)
        $this->user->bank_account = $data['bank_account'] ?? '';
        $this->user->tele_birr = $data['tele_birr'] ?? '';

        // Handle profile photo upload (required)
        if(isset($files['profile_photo']) && $files['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadProfilePhoto($files['profile_photo']);
            if($uploadResult['success']) {
                $this->user->profile_photo = $uploadResult['file_name'];
            } else {
                return ['success' => false, 'message' => $uploadResult['message']];
            }
        } else {
            return ['success' => false, 'message' => 'Profile photo is required.'];
        }

        // Handle residence ID upload (required)
        if(isset($files['residence_id']) && $files['residence_id']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadResidenceID($files['residence_id']);
            if($uploadResult['success']) {
                $this->user->residence_id_file = $uploadResult['file_name'];
            } else {
                return ['success' => false, 'message' => $uploadResult['message']];
            }
        } else {
            return ['success' => false, 'message' => 'Residence ID is required.'];
        }

        // Handle certification upload for technicians (optional)
        if($this->user->role === 'technician' && isset($files['certification']) && $files['certification']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadCertification($files['certification']);
            if($uploadResult['success']) {
                $this->user->certification_file = $uploadResult['file_name'];
            }
        }

        if($this->user->emailExists()) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        if($this->user->register()) {
            // Send welcome email to the newly registered user
            $firstName = $this->user->first_name;
            $subject = 'Welcome to HomeFix Pro';
            $body = '<p>Hello ' . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ', welcome to HomeFix Pro. Your account has been successfully registered.</p>';

            // Use the shared application mail helper; ignore failures so registration is not blocked
            @sendAppMail($this->user->email, $subject, $body);

            return [
                'success' => true,
                'message' => 'Registration successful! You can now log in.',
                'role' => $this->user->role
            ];
        }

        return ['success' => false, 'message' => 'Registration failed.', 'role' => $this->user->role];
    }
    
    public function login($email, $password) {
        $this->user->email = $email;
        $this->user->password = $password;
        
        $loginResult = $this->user->login();
        
        if($loginResult['success']) {
            // Check if technician is approved
            if($this->user->role == 'technician' && $this->user->status != 'approved') {
                return [
                    'success' => false, 
                    'message' => 'Your technician account is pending approval. Please wait for admin approval.'
                ];
            }
            
            $_SESSION['user_id'] = $this->user->id;
            $_SESSION['user_name'] = $this->user->first_name . ' ' . $this->user->last_name;
            $_SESSION['user_role'] = $this->user->role;
            $_SESSION['user_status'] = $this->user->status;
            $_SESSION['profile_photo'] = $this->user->profile_photo;
            // In the login method of AuthController.php, add this line:
$_SESSION['user_profession'] = $this->user->profession;
// In AuthController.php login method, add these lines:
$_SESSION['user_address'] = $this->user->address;
$_SESSION['user_subcity'] = $this->user->subcity;
$_SESSION['user_woreda'] = $this->user->woreda;
            return [
                'success' => true, 
                'role' => $this->user->role, 
                'status' => $this->user->status
            ];
        }
        
        return ['success' => false, 'message' => $loginResult['message']];
    }
    
    private function uploadProfilePhoto($file) {
        $target_dir = "assets/uploads/profiles/";
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_profile_' . basename($file["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if(!in_array($file_type, $allowed_types)) {
            return ['success' => false, 'message' => 'Only JPG, JPEG, PNG, GIF files are allowed for profile photos.'];
        }
        
        // Check file size (2MB max)
        if($file["size"] > 2000000) {
            return ['success' => false, 'message' => 'Profile photo is too large. Maximum size is 2MB.'];
        }
        
        if(move_uploaded_file($file["tmp_name"], $target_file)) {
            return ['success' => true, 'file_name' => $file_name];
        }
        
        return ['success' => false, 'message' => 'Error uploading profile photo.'];
    }
    
    private function uploadCertification($file) {
        $target_dir = "assets/uploads/certifications/";
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_cert_' . basename($file["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check file type
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        if(!in_array($file_type, $allowed_types)) {
            return ['success' => false, 'message' => 'Only PDF, JPG, JPEG, PNG files are allowed for certifications.'];
        }
        
        // Check file size (5MB max)
        if($file["size"] > 5000000) {
            return ['success' => false, 'message' => 'File is too large. Maximum size is 5MB.'];
        }
        
        if(move_uploaded_file($file["tmp_name"], $target_file)) {
            return ['success' => true, 'file_name' => $file_name];
        }
        
        return ['success' => false, 'message' => 'Error uploading file.'];
    }
}
?>