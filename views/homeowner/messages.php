<?php
session_start();
require_once '../../includes/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'homeowner') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../controllers/HomeownerController.php';

$conn = getDBConnection();
$homeownerController = new HomeownerController($conn);
$stats = $homeownerController->getDashboardStats();

$userId = $_SESSION['user_id'];

$messageNotice = '';
$selectedTechnicianId = isset($_GET['technician_id']) ? (int)$_GET['technician_id'] : null;

if(isset($_POST['send_message'])) {
    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    $text = trim($_POST['message'] ?? '');

    if($receiverId && $text !== '') {
        $stmt = $conn->prepare('INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)');
        if($stmt->execute([$userId, $receiverId, $text])) {
            $messageNotice = '<div class="alert success">Message sent successfully.</div>';
            $selectedTechnicianId = $receiverId;
        } else {
            $messageNotice = '<div class="alert error">Unable to send the message. Please try again.</div>';
        }
    } else {
        $messageNotice = '<div class="alert error">Please choose a technician and type a message.</div>';
    }
}

$techStmt = $conn->prepare('SELECT id, first_name, last_name, profession, profile_photo FROM users WHERE role = "technician" AND status = "approved" ORDER BY first_name ASC, last_name ASC');
$techStmt->execute();
$technicians = $techStmt->fetchAll(PDO::FETCH_ASSOC);

$conversationStmt = $conn->prepare('
    SELECT partner.id AS partner_id,
           partner.first_name,
           partner.last_name,
           partner.profession,
           partner.profile_photo,
           latest.message AS last_message,
           latest.created_at AS last_message_time,
           (
               SELECT COUNT(*)
               FROM chat_messages cm2
               WHERE cm2.sender_id = partner.id
                 AND cm2.receiver_id = :uid
                 AND cm2.is_read = 0
           ) AS unread_count
    FROM (
        SELECT MAX(cm.id) AS last_message_id,
               CASE WHEN cm.sender_id = :uid THEN cm.receiver_id ELSE cm.sender_id END AS partner_id
        FROM chat_messages cm
        WHERE cm.sender_id = :uid OR cm.receiver_id = :uid
        GROUP BY LEAST(cm.sender_id, cm.receiver_id), GREATEST(cm.sender_id, cm.receiver_id)
    ) grouped
    JOIN chat_messages latest ON latest.id = grouped.last_message_id
    JOIN users partner ON partner.id = grouped.partner_id
    WHERE partner.role = "technician"
    ORDER BY latest.created_at DESC
');
$conversationStmt->execute(['uid' => $userId]);
$conversations = $conversationStmt->fetchAll(PDO::FETCH_ASSOC);

if(!$selectedTechnicianId && count($conversations) > 0) {
    $selectedTechnicianId = (int)$conversations[0]['partner_id'];
}

$selectedTechnician = null;
foreach($conversations as $conversation) {
    if((int)$conversation['partner_id'] === $selectedTechnicianId) {
        $selectedTechnician = $conversation;
        break;
    }
}

if(!$selectedTechnician && $selectedTechnicianId) {
    $selectedTechStmt = $conn->prepare('SELECT id AS partner_id, first_name, last_name, profession, profile_photo FROM users WHERE id = ? AND role = "technician"');
    $selectedTechStmt->execute([$selectedTechnicianId]);
    $selectedTechnician = $selectedTechStmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$conversationMessages = [];
if($selectedTechnicianId) {
    $messagesStmt = $conn->prepare('
        SELECT cm.*, u.first_name, u.last_name
        FROM chat_messages cm
        JOIN users u ON cm.sender_id = u.id
        WHERE (cm.sender_id = ? AND cm.receiver_id = ?)
           OR (cm.sender_id = ? AND cm.receiver_id = ?)
        ORDER BY cm.created_at ASC
    ');
    $messagesStmt->execute([$userId, $selectedTechnicianId, $selectedTechnicianId, $userId]);
    $conversationMessages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark all messages from this technician to the homeowner as read
    $markReadStmt = $conn->prepare('UPDATE chat_messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND COALESCE(is_read,0) = 0');
    $markReadStmt->execute([$selectedTechnicianId, $userId]);
}

// Recalculate unread message count for this homeowner after marking as read
$unreadCountStmt = $conn->prepare('SELECT COUNT(*) FROM chat_messages WHERE receiver_id = ? AND COALESCE(is_read,0) = 0');
$unreadCountStmt->execute([$userId]);
$unreadMessagesCount = (int)$unreadCountStmt->fetchColumn();

// Ensure stats on this page reflect the fresh unread count
$stats['unread_messages'] = $unreadMessagesCount;

// Get unique professions for filters
$conversationProfessionFilter = array_unique(array_filter(array_column($conversations, 'profession')));
$technicianProfessionFilter = array_unique(array_filter(array_column($technicians, 'profession')));
sort($conversationProfessionFilter);
sort($technicianProfessionFilter);

$pageTitle = 'Messages & Conversations';
$pageDescription = 'Chat with technicians, share updates, and keep every project moving smoothly in real time.';
$userName = $_SESSION['user_name'] ?? 'Homeowner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Homefix Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --accent: #f72585;
            --secondary-muted: #7209b7;
            --success: #4cc9f0;
            --dark: #1e1e2c;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 18px;
            --shadow: 0 20px 45px -20px rgba(30, 42, 86, 0.25);
        }

        body {
            font-family: 'Poppins', 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
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
            width: calc(100% - 280px);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 25px 30px;
            background: white;
            border-radius: 24px;
            box-shadow: var(--shadow);
        }

        .dashboard-header h1 {
            font-size: 2rem;
            margin-bottom: 6px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .dashboard-header p {
            color: var(--gray);
            margin: 0;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-pill {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #fff;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            color: var(--gray);
        }

        .notification-pill span {
            position: absolute;
            top: 8px;
            right: 8px;
            background: var(--accent);
            color: #fff;
            border-radius: 999px;
            font-size: 0.65rem;
            padding: 2px 6px;
        }

        .btn-primary-soft {
            border: none;
            border-radius: 999px;
            padding: 12px 24px;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow: 0 12px 25px rgba(67, 97, 238, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary-soft:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 35px rgba(67, 97, 238, 0.35);
            color: #fff;
        }

        .chat-wrapper { 
            display: flex; 
            gap: 25px; 
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 25px;
            flex-wrap: wrap;
            align-items: stretch;
        }
        
        .conversation-panel { 
            flex: 0 0 360px; 
            max-width: 420px;
            background: #fff; 
            display: flex; 
            flex-direction: column;
            border-right: 1px solid var(--gray-light);
        }
        
        .panel-section { 
            padding: 25px; 
            border-bottom: 1px solid var(--gray-light); 
        }
        
        .panel-section:last-of-type { 
            border-bottom: none; 
        }
        
        .filter-group { 
            display: flex; 
            flex-direction: column; 
            gap: 12px; 
        }
        
        .filter-group input, .filter-group select, .panel-section textarea, .panel-section select { 
            padding: 12px 16px; 
            border-radius: 12px; 
            border: 1.5px solid var(--gray-light); 
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .filter-group input:focus, .filter-group select:focus, .panel-section textarea:focus, .panel-section select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .conversation-list { 
            flex: 1; 
            overflow-y: visible; 
        }
        
        .conversation-item { 
            border-bottom: 1px solid var(--gray-light); 
            transition: all 0.3s ease; 
        }
        
        .conversation-item:last-child { 
            border-bottom: none; 
        }
        
        .conversation-link { 
            display: flex; 
            gap: 15px; 
            padding: 18px 25px; 
            align-items: center; 
            color: inherit; 
            text-decoration: none; 
        }
        
        .conversation-item.active { 
            background: linear-gradient(90deg, rgba(67, 97, 238, 0.1), transparent);
            border-left: 4px solid var(--primary);
        }
        
        .conversation-item:hover:not(.active) { 
            background: var(--gray-light); 
        }
        
        .conversation-avatar { 
            width: 50px; 
            height: 50px; 
            border-radius: 50%; 
            background: linear-gradient(135deg, var(--primary), var(--primary-light)); 
            color: #fff; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: 600; 
            font-size: 1.1rem; 
        }
        
        .conversation-info { 
            flex: 1; 
            min-width: 0; 
        }
        
        .conversation-name { 
            font-weight: 600; 
            margin-bottom: 4px; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
        }
        
        .conversation-preview { 
            font-size: 0.9rem; 
            color: var(--gray); 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
        }
        
        .conversation-meta { 
            text-align: right; 
            font-size: 0.8rem; 
            color: var(--gray); 
        }
        
        .unread-badge { 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            width: 22px; 
            height: 22px; 
            border-radius: 50%; 
            background: var(--accent); 
            color: #fff; 
            font-size: 0.75rem; 
            margin-top: 6px; 
        }
        
        .chat-area { 
            flex: 1 1 600px; 
            background: #fff; 
            display: flex; 
            flex-direction: column; 
        }
        
        .chat-header { 
            padding: 25px; 
            border-bottom: 1px solid var(--gray-light); 
            display: flex; 
            align-items: center; 
            gap: 15px; 
        }
        
        .chat-header-avatar { 
            width: 55px; 
            height: 55px; 
            border-radius: 50%; 
            background: linear-gradient(135deg, var(--primary), var(--primary-light)); 
            color: #fff; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: 600; 
            font-size: 1.2rem; 
        }
        
        .chat-header-info h2 { 
            margin: 0; 
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .chat-header-info p { 
            margin: 4px 0 0; 
            color: var(--gray); 
            font-size: 0.95rem; 
        }
        
        .messages-container { 
            flex: 1; 
            padding: 25px; 
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); 
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .message { 
            max-width: 70%; 
            padding: 16px 20px; 
            border-radius: 20px; 
            font-size: 0.95rem; 
            line-height: 1.5; 
            position: relative;
            animation: messageSlide 0.3s ease-out;
        }

        @keyframes messageSlide {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message.sent { 
            align-self: flex-end;
            background: linear-gradient(135deg, var(--primary), var(--primary-light)); 
            color: #fff; 
            border-bottom-right-radius: 6px; 
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
        }
        
        .message.received { 
            align-self: flex-start;
            background: #fff; 
            color: var(--dark); 
            border-bottom-left-radius: 6px; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--gray-light);
        }
        
        .message-time { 
            font-size: 0.75rem; 
            margin-top: 6px; 
            opacity: 0.8; 
            text-align: right; 
        }
        
        .chat-input { 
            border-top: 1px solid var(--gray-light); 
            padding: 20px 25px; 
            background: white;
        }
        
        .chat-input form { 
            display: flex; 
            gap: 12px; 
            align-items: flex-end;
        }
        
        .chat-input textarea { 
            flex: 1; 
            border-radius: 16px; 
            border: 1.5px solid var(--gray-light); 
            padding: 14px 16px; 
            font-size: 0.95rem; 
            resize: none; 
            min-height: 60px;
            max-height: 120px;
            transition: all 0.3s ease;
        }

        .chat-input textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .chat-input button { 
            background: linear-gradient(135deg, var(--primary), var(--secondary)); 
            border: none; 
            color: #fff; 
            padding: 14px 24px; 
            border-radius: 16px; 
            font-size: 1rem; 
            cursor: pointer; 
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .chat-input button:hover { 
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
        }
        
        .empty-chat { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            color: var(--gray); 
            text-align: center; 
            padding: 40px; 
        }
        
        .empty-chat i { 
            font-size: 4rem; 
            margin-bottom: 20px; 
            color: var(--gray-light);
        }

        .empty-chat h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .alert { 
            margin: 0 25px 25px; 
            padding: 14px 18px; 
            border-radius: 12px; 
            font-weight: 500;
            border-left: 4px solid transparent;
        }
        
        .alert.success { 
            background: rgba(16, 185, 129, 0.1); 
            color: #065f46; 
            border-left-color: #10b981;
        }
        
        .alert.error { 
            background: rgba(239, 68, 68, 0.1); 
            color: #991b1b; 
            border-left-color: #ef4444;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
        }

        /* Mobile Responsive */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 280px;
            }
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .chat-wrapper {
                flex-direction: column;
                height: auto;
            }
            
            .conversation-panel {
                width: 100%;
                height: 400px;
                border-right: none;
                border-bottom: 1px solid var(--gray-light);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .message {
                max-width: 85%;
            }
        }

        /* Menu Toggle for Mobile */
        .menu-toggle {
            display: none;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
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
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Include the new sidebar component -->
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="dashboard-header" data-aos="fade-down">
                <div>
                    <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                    <p><?php echo htmlspecialchars($pageDescription); ?></p>
                </div>
                <div class="header-actions">
                    <div class="text-end me-2">
                        <small class="text-muted">Signed in as</small>
                        <div class="fw-semibold"><?php echo htmlspecialchars($userName); ?></div>
                    </div>
                    <div class="notification-pill">
                        <i class="fa-solid fa-bell"></i>
                        <?php if (!empty($stats['unread_messages'])): ?>
                            <span></span>
                        <?php endif; ?>
                    </div>
                    <a href="request_service.php" class="btn-primary-soft">
                        <i class="fa-solid fa-plus"></i>
                        Request Service
                    </a>
                </div>
            </div>

            <?php if(!empty($messageNotice)): ?>
                <div data-aos="fade-down">
                    <?php echo $messageNotice; ?>
                </div>
            <?php endif; ?>

            <div class="chat-wrapper" data-searchable="messages conversations chat" data-aos="fade-up">
                <aside class="conversation-panel">
                    <div class="panel-section">
                        <h3><i class="fas fa-comments me-2"></i>Your Conversations</h3>
                        <div class="filter-group">
                            <input type="text" id="conversationSearch" placeholder="Search technician by name">
                            <select id="conversationProfessionFilter">
                                <option value="">All professions</option>
                                <?php foreach($conversationProfessionFilter as $profession): ?>
                                    <option value="<?php echo strtolower($profession); ?>"><?php echo htmlspecialchars($profession); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="conversation-list" id="conversationList">
                        <?php if(count($conversations) > 0): ?>
                            <?php foreach($conversations as $conversation): ?>
                                <?php $isActive = (int)$conversation['partner_id'] === $selectedTechnicianId; ?>
                                <div class="conversation-item <?php echo $isActive ? 'active' : ''; ?>" data-name="<?php echo strtolower($conversation['first_name'] . ' ' . $conversation['last_name']); ?>" data-profession="<?php echo strtolower($conversation['profession'] ?? ''); ?>">
                                    <a class="conversation-link" href="?technician_id=<?php echo (int)$conversation['partner_id']; ?>">
                                        <div class="conversation-avatar">
                                            <?php if(!empty($conversation['profile_photo'])): ?>
                                                <img src="<?php echo SITE_URL . '/assets/uploads/profiles/' . htmlspecialchars($conversation['profile_photo']); ?>" alt="<?php echo htmlspecialchars($conversation['first_name']); ?> profile" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($conversation['first_name'], 0, 1) . substr($conversation['last_name'], 0, 1)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="conversation-info">
                                            <div class="conversation-name"><?php echo htmlspecialchars($conversation['first_name'] . ' ' . $conversation['last_name']); ?></div>
                                            <div class="conversation-preview"><?php echo htmlspecialchars($conversation['last_message']); ?></div>
                                        </div>
                                        <div class="conversation-meta">
                                            <div><?php echo date('M j, g:i A', strtotime($conversation['last_message_time'])); ?></div>
                                            <?php if((int)$conversation['unread_count'] > 0): ?>
                                                <span class="unread-badge"><?php echo (int)$conversation['unread_count']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-chat">
                                <i class="fas fa-comments"></i>
                                <h3>No Conversations</h3>
                                <p>Start a new conversation with a technician to get help with your home services.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="panel-section">
                        <h3><i class="fas fa-plus-circle me-2"></i>Start New Conversation</h3>
                        <div class="filter-group">
                            <input type="text" id="technicianSearch" placeholder="Search technicians...">
                            <select id="technicianProfessionFilter">
                                <option value="">All professions</option>
                                <?php foreach($technicianProfessionFilter as $profession): ?>
                                    <option value="<?php echo strtolower($profession); ?>"><?php echo htmlspecialchars($profession); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <form method="POST" style="margin-top: 15px; display: flex; flex-direction: column; gap: 12px;">
                            <select name="receiver_id" id="technicianSelect" class="form-control" required>
                                <option value="">Choose a technician...</option>
                                <?php foreach($technicians as $tech): ?>
                                    <?php $techProfession = strtolower($tech['profession'] ?? ''); ?>
                                    <option value="<?php echo $tech['id']; ?>" data-name="<?php echo strtolower($tech['first_name'] . ' ' . $tech['last_name']); ?>" data-profession="<?php echo $techProfession; ?>">
                                        <?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name'] . ' - ' . ($tech['profession'] ?? 'Unknown')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <textarea name="message" class="form-control" rows="3" placeholder="Type your first message..." required style="resize: vertical;"></textarea>
                            <button type="submit" name="send_message" class="btn" style="width: 100%;">
                                <i class="fas fa-paper-plane me-2"></i>Start Conversation
                            </button>
                        </form>
                    </div>
                </aside>

                <section class="chat-area">
                    <?php if($selectedTechnicianId && $selectedTechnician): ?>
                        <div class="chat-header">
                            <div class="chat-header-avatar">
                                <?php echo strtoupper(substr($selectedTechnician['first_name'], 0, 1) . substr($selectedTechnician['last_name'], 0, 1)); ?>
                            </div>
                            <div class="chat-header-info">
                                <h2><?php echo htmlspecialchars($selectedTechnician['first_name'] . ' ' . $selectedTechnician['last_name']); ?></h2>
                                <p><?php echo htmlspecialchars($selectedTechnician['profession'] ?? 'Technician'); ?></p>
                            </div>
                        </div>

                        <div class="messages-container" id="messagesContainer">
                            <?php if(count($conversationMessages) > 0): ?>
                                <?php foreach($conversationMessages as $msg): ?>
                                    <div class="message <?php echo $msg['sender_id'] == $userId ? 'sent' : 'received'; ?>">
                                        <div><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                                        <div class="message-time"><?php echo date('M j, g:i A', strtotime($msg['created_at'])); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-chat">
                                    <i class="fas fa-comment-dots"></i>
                                    <h3>No Messages Yet</h3>
                                    <p>Start the conversation by sending your first message.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="chat-input">
                            <form method="POST">
                                <input type="hidden" name="send_message" value="1">
                                <input type="hidden" name="receiver_id" value="<?php echo $selectedTechnicianId; ?>">
                                <textarea name="message" placeholder="Type your message..." required></textarea>
                                <button type="submit">
                                    <i class="fas fa-paper-plane me-2"></i>Send
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="empty-chat">
                            <i class="fas fa-comments"></i>
                            <h3>Welcome to Messages</h3>
                            <p>Select a conversation from the sidebar or start a new one to begin chatting with technicians.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if(window.AOS) {
                AOS.init({ duration: 800, once: true, offset: 60 });
            }

            // Mobile menu toggle
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('mobile-open');
                });
            }

            const conversationSearch = document.getElementById('conversationSearch');
            const conversationProfessionFilter = document.getElementById('conversationProfessionFilter');
            const conversationItems = Array.from(document.querySelectorAll('.conversation-item'));
            const technicianSearch = document.getElementById('technicianSearch');
            const technicianProfessionFilter = document.getElementById('technicianProfessionFilter');
            const technicianSelect = document.getElementById('technicianSelect');

            const filterConversations = () => {
                const term = (conversationSearch?.value || '').trim().toLowerCase();
                const profession = conversationProfessionFilter?.value || '';
                conversationItems.forEach(item => {
                    const matchesName = item.dataset.name.includes(term);
                    const matchesProfession = !profession || item.dataset.profession === profession;
                    item.style.display = matchesName && matchesProfession ? 'block' : 'none';
                });
            };

            const filterTechnicians = () => {
                const term = (technicianSearch?.value || '').trim().toLowerCase();
                const profession = technicianProfessionFilter?.value || '';
                if(!technicianSelect) return;
                Array.from(technicianSelect.options).forEach(option => {
                    if(!option.value) return;
                    const name = option.dataset.name || '';
                    const optionProfession = option.dataset.profession || '';
                    const matchesName = name.includes(term);
                    const matchesProfession = !profession || optionProfession === profession;
                    const visible = matchesName && matchesProfession;
                    option.hidden = !visible;
                    option.disabled = !visible;
                });
                if(technicianSelect.selectedOptions.length && technicianSelect.selectedOptions[0].hidden) {
                    technicianSelect.value = '';
                }
            };

            conversationSearch?.addEventListener('input', filterConversations);
            conversationProfessionFilter?.addEventListener('change', filterConversations);
            technicianSearch?.addEventListener('input', filterTechnicians);
            technicianProfessionFilter?.addEventListener('change', filterTechnicians);

            const messagesContainer = document.getElementById('messagesContainer');
            if(messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Auto-resize textarea
            const textareas = document.querySelectorAll('textarea');
            textareas.forEach(textarea => {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                });
            });
        });
    </script>
</body>
</html>