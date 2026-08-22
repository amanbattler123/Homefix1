<?php
session_start();
require_once '../../includes/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'technician') {
    header('Location: ../../login.php');
    exit();
}

$requestId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if($requestId <= 0) {
    die('No request ID provided');
}

require_once '../../controllers/TechnicianController.php';

$conn = getDBConnection();
$technicianController = new TechnicianController($conn);
$request = $technicianController->getRequestForTechnician($requestId);

if(!$request) {
    die('Request not found or you do not have permission to view this request.');
}

$backTarget = 'my_tasks.php';
if(isset($_GET['from']) && $_GET['from'] === 'pending') {
    $backTarget = 'pending_tasks.php';
}

function formatDate(?string $value, string $format = 'M j, Y g:i A'): string {
    return $value ? date($format, strtotime($value)) : '—';
}

$statusLabels = [
    'waiting_acceptance' => 'Waiting Acceptance',
    'waiting_inspection' => 'Waiting Inspection',
];
$statusDisplay = $statusLabels[$request['status']] ?? ucwords(str_replace('_', ' ', $request['status']));
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
            background: #f5f7fb;
            padding: 30px;
            font-family: 'Poppins', sans-serif;
        }
        .request-wrapper {
            max-width: 960px;
            margin: 0 auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.1);
            padding: 35px;
        }
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 30px;
        }
        .request-title {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .status-chip {
            display: inline-flex;
            align-items: center;
            padding: 6px 16px;
            border-radius: 999px;
            font-weight: 600;
            background: #eef2ff;
            color: #4338ca;
            text-transform: capitalize;
        }
        .sections {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        .section {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
        }
        .section h3 {
            margin: 0 0 12px;
            font-size: 18px;
            color: #1e293b;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px 20px;
        }
        .item-label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .item-value {
            font-size: 16px;
            color: #0f172a;
            font-weight: 600;
        }
        .description-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 18px;
            border: 1px solid #e2e8f0;
            white-space: pre-wrap;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }
        .btn-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.25);
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #0f172a;
        }
        @media (max-width: 640px) {
            .request-wrapper {
                padding: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="request-wrapper">
        <div class="request-header">
            <div>
                <p class="item-label" style="margin:0;">Service Request</p>
                <h1 class="request-title"><?php echo htmlspecialchars($request['title']); ?></h1>
            </div>
            <span class="status-chip"><?php echo htmlspecialchars($statusDisplay); ?></span>
        </div>

        <div class="sections">
            <div class="section">
                <h3>Job Overview</h3>
                <div class="grid">
                    <div>
                        <div class="item-label">Service Type</div>
                        <div class="item-value"><?php echo htmlspecialchars($request['service_type']); ?></div>
                    </div>
                    <div>
                        <div class="item-label">Created</div>
                        <div class="item-value"><?php echo formatDate($request['created_at']); ?></div>
                    </div>
                    <div>
                        <div class="item-label">Last Updated</div>
                        <div class="item-value"><?php echo formatDate($request['updated_at']); ?></div>
                    </div>
                    <?php if(!empty($request['preferred_date'])): ?>
                    <div>
                        <div class="item-label">Preferred Date</div>
                        <div class="item-value"><?php echo date('M j, Y', strtotime($request['preferred_date'])); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($request['preferred_time'])): ?>
                    <div>
                        <div class="item-label">Preferred Time</div>
                        <div class="item-value"><?php echo date('g:i A', strtotime($request['preferred_time'])); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="section">
                <h3>Homeowner Contact</h3>
                <div class="grid">
                    <div>
                        <div class="item-label">Name</div>
                        <div class="item-value"><?php echo htmlspecialchars($request['homeowner_first_name'] . ' ' . $request['homeowner_last_name']); ?></div>
                    </div>
                    <div>
                        <div class="item-label">Email</div>
                        <div class="item-value"><?php echo htmlspecialchars($request['homeowner_email'] ?? '—'); ?></div>
                    </div>
                    <div>
                        <div class="item-label">Phone</div>
                        <div class="item-value"><?php echo htmlspecialchars($request['homeowner_phone'] ?? 'Not provided'); ?></div>
                    </div>
                    <div>
                        <div class="item-label">Location</div>
                        <div class="item-value"><?php echo htmlspecialchars(($request['homeowner_subcity'] ?? '—') . ', ' . ($request['homeowner_woreda'] ?? '')); ?></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h3>Address & Description</h3>
                <div class="grid" style="margin-bottom:12px;">
                    <div>
                        <div class="item-label">Full Address</div>
                        <div class="item-value" style="font-weight:500;"> <?php echo htmlspecialchars($request['address']); ?> </div>
                    </div>
                    <?php if(!empty($request['subcity'])): ?>
                    <div>
                        <div class="item-label">Subcity</div>
                        <div class="item-value"><?php echo htmlspecialchars($request['subcity']); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($request['woreda'])): ?>
                    <div>
                        <div class="item-label">Woreda</div>
                        <div class="item-value"><?php echo htmlspecialchars($request['woreda']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="item-label">Job Description</div>
                    <div class="description-box"><?php echo nl2br(htmlspecialchars($request['description'])); ?></div>
                </div>
            </div>

            <?php if(!empty($request['budget']) || !empty($request['estimated_cost'])): ?>
            <div class="section">
                <h3>Budget & Pricing</h3>
                <div class="grid">
                    <?php if(!empty($request['budget'])): ?>
                    <div>
                        <div class="item-label">Homeowner Budget</div>
                        <div class="item-value">ETB <?php echo number_format($request['budget'], 2); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($request['materials_cost'])): ?>
                    <div>
                        <div class="item-label">Materials Cost</div>
                        <div class="item-value">ETB <?php echo number_format($request['materials_cost'], 2); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($request['labor_cost'])): ?>
                    <div>
                        <div class="item-label">Labor Cost</div>
                        <div class="item-value">ETB <?php echo number_format($request['labor_cost'], 2); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($request['estimated_cost'])): ?>
                    <div>
                        <div class="item-label">Proposed Total</div>
                        <div class="item-value" style="color:#dc2626;">ETB <?php echo number_format($request['estimated_cost'], 2); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if(!empty($request['inspection_submitted_at'])): ?>
                    <small>Inspection submitted on <?php echo formatDate($request['inspection_submitted_at']); ?></small>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($request['inspection_findings']) || !empty($request['inspection_notes'])): ?>
            <div class="section">
                <h3>Inspection Summary</h3>
                <div class="grid">
                    <?php if(!empty($request['inspection_findings'])): ?>
                    <div>
                        <div class="item-label">Findings</div>
                        <div class="description-box"><?php echo nl2br(htmlspecialchars($request['inspection_findings'])); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($request['inspection_recommendations'])): ?>
                    <div>
                        <div class="item-label">Recommendations</div>
                        <div class="description-box"><?php echo nl2br(htmlspecialchars($request['inspection_recommendations'])); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($request['inspection_notes'])): ?>
                    <div>
                        <div class="item-label">Notes for Homeowner</div>
                        <div class="description-box"><?php echo nl2br(htmlspecialchars($request['inspection_notes'])); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="actions">
            <a href="<?php echo $backTarget; ?>" class="btn-link btn-secondary">&larr; Back to Tasks</a>
            <a href="messages.php" class="btn-link btn-primary"><i class="fas fa-comment"></i> Message Homeowner</a>
        </div>
    </div>
</body>
</html>
