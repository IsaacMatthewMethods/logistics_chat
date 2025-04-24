<?php
require_once 'includes/auth.php';
requireLogin();

$shipment_id = isset($_GET['shipment_id']) ? (int)$_GET['shipment_id'] : null;
$receiver_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;

// For customers, the receiver is always logistics support
if ($_SESSION['role'] === 'customer' && !$receiver_id) {
    $logistics = $conn->query("SELECT id FROM users WHERE role = 'logistics' LIMIT 1")->fetch_assoc();
    $receiver_id = $logistics ? $logistics['id'] : null;
}

// For logistics, customer_id must be provided
if ($_SESSION['role'] === 'logistics' && !$receiver_id) {
    header("Location: " . BASE_URL . "logistics.php");
    exit();
}

// Get receiver info
$receiver = $receiver_id ? getUserById($receiver_id) : null;
if (!$receiver) {
    header("Location: " . BASE_URL . ($_SESSION['role'] === 'customer' ? "customer.php" : "logistics.php"));
    exit();
}

// Get shipment info if provided
$shipment = null;
if ($shipment_id) {
    $shipment = $conn->query("SELECT * FROM shipments WHERE id = $shipment_id")->fetch_assoc();
}

// Get messages
$messages = getMessages($_SESSION['user_id'], $receiver_id, $shipment_id);

// Mark messages as read
$conn->query("UPDATE messages SET is_read = TRUE 
              WHERE receiver_id = {$_SESSION['user_id']} 
              AND sender_id = $receiver_id 
              AND is_read = FALSE");
?>
<?php include 'includes/header.php'; ?>

<div class="chat-container">
    <div class="chat-sidebar">
        <div class="chat-header">
            <h3>Conversations</h3>
        </div>
        
        <ul class="conversation-list">
            <?php foreach (getUserConversations($_SESSION['user_id']) as $conversation): ?>
                <li class="conversation-item <?php echo $conversation['other_user_id'] == $receiver_id ? 'active' : ''; ?>" 
                    data-conversation-id="<?php echo $conversation['other_user_id']; ?>">
                    <div class="conversation-user">
                        <div class="conversation-avatar">
                            <?php echo strtoupper(substr($conversation['other_user_name'], 0, 1)); ?>
                        </div>
                        <div class="conversation-info">
                            <div class="conversation-name">
                                <?php echo e($conversation['other_user_name']); ?>
                                <span class="conversation-role">
                                    (<?php echo ucfirst(e($conversation['other_user_role'])); ?>)
                                </span>
                            </div>
                            <div class="conversation-preview">
                                <?php 
                                $last_message = $conn->query("
                                    SELECT content FROM messages 
                                    WHERE (sender_id = {$_SESSION['user_id']} AND receiver_id = {$conversation['other_user_id']}) 
                                    OR (sender_id = {$conversation['other_user_id']} AND receiver_id = {$_SESSION['user_id']}) 
                                    ORDER BY created_at DESC LIMIT 1
                                ")->fetch_assoc();
                                echo e($last_message ? $last_message['content'] : 'No messages yet');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="conversation-time">
                        <?php echo date('H:i', strtotime($conversation['last_message_time'])); ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="chat-main">
        <div class="chat-header" data-receiver-id="<?php echo $receiver_id; ?>" data-shipment-id="<?php echo $shipment_id; ?>">
            <div class="chat-user">
                <div class="chat-user-avatar">
                    <?php echo strtoupper(substr($receiver['username'], 0, 1)); ?>
                </div>
                <div>
                    <div class="chat-user-name"><?php echo e($receiver['username']); ?></div>
                    <?php if ($shipment): ?>
                        <div class="chat-shipment-info">
                            Shipment: <?php echo e($shipment['tracking_number']); ?>
                            <span class="status-badge status-<?php echo e($shipment['status']); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $shipment['status'])); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="chat-messages">
            <?php foreach ($messages as $message): ?>
                <div class="message <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'message-sent' : 'message-received'; ?>">
                    <div class="message-content">
                        <?php echo nl2br(e($message['content'])); ?>
                    </div>
                    <div class="message-time">
                        <?php echo date('H:i', strtotime($message['created_at'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="chat-input">
            <textarea class="chat-input-field" placeholder="Type your message here..."></textarea>
            <button class="chat-send-btn">
                <i class="fas fa-paper-plane"></i> Send
            </button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>