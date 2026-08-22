<?php
session_start();
require_once '../../includes/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'technician') {
    header('Location: ../../login.php');
    exit();
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

$messageNotice = '';
$selectedHomeownerId = isset($_GET['homeowner_id']) ? (int)$_GET['homeowner_id'] : null;

// When technician opens the Messages page, mark all incoming messages as read
$markAllReadStmt = $conn->prepare('UPDATE chat_messages SET is_read = 1 WHERE receiver_id = ? AND COALESCE(is_read,0) = 0');
$markAllReadStmt->execute([$userId]);

$initiatedStmt = $conn->prepare('
    SELECT DISTINCT u.id, u.first_name, u.last_name
    FROM chat_messages cm
    JOIN users u ON cm.sender_id = u.id
    WHERE cm.receiver_id = ? AND u.role = "homeowner"
    ORDER BY u.first_name ASC, u.last_name ASC
');
$initiatedStmt->execute([$userId]);
$initiatedHomeowners = $initiatedStmt->fetchAll(PDO::FETCH_ASSOC);
$initiatedHomeownerIds = array_map('intval', array_column($initiatedHomeowners, 'id'));

if(isset($_POST['send_message'])) {
    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    $text = trim($_POST['message'] ?? '');

    if($receiverId && $text !== '') {
        if(in_array($receiverId, $initiatedHomeownerIds, true)) {
            $stmt = $conn->prepare('INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)');
            if($stmt->execute([$userId, $receiverId, $text])) {
                $messageNotice = '<div class="alert success">Message sent.</div>';
                $selectedHomeownerId = $receiverId;
            } else {
                $messageNotice = '<div class="alert error">Unable to send message right now.</div>';
            }
        } else {
            $messageNotice = '<div class="alert error">Only homeowners can start a conversation. Reply to an existing chat.</div>';
        }
    } else {
        $messageNotice = '<div class="alert error">Please choose a homeowner and enter a message.</div>';
    }
}

$conversationStmt = $conn->prepare('
    SELECT partner.id AS partner_id,
           partner.first_name,
           partner.last_name,
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
    WHERE partner.role = "homeowner"
    ORDER BY latest.created_at DESC
');
$conversationStmt->execute(['uid' => $userId]);
$conversations = $conversationStmt->fetchAll(PDO::FETCH_ASSOC);

if(!$selectedHomeownerId && count($conversations) > 0) {
    $selectedHomeownerId = (int)$conversations[0]['partner_id'];
}

$selectedHomeowner = null;
foreach($conversations as $conversation) {
    if((int)$conversation['partner_id'] === $selectedHomeownerId) {
        $selectedHomeowner = $conversation;
        break;
    }
}

if($selectedHomeownerId && !$selectedHomeowner) {
    $selectedHomeownerId = null;
}

$conversationMessages = [];
if($selectedHomeownerId) {
    $messagesStmt = $conn->prepare('
        SELECT cm.*, u.first_name, u.last_name
        FROM chat_messages cm
        JOIN users u ON cm.sender_id = u.id
        WHERE (cm.sender_id = ? AND cm.receiver_id = ?)
           OR (cm.sender_id = ? AND cm.receiver_id = ?)
        ORDER BY cm.created_at ASC
    ');
    $messagesStmt->execute([$userId, $selectedHomeownerId, $selectedHomeownerId, $userId]);
    $conversationMessages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);

    $markReadStmt = $conn->prepare('UPDATE chat_messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0');
    $markReadStmt->execute([$selectedHomeownerId, $userId]);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - HomeFix Pro</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .chat-wrapper { display: flex; gap: 20px; padding: 20px; }
        .conversation-panel { width: 320px; background: #fff; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); display: flex; flex-direction: column; }
        .panel-section { padding: 20px; border-bottom: 1px solid #f0f0f0; }
        .panel-section:last-of-type { border-bottom: none; }
        .panel-section h3 { margin: 0 0 12px; font-size: 1.1rem; }
        .filter-group { display: flex; flex-direction: column; gap: 10px; }
        .filter-group input, .filter-group select { padding: 10px; border-radius: 8px; border: 1px solid #dcdcdc; font-size: 0.95rem; }
        .conversation-list { flex: 1; overflow-y: auto; max-height: 420px; }
        .conversation-item { border-bottom: 1px solid #f1f1f1; transition: background 0.2s ease, border-left 0.2s ease; }
        .conversation-item:last-child { border-bottom: none; }
        .conversation-link { display: flex; gap: 12px; padding: 15px 20px; align-items: center; color: inherit; text-decoration: none; }
        .conversation-item.active { border-left: 4px solid #1c9c6b; background: #f3fffa; }
        .conversation-item:hover { background: #f3fffa; }
        .conversation-avatar { width: 46px; height: 46px; border-radius: 50%; background: #1c9c6b; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1rem; }
        .conversation-info { flex: 1; min-width: 0; }
        .conversation-name { font-weight: 600; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .conversation-preview { font-size: 0.88rem; color: #666; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .conversation-meta { text-align: right; font-size: 0.78rem; color: #999; }
        .unread-badge { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: #ff7a18; color: #fff; font-size: 0.75rem; margin-top: 6px; }
        .chat-area { flex: 1; background: #fff; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); display: flex; flex-direction: column; }
        .chat-header { padding: 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 12px; }
        .chat-header-avatar { width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, #32d586, #1c9c6b); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.1rem; }
        .chat-header-info h2 { margin: 0; font-size: 1.2rem; }
        .chat-header-info p { margin: 4px 0 0; color: #777; font-size: 0.9rem; }
        .messages-container { flex: 1; padding: 20px; background: #f6fffa; overflow-y: auto; }
        .message { max-width: 70%; margin-bottom: 15px; padding: 12px 16px; border-radius: 18px; font-size: 0.95rem; line-height: 1.4; position: relative; }
        .message.sent { margin-left: auto; background: #1c9c6b; color: #fff; border-bottom-right-radius: 6px; }
        .message.received { background: #fff; color: #2c2c2c; border-bottom-left-radius: 6px; box-shadow: 0 3px 12px rgba(0,0,0,0.05); }
        .message-time { font-size: 0.75rem; margin-top: 6px; opacity: 0.7; text-align: right; }
        .chat-input { border-top: 1px solid #f0f0f0; padding: 18px 20px; }
        .chat-input form { display: flex; gap: 10px; }
        .chat-input textarea { flex: 1; border-radius: 12px; border: 1px solid #dcdcdc; padding: 12px; font-size: 0.95rem; resize: none; min-height: 60px; }
        .chat-input button { background: #1c9c6b; border: none; color: #fff; padding: 0 24px; border-radius: 12px; font-size: 1rem; cursor: pointer; transition: background 0.2s ease; }
        .chat-input button:hover { background: #17845a; }
        .empty-chat { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #888; text-align: center; padding: 40px; }
        .info-note { font-size: 0.9rem; color: #666; line-height: 1.5; }
        .alert { margin: 0 20px 20px; padding: 12px 15px; border-radius: 8px; font-weight: 500; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        @media (max-width: 992px) {
            .chat-wrapper { flex-direction: column; }
            .conversation-panel { width: 100%; }
            .conversation-list { max-height: none; }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Messages</h1>
                <p>Homeowners open the conversation, and you keep it moving.</p>
            </div>

            <?php echo $messageNotice; ?>

            <div class="chat-wrapper">
                <aside class="conversation-panel">
                    <div class="panel-section">
                        <h3>Homeowner Threads</h3>
                        <div class="filter-group">
                            <input type="text" id="conversationSearch" placeholder="Search homeowner by name">
                            <select id="conversationStatusFilter">
                                <option value="">All conversations</option>
                                <option value="unread">Unread only</option>
                                <option value="replied">No unread</option>
                            </select>
                        </div>
                    </div>

                    <div class="conversation-list" id="conversationList">
                        <?php if(count($conversations) > 0): ?>
                            <?php foreach($conversations as $conversation): ?>
                                <?php
                                    $isActive = (int)$conversation['partner_id'] === $selectedHomeownerId;
                                    $nameKey = strtolower($conversation['first_name'] . ' ' . $conversation['last_name']);
                                    $hasUnread = (int)$conversation['unread_count'] > 0 ? '1' : '0';
                                ?>
                                <div class="conversation-item <?php echo $isActive ? 'active' : ''; ?>" data-name="<?php echo $nameKey; ?>" data-unread="<?php echo $hasUnread; ?>">
                                    <a class="conversation-link" href="?homeowner_id=<?php echo (int)$conversation['partner_id']; ?>">
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
                            <div class="empty-chat" style="padding: 30px;">
                                <p>No conversations yet. Homeowners will appear here once they contact you.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="panel-section">
                        <h3>Need-to-know</h3>
                        <p class="info-note">Homeowners send the first message. Use the chat on the right to respond, keep track of unread messages, and follow up quickly.</p>
                    </div>
                </aside>

                <section class="chat-area">
                    <?php if($selectedHomeownerId && $selectedHomeowner): ?>
                        <div class="chat-header">
                            <div class="chat-header-avatar">
                                <?php if(!empty($selectedHomeowner['profile_photo'])): ?>
                                    <img src="<?php echo SITE_URL . '/assets/uploads/profiles/' . htmlspecialchars($selectedHomeowner['profile_photo']); ?>" alt="<?php echo htmlspecialchars($selectedHomeowner['first_name']); ?> profile" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($selectedHomeowner['first_name'], 0, 1) . substr($selectedHomeowner['last_name'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>

                            <div class="chat-header-info">
                                <h2><?php echo htmlspecialchars($selectedHomeowner['first_name'] . ' ' . $selectedHomeowner['last_name']); ?></h2>
                                <p>Homeowner</p>
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
                                <div class="empty-chat" style="padding: 30px;">
                                    <p>This homeowner has not sent any messages yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if(in_array($selectedHomeownerId, $initiatedHomeownerIds, true)): ?>
                            <div class="chat-input">
                                <form method="POST">
                                    <input type="hidden" name="send_message" value="1">
                                    <input type="hidden" name="receiver_id" value="<?php echo $selectedHomeownerId; ?>">
                                    <textarea name="message" placeholder="Type your reply..." required></textarea>
                                    <button type="submit">Send</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="empty-chat" style="padding: 20px;">
                                <p>You can reply once the homeowner starts the chat.</p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-chat">
                            <p>Select a homeowner conversation from the left to view and reply.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const conversationSearch = document.getElementById('conversationSearch');
            const conversationStatusFilter = document.getElementById('conversationStatusFilter');
            const conversationItems = document.querySelectorAll('.conversation-item');

            const filterConversations = () => {
                const term = (conversationSearch?.value || '').trim().toLowerCase();
                const status = conversationStatusFilter?.value || '';
                conversationItems.forEach(item => {
                    const matchesName = item.dataset.name.includes(term);
                    let matchesStatus = true;
                    if(status === 'unread') {
                        matchesStatus = item.dataset.unread === '1';
                    } else if(status === 'replied') {
                        matchesStatus = item.dataset.unread === '0';
                    }
                    item.style.display = matchesName && matchesStatus ? 'block' : 'none';
                });
            };

            conversationSearch?.addEventListener('input', filterConversations);
            conversationStatusFilter?.addEventListener('change', filterConversations);

            const messagesContainer = document.getElementById('messagesContainer');
            if(messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        })();
    </script>
</body>
</html>