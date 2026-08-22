<?php
session_start();
// Correct paths - going up two levels from views/admin/ to reach project root
require_once '../../includes/config.php';
require_once '../../models/Database.php';
require_once '../../controllers/UserController.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    die('Unauthorized access');
}

$db = new Database();
$userController = new UserController($db->getConnection());

if(isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $user = $userController->getUserById($user_id);
    
    if($user) {
        ?>
        <div class="user-details">
            <div class="detail-section">
                <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label><i class="fas fa-user"></i> First Name:</label>
                        <span><?php echo htmlspecialchars($user['first_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label><i class="fas fa-user"></i> Last Name:</label>
                        <span><?php echo htmlspecialchars($user['last_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label><i class="fas fa-envelope"></i> Email:</label>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label><i class="fas fa-phone"></i> Phone:</label>
                        <span><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label><i class="fas fa-user-tag"></i> Role:</label>
                        <span class="role-badge role-<?php echo $user['role']; ?>">
                            <i class="fas fa-<?php echo $user['role'] == 'admin' ? 'crown' : ($user['role'] == 'technician' ? 'tools' : 'home'); ?>"></i>
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label><i class="fas fa-info-circle"></i> Status:</label>
                        <span class="status-badge status-<?php echo $user['status']; ?>">
                            <i class="fas fa-<?php echo $user['status'] == 'approved' ? 'check' : ($user['status'] == 'rejected' ? 'times' : 'clock'); ?>"></i>
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label><i class="fas fa-calendar"></i> Registered:</label>
                        <span><?php echo date('F j, Y g:i A', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3><i class="fas fa-map-marker-alt"></i> Address Information</h3>
                <div class="detail-grid">
                    <div class="detail-item full-width">
                        <label><i class="fas fa-home"></i> Full Address:</label>
                        <span><?php echo htmlspecialchars($user['address'] ?: 'Not provided'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label><i class="fas fa-city"></i> Subcity:</label>
                        <span><?php echo htmlspecialchars($user['subcity'] ?: 'Not provided'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label><i class="fas fa-map-pin"></i> Woreda:</label>
                        <span><?php echo htmlspecialchars($user['woreda'] ?: 'Not provided'); ?></span>
                    </div>
                </div>
            </div>

            <?php if($user['role'] == 'technician'): ?>
            <div class="detail-section">
                <h3><i class="fas fa-briefcase"></i> Professional Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label><i class="fas fa-tools"></i> Profession:</label>
                        <span class="profession"><?php echo htmlspecialchars($user['profession'] ?: 'Not specified'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label><i class="fas fa-clock"></i> Account Status:</label>
                        <span class="status-badge status-<?php echo $user['status']; ?>">
                            <?php 
                            $statusText = '';
                            switch($user['status']) {
                                case 'pending':
                                    $statusText = 'Awaiting Approval';
                                    break;
                                case 'approved':
                                    $statusText = 'Active Technician';
                                    break;
                                case 'rejected':
                                    $statusText = 'Application Rejected';
                                    break;
                            }
                            echo $statusText;
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="detail-section">
                <h3><i class="fas fa-file-alt"></i> Documents & Files</h3>
                <div class="document-grid">
                    <?php 
                    $profilePhotoPath = '../../assets/uploads/profiles/' . $user['profile_photo'];
                    if($user['profile_photo'] && file_exists($profilePhotoPath)): 
                    ?>
                    <div class="document-item">
                        <label><i class="fas fa-image"></i> Profile Photo:</label>
                        <div class="document-actions">
                            <a href="<?php echo $profilePhotoPath; ?>" 
                               target="_blank" class="document-link">
                                <i class="fas fa-eye"></i> View Photo
                            </a>
                            <span class="file-size"><?php echo round(filesize($profilePhotoPath) / 1024, 2); ?> KB</span>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="document-item">
                        <label><i class="fas fa-image"></i> Profile Photo:</label>
                        <span class="no-document">No profile photo uploaded</span>
                    </div>
                    <?php endif; ?>

                    <?php 
                    $certificationPath = '../../assets/uploads/certifications/' . $user['certification_file'];
                    if($user['role'] == 'technician' && $user['certification_file'] && file_exists($certificationPath)): 
                    ?>
                    <div class="document-item">
                        <label><i class="fas fa-file-certificate"></i> Certification:</label>
                        <div class="document-actions">
                            <a href="<?php echo $certificationPath; ?>" 
                               target="_blank" class="document-link">
                                <i class="fas fa-download"></i> View Certification
                            </a>
                            <span class="file-size"><?php echo round(filesize($certificationPath) / 1024, 2); ?> KB</span>
                        </div>
                    </div>
                    <?php elseif($user['role'] == 'technician'): ?>
                    <div class="document-item">
                        <label><i class="fas fa-file-certificate"></i> Certification:</label>
                        <span class="no-document">No certification uploaded</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="detail-actions">
                <?php if($user['role'] == 'technician'): ?>
                <form method="POST" action="dashboard.php" class="inline-form status-form">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <div class="form-group">
                        <label>Update Status:</label>
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="pending" <?php echo $user['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $user['status'] == 'approved' ? 'selected' : ''; ?>>Approve</option>
                            <option value="rejected" <?php echo $user['status'] == 'rejected' ? 'selected' : ''; ?>>Reject</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_status">
                </form>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <button type="button" class="btn btn-secondary" onclick="printUserDetails()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <form method="POST" action="dashboard.php" class="inline-form" 
                          onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="delete_user" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <style>
        .user-details {
            font-size: 14px;
            color: #333;
        }

        .detail-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-section:last-child {
            border-bottom: none;
        }

        .detail-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #4361ee;
        }

        .detail-item.full-width {
            grid-column: 1 / -1;
        }

        .detail-item label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-item span {
            color: #495057;
            font-size: 1rem;
        }

        .document-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }

        .document-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .document-item label {
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .document-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .document-link {
            color: #4361ee;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            padding: 8px 16px;
            border: 1px solid #4361ee;
            border-radius: 6px;
            transition: all 0.3s ease;
            background: white;
        }

        .document-link:hover {
            background: #4361ee;
            color: white;
            text-decoration: none;
        }

        .no-document {
            color: #6c757d;
            font-style: italic;
        }

        .file-size {
            color: #6c757d;
            font-size: 0.8rem;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .detail-actions {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            gap: 20px;
        }

        .status-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .status-form .form-group {
            margin: 0;
        }

        .status-form label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .role-badge, .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .role-admin { background: #4361ee; color: white; }
        .role-technician { background: #e74c3c; color: white; }
        .role-homeowner { background: #27ae60; color: white; }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d1edff; color: #0c5460; }
        .status-rejected { background: #f8d7da; color: #721c24; }

        .profession {
            background: #4361ee;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            .document-grid {
                grid-template-columns: 1fr;
            }
            .detail-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .action-buttons {
                justify-content: space-between;
            }
        }

        @media print {
            .detail-actions {
                display: none;
            }
            .document-link {
                border: none;
                padding: 0;
            }
        }
        </style>

        <script>
        function printUserDetails() {
            window.print();
        }
        </script>
        <?php
    } else {
        echo '<div class="error alert alert-danger"><i class="fas fa-exclamation-triangle"></i> User not found.</div>';
    }
} else {
    echo '<div class="error alert alert-danger"><i class="fas fa-exclamation-triangle"></i> User ID not provided.</div>';
}
?>