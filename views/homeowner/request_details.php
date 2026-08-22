<?php
session_start();
require_once '../../includes/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'homeowner') {
    die('Unauthorized access');
}

if(!isset($_GET['id'])) {
    die('No request ID provided');
}

require_once '../../controllers/HomeownerController.php';

$conn = getDBConnection();
$homeownerController = new HomeownerController($conn);
$request = $homeownerController->getServiceRequestDetails($_GET['id']);

if(!$request) {
    die('Request not found or you do not have permission to view this request.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details - HomeFix Pro</title>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        body {
            padding: 20px;
            background: white;
        }
        .detail-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .detail-section:last-child {
            border-bottom: none;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .detail-item {
            margin-bottom: 15px;
        }
        .detail-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="request-details">
        <h2 style="margin-bottom: 20px; color: #2c3e50;">Service Request Details</h2>
        
        <div class="detail-section">
            <h3 style="color: #3498db; margin-bottom: 15px;">Basic Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Title</div>
                    <div><?php echo htmlspecialchars($request['title']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Service Type</div>
                    <div><?php echo htmlspecialchars($request['service_type']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div>
                        <span class="status status-<?php echo $request['status']; ?>">
                            <?php 
                            $statusText = $request['status'];
                            if($statusText == 'waiting_acceptance') {
                                echo 'Waiting Acceptance';
                            } else {
                                echo ucfirst($statusText);
                            }
                            ?>
                        </span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Created Date</div>
                    <div><?php echo date('F j, Y g:i A', strtotime($request['created_at'])); ?></div>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h3 style="color: #3498db; margin-bottom: 15px;">Service Description</h3>
            <div class="detail-item">
                <div class="detail-label">Description</div>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <?php echo nl2br(htmlspecialchars($request['description'])); ?>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h3 style="color: #3498db; margin-bottom: 15px;">Location Details</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Full Address</div>
                    <div><?php echo htmlspecialchars($request['address']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Subcity</div>
                    <div><?php echo htmlspecialchars($request['subcity']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Woreda</div>
                    <div><?php echo htmlspecialchars($request['woreda']); ?></div>
                </div>
            </div>
        </div>

        <?php if($request['preferred_date'] || $request['preferred_time']): ?>
        <div class="detail-section">
            <h3 style="color: #3498db; margin-bottom: 15px;">Preferred Schedule</h3>
            <div class="detail-grid">
                <?php if($request['preferred_date']): ?>
                <div class="detail-item">
                    <div class="detail-label">Preferred Date</div>
                    <div><?php echo date('F j, Y', strtotime($request['preferred_date'])); ?></div>
                </div>
                <?php endif; ?>
                <?php if($request['preferred_time']): ?>
                <div class="detail-item">
                    <div class="detail-label">Preferred Time</div>
                    <div><?php echo date('g:i A', strtotime($request['preferred_time'])); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($request['first_name']): ?>
        <div class="detail-section">
            <h3 style="color: #3498db; margin-bottom: 15px;">Assigned Technician</h3>
            <div style="background: #e8f4fd; padding: 20px; border-radius: 5px;">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Name</div>
                        <div><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Profession</div>
                        <div><?php echo htmlspecialchars($request['profession']); ?></div>
                    </div>
                    <?php if($request['technician_phone']): ?>
                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div><?php echo htmlspecialchars($request['technician_phone']); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($request['technician_email']): ?>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div><?php echo htmlspecialchars($request['technician_email']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($request['budget']): ?>
        <div class="detail-section">
            <h3 style="color: #3498db; margin-bottom: 15px;">Budget & Cost</h3>
            <div class="detail-item">
                <div class="detail-label">Estimated Budget</div>
                <div style="font-size: 18px; font-weight: bold; color: #27ae60;">
                    ETB <?php echo number_format($request['budget'], 2); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($request['inspection_submitted_at']): ?>
        <div class="detail-section">
            <h3 style="color: #3498db; margin-bottom: 15px;">Inspection Result & Pricing</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Inspection Findings</div>
                    <div><?php echo nl2br(htmlspecialchars($request['inspection_findings'] ?? $request['inspection_notes'])); ?></div>
                </div>
                <?php if(!empty($request['inspection_recommendations'])): ?>
                <div class="detail-item">
                    <div class="detail-label">Recommended Work</div>
                    <div><?php echo nl2br(htmlspecialchars($request['inspection_recommendations'])); ?></div>
                </div>
                <?php endif; ?>
                <?php if(!empty($request['materials_cost'])): ?>
                <div class="detail-item">
                    <div class="detail-label">Materials Cost</div>
                    <div>ETB <?php echo number_format($request['materials_cost'], 2); ?></div>
                </div>
                <?php endif; ?>
                <?php if(!empty($request['labor_cost'])): ?>
                <div class="detail-item">
                    <div class="detail-label">Labor Cost</div>
                    <div>ETB <?php echo number_format($request['labor_cost'], 2); ?></div>
                </div>
                <?php endif; ?>
                <div class="detail-item">
                    <div class="detail-label">Proposed Total Price</div>
                    <div style="font-size: 20px; font-weight: 600; color:#c0392b;">ETB <?php echo number_format($request['estimated_cost'], 2); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div><span class="status status-<?php echo $request['status']; ?>"><?php echo str_replace('_',' ', $request['status']); ?></span></div>
                </div>
            </div>
            <small>Inspection submitted on <?php echo date('M j, Y g:i A', strtotime($request['inspection_submitted_at'])); ?></small>
        </div>
        <?php endif; ?>

        <?php if($request['status'] === 'rejected' && !empty($request['price_rejection_reason'])): ?>
        <div class="detail-section">
            <h3 style="color: #e74c3c; margin-bottom: 15px;">Technician Rejection Reason</h3>
            <div class="detail-item">
                <div class="detail-label">Reason provided by technician</div>
                <div style="background: #fef2f2; padding: 15px; border-radius: 5px; border: 1px solid #fecaca;">
                    <?php echo nl2br(htmlspecialchars($request['price_rejection_reason'])); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 30px; text-align: center;">
            <button onclick="window.close()" class="btn">Close Window</button>
            <?php if($request['first_name']): ?>
            <a href="messages.php" class="btn btn-success" style="margin-left: 10px;">Message Technician</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>