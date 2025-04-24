<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$shipment_id = isset($_POST['shipment_id']) ? (int)$_POST['shipment_id'] : null;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if ($receiver_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid receiver']);
    exit();
}

if (empty($content)) {
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    exit();
}

// Check if receiver exists
$receiver = getUserById($receiver_id);
if (!$receiver) {
    echo json_encode(['success' => false, 'error' => 'Receiver not found']);
    exit();
}

// Check if shipment exists if provided
if ($shipment_id) {
    $shipment = $conn->query("SELECT * FROM shipments WHERE id = $shipment_id")->fetch_assoc();
    if (!$shipment) {
        echo json_encode(['success' => false, 'error' => 'Shipment not found']);
        exit();
    }
}

// Send the message
$success = sendMessage($_SESSION['user_id'], $receiver_id, $content, $shipment_id);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to send message']);
}
?>