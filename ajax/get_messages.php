<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;
$shipment_id = isset($_GET['shipment_id']) ? (int)$_GET['shipment_id'] : null;

if ($receiver_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid receiver']);
    exit();
}

// Get messages
$messages = getMessages($_SESSION['user_id'], $receiver_id, $shipment_id);

// Mark messages as read
$conn->query("UPDATE messages SET is_read = TRUE 
              WHERE receiver_id = {$_SESSION['user_id']} 
              AND sender_id = $receiver_id 
              AND is_read = FALSE");

echo json_encode([
    'success' => true,
    'messages' => $messages
]);
?>