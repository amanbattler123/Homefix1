<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../controllers/UserController.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$userController = new UserController($conn);

$summary = $userController->getTechniciansPaymentSummary();
$technicians = $summary['technicians'];
$totalGain = $summary['overall_total'];
$ownerShare = $totalGain * 0.30;
$technicianShare = $totalGain - $ownerShare;
$detailedPayments = $userController->getAllTechnicianPayments();

$paymentsByTech = [];
foreach ($detailedPayments as $payment) {
    $techId = $payment['technician_id'];
    $paymentsByTech[$techId][] = [
        'service_title' => $payment['service_title'],
        'amount' => (float)$payment['amount'],
        'payment_status' => $payment['payment_status'],
        'payment_method' => $payment['payment_method'],
        'paid_at' => $payment['paid_at'],
        'verified_at' => $payment['verified_at'],
        'created_at' => $payment['created_at'],
        'homeowner_name' => $payment['homeowner_name']
    ];
}
$paymentsByTechJson = json_encode($paymentsByTech, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

$chartLabels = array_map(function($tech) {
    return $tech['first_name'] . ' ' . $tech['last_name'];
}, $technicians);

$chartData = array_map(function($tech) {
    return (float)$tech['total_received'];
}, $technicians);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technicians Gain - HomeFix Pro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #3a0ca3;
            --success: #22c55e;
            --warning: #f97316;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --border-radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #f5f7ff 0%, #f0f4ff 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        .page {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px 40px;
        }

        .header {
            margin-bottom: 32px;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .subtitle {
            color: var(--gray-500);
            font-size: 16px;
            margin-bottom: 35px;
        }

        .action-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .search-wrapper {
            flex: 1 1 260px;
            position: relative;
        }

        .search-wrapper input {
            width: 100%;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            padding: 12px 46px;
            font-size: 15px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .search-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }

        .search-wrapper input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 10px 25px -5px rgba(67,97,238,0.25);
        }

        .btn-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 10px;
            background: white;
            border: 1px solid var(--gray-200);
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .btn-link:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .summary-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 30px;
            border-left: 4px solid var(--primary);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .summary-item {
            padding: 20px;
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius);
            background: var(--gray-100);
            transition: var(--transition);
        }

        .summary-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
            border-color: var(--primary-light);
        }

        .summary-item h4 {
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray-500);
            margin-bottom: 8px;
        }

        .summary-item p {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
        }

        .table-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 0 0 20px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 40px;
            overflow: hidden;
            transition: var(--transition);
        }

        .table-card h2 span {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-500);
            margin-top: 6px;
        }

        .table-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .table-card h2 {
            margin: 0;
            padding: 20px 30px;
            border-bottom: 1px solid var(--gray-200);
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            background: var(--gray-100);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table-responsive table {
            min-width: 800px;
        }

        th, td {
            padding: 16px 24px;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.8px;
            color: var(--gray-500);
            font-weight: 600;
            background: var(--gray-50);
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr:hover {
            background: var(--primary-light);
        }

        .tech-name {
            font-weight: 600;
            display: block;
            color: var(--dark);
        }

        .tech-meta {
            font-size: 13px;
            color: var(--gray-500);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: var(--primary-light);
            color: var(--primary);
        }

        .amount {
            font-weight: 700;
            color: var(--dark);
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .pill-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .pill-status.paid { 
            background: rgba(34,197,94,0.1); 
            color: #15803d; 
        }

        .pill-status.verified { 
            background: rgba(59,130,246,0.1); 
            color: #1d4ed8; 
        }

        .pill-status.pending { 
            background: rgba(249,115,22,0.1); 
            color: #c2410c; 
        }

        .chart-container {
            padding: 20px 30px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 40px;
        }

        .detail-panel {
            padding-bottom: 0;
        }

        .detail-body {
            padding: 20px 30px;
        }

        .detail-header {
            border-bottom: 1px solid var(--gray-200);
            padding-bottom: 18px;
            margin-bottom: 18px;
        }

        .detail-header h2 {
            margin: 0;
            font-size: 20px;
        }

        .detail-header p {
            margin: 6px 0 0;
            color: var(--gray-500);
            font-size: 14px;
        }

        .payments-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
            max-height: 460px;
            overflow-y: auto;
            padding-right: 6px;
        }

        .payment-pill {
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            padding: 14px 16px;
            background: var(--gray-50);
        }

        .payment-pill h4 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
        }

        .payment-pill .meta {
            font-size: 13px;
            color: var(--gray-500);
        }

        .technician-row {
            cursor: pointer;
        }

        .technician-row.active,
        .technician-row.active td {
            background: rgba(67, 97, 238, 0.08);
        }

        .technician-row:hover td {
            background: rgba(67, 97, 238, 0.05);
        }

        .badge.primary {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        @media (max-width: 1024px) {
            .data-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .page { 
                padding: 0 16px 40px; 
                margin: 20px auto;
            }
            
            th, td { 
                padding: 12px 16px; 
            }
            
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>Technicians Gain</h1>
            <p class="subtitle">Overview of technician payouts and earnings</p>
        </div>

        <div class="summary-card">
            <div class="summary-grid">
                <div class="summary-item">
                    <h4>Total Gain</h4>
                    <p>ETB <?php echo number_format($totalGain, 2); ?></p>
                </div>
                <div class="summary-item">
                    <h4>Owner Share (30%)</h4>
                    <p>ETB <?php echo number_format($ownerShare, 2); ?></p>
                </div>
                <div class="summary-item">
                    <h4>Technicians Net (70%)</h4>
                    <p>ETB <?php echo number_format($technicianShare, 2); ?></p>
                </div>
                <div class="summary-item">
                    <h4>Total Technicians</h4>
                    <p><?php echo count($technicians); ?></p>
                </div>
                <div class="summary-item">
                    <h4>Average Gain</h4>
                    <p>ETB <?php echo count($technicians) > 0 ? number_format($totalGain / count($technicians), 2) : '0.00'; ?></p>
                </div>
                <div class="summary-item">
                    <h4>Payments Recorded</h4>
                    <p><?php echo array_sum(array_column($technicians, 'payments_count')); ?></p>
                </div>
            </div>
        </div>

        <div class="action-bar">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="technicianSearch" placeholder="Search by name, email, or profession">
            </div>
            <a href="dashboard.php" class="btn-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="table-card">
            <h2>Visual Insights <span>Real-time earnings vs. total disbursements</span></h2>
            <?php if(count($technicians) > 0): ?>
                <div class="chart-container">
                    <canvas id="technicianGainChart" height="120"></canvas>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-chart-bar"></i>
                    <p>No technician data to visualize.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="data-grid">
            <div class="table-card">
                <h2>Technician Earnings</h2>
                <?php if(count($technicians) > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Technician</th>
                                    <th>Contact</th>
                                    <th>Profession</th>
                                    <th>Payments</th>
                                    <th>Total Received</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="techniciansTableBody">
                                <?php foreach($technicians as $tech): 
                                    $searchText = strtolower(trim(($tech['first_name'] ?? '') . ' ' . ($tech['last_name'] ?? '') . ' ' . ($tech['email'] ?? '') . ' ' . ($tech['phone'] ?? '') . ' ' . ($tech['profession'] ?? '')));
                                ?>
                                    <tr class="technician-row" 
                                        data-tech-id="<?php echo (int)$tech['id']; ?>"
                                        data-tech-name="<?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name'], ENT_QUOTES); ?>"
                                        data-tech-email="<?php echo htmlspecialchars($tech['email'], ENT_QUOTES); ?>"
                                        data-tech-phone="<?php echo htmlspecialchars($tech['phone'] ?: 'No phone', ENT_QUOTES); ?>"
                                        data-tech-profession="<?php echo htmlspecialchars($tech['profession'] ?: 'Not specified', ENT_QUOTES); ?>"
                                        data-payments-count="<?php echo (int)$tech['payments_count']; ?>"
                                        data-total-received="<?php echo number_format($tech['total_received'], 2); ?>"
                                        data-search-text="<?php echo htmlspecialchars($searchText, ENT_QUOTES); ?>">
                                        <td>
                                            <span class="tech-name"><?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?></span>
                                            <span class="tech-meta">Joined <?php echo date('M j, Y', strtotime($tech['created_at'])); ?></span>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($tech['email']); ?></div>
                                            <span class="tech-meta"><?php echo htmlspecialchars($tech['phone'] ?: 'No phone'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($tech['profession'] ?: 'Not specified'); ?></td>
                                        <td><?php echo (int)$tech['payments_count']; ?></td>
                                        <td class="amount">ETB <?php echo number_format($tech['total_received'], 2); ?></td>
                                        <td>
                                            <span class="badge primary">
                                                <i class="fas fa-circle"></i>
                                                <?php echo ucfirst($tech['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <p>No technicians found.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="table-card detail-panel" id="technicianDetailPanel">
                <div class="detail-body">
                    <div class="detail-header">
                        <h2 id="detailTechName">Select a technician</h2>
                        <p id="detailTechMeta">Tap a row in the table to explore their payment history.</p>
                    </div>
                    <div class="payments-list" id="technicianPaymentsList">
                        <div class="empty-state" style="padding: 20px;">
                            <i class="fas fa-hand-pointer"></i>
                            <p>Choose a technician to see detailed payouts.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <h2>Recent Technician Payments</h2>
            <?php if(count($detailedPayments) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Technician</th>
                                <th>Service</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Dates</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($detailedPayments as $payment): ?>
                                <tr>
                                    <td>
                                        <span class="tech-name"><?php echo htmlspecialchars($payment['technician_name']); ?></span>
                                        <span class="tech-meta"><?php echo htmlspecialchars($payment['technician_profession'] ?: ''); ?></span>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($payment['service_title']); ?></div>
                                        <span class="tech-meta">Homeowner: <?php echo htmlspecialchars($payment['homeowner_name']); ?></span>
                                    </td>
                                    <td class="amount">ETB <?php echo number_format($payment['amount'], 2); ?></td>
                                    <td>
                                        <?php $statusClass = strtolower($payment['payment_status']); ?>
                                        <span class="pill-status <?php echo $statusClass; ?>">
                                            <i class="fas <?php echo $statusClass === 'verified' ? 'fa-check-circle' : ($statusClass === 'paid' ? 'fa-receipt' : 'fa-clock'); ?>"></i>
                                            <?php echo ucfirst($payment['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($payment['paid_at']): ?>Paid: <?php echo date('M j, Y', strtotime($payment['paid_at'])); ?><br><?php endif; ?>
                                        <?php if($payment['verified_at']): ?>Verified: <?php echo date('M j, Y', strtotime($payment['verified_at'])); ?><br><?php endif; ?>
                                        <span class="tech-meta">Recorded: <?php echo date('M j, Y', strtotime($payment['created_at'])); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <p>No payment records yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const gainCtx = document.getElementById('technicianGainChart');
        if (gainCtx) {
            new Chart(gainCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chartLabels); ?>,
                    datasets: [{
                        label: 'Total Received (ETB)',
                        data: <?php echo json_encode($chartData); ?>,
                        backgroundColor: 'rgba(67, 97, 238, 0.6)',
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    plugins: { 
                        legend: { 
                            display: false 
                        } 
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: value => 'ETB ' + value.toLocaleString()
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        const paymentsByTech = <?php echo $paymentsByTechJson ?: '{}'; ?>;
        const technicianRows = document.querySelectorAll('.technician-row');
        const detailName = document.getElementById('detailTechName');
        const detailMeta = document.getElementById('detailTechMeta');
        const paymentsList = document.getElementById('technicianPaymentsList');
        const searchInput = document.getElementById('technicianSearch');

        const formatDate = (dateString) => {
            if (!dateString) return '—';
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;
            return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
        };

        const renderPayments = (payments) => {
            if (!payments || payments.length === 0) {
                paymentsList.innerHTML = `
                    <div class="empty-state" style="padding: 20px;">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <p>No payment history for this technician yet.</p>
                    </div>`;
                return;
            }

            paymentsList.innerHTML = payments.map(payment => `
                <div class="payment-pill">
                    <div class="flex items-center justify-between" style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                        <div>
                            <h4>${payment.service_title ?? 'Service'}</h4>
                            <div class="meta">Homeowner: ${payment.homeowner_name ?? '—'}</div>
                        </div>
                        <div style="text-align:right;">
                            <strong>ETB ${Number(payment.amount).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})}</strong>
                            <div class="pill-status ${String(payment.payment_status).toLowerCase()}">
                                <i class="fas ${payment.payment_status?.toLowerCase() === 'verified' ? 'fa-check-circle' : payment.payment_status?.toLowerCase() === 'paid' ? 'fa-receipt' : 'fa-clock'}"></i>
                                ${payment.payment_status ?? ''}
                            </div>
                        </div>
                    </div>
                    <div class="meta" style="margin-top:8px;display:flex;flex-direction:column;gap:4px;">
                        <span>Paid: ${formatDate(payment.paid_at)}</span>
                        <span>Verified: ${formatDate(payment.verified_at)}</span>
                        <span>Recorded: ${formatDate(payment.created_at)}</span>
                    </div>
                </div>
            `).join('');
        };

        technicianRows.forEach(row => {
            row.addEventListener('click', () => {
                technicianRows.forEach(r => r.classList.remove('active'));
                row.classList.add('active');

                const techId = row.dataset.techId;
                detailName.textContent = row.dataset.techName;
                detailMeta.textContent = `${row.dataset.techProfession} • ${row.dataset.techEmail} • ${row.dataset.techPhone}`;

                renderPayments(paymentsByTech?.[techId] || []);
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', (event) => {
                const term = event.target.value.trim().toLowerCase();
                technicianRows.forEach(row => {
                    const haystack = row.dataset.searchText || '';
                    row.style.display = haystack.includes(term) ? '' : 'none';
                });
            });
        }
    </script>
</body>
</html>