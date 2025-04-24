<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'create_user':
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) ? $_POST['role'] : '';
        
        if (empty($username) || empty($email) || empty($password) || empty($role)) {
            echo json_encode(['success' => false, 'error' => 'All fields are required']);
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Invalid email']);
            exit();
        }
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'Email already exists']);
            exit();
        }
        
        // Create user
        $success = createUser($username, $email, $password, $role);
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create user']);
        }
        break;
        
    case 'update_user':
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $role = isset($_POST['role']) ? $_POST['role'] : '';
        
        if ($user_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user']);
            exit();
        }
        
        if (empty($username) || empty($email) || empty($role)) {
            echo json_encode(['success' => false, 'error' => 'All fields are required']);
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Invalid email']);
            exit();
        }
        
        // Check if email already exists for another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'Email already exists']);
            exit();
        }
        
        // Update user
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $role, $user_id);
        $success = $stmt->execute();
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update user']);
        }
        break;
        
    case 'delete_user':
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        
        if ($user_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user']);
            exit();
        }
        
        // Prevent deleting yourself
        if ($user_id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'error' => 'You cannot delete yourself']);
            exit();
        }
        
        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $success = $stmt->execute();
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete user']);
        }
        break;
        
    case 'create_shipment':
        $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
        $tracking_number = isset($_POST['tracking_number']) ? trim($_POST['tracking_number']) : '';
        $origin = isset($_POST['origin']) ? trim($_POST['origin']) : '';
        $destination = isset($_POST['destination']) ? trim($_POST['destination']) : '';
        
        if ($customer_id <= 0 || empty($tracking_number) || empty($origin) || empty($destination)) {
            echo json_encode(['success' => false, 'error' => 'All fields are required']);
            exit();
        }
        
        // Check if tracking number already exists
        $stmt = $conn->prepare("SELECT id FROM shipments WHERE tracking_number = ?");
        $stmt->bind_param("s", $tracking_number);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'Tracking number already exists']);
            exit();
        }
        
        // Create shipment
        $success = createShipment($customer_id, $tracking_number, $origin, $destination);
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create shipment']);
        }
        break;
        
    case 'update_shipment':
        $shipment_id = isset($_POST['shipment_id']) ? (int)$_POST['shipment_id'] : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        
        if ($shipment_id <= 0 || empty($status)) {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            exit();
        }
        
        // Update shipment status
        $success = updateShipmentStatus($shipment_id, $status);
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update shipment']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
?>