<?php
require_once 'config.php';

// Get user by ID
function getUserById($id) {
    global $conn;
    $id = (int)$id;
    $sql = "SELECT * FROM users WHERE id = $id LIMIT 1";
    $result = $conn->query($sql);
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

// Get all logistics users
function getLogisticsUsers() {
    global $conn;
    $sql = "SELECT * FROM users WHERE role = 'logistics'";
    $result = $conn->query($sql);
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    return $users;
}

// Get all customers
function getCustomers() {
    global $conn;
    $sql = "SELECT * FROM users WHERE role = 'customer'";
    $result = $conn->query($sql);
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    return $users;
}

// Get shipments for customer
function getCustomerShipments($customer_id) {
    global $conn;
    $customer_id = (int)$customer_id;
    $sql = "SELECT * FROM shipments WHERE customer_id = $customer_id ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $shipments = [];
    while ($row = $result->fetch_assoc()) {
        $shipments[] = $row;
    }
    return $shipments;
}

// Get all shipments (for admin/logistics)
function getAllShipments() {
    global $conn;
    $sql = "SELECT s.*, u.username as customer_name FROM shipments s 
            JOIN users u ON s.customer_id = u.id 
            ORDER BY s.created_at DESC";
    $result = $conn->query($sql);
    $shipments = [];
    while ($row = $result->fetch_assoc()) {
        $shipments[] = $row;
    }
    return $shipments;
}

// Get messages between two users
function getMessages($user1_id, $user2_id, $shipment_id = null) {
    global $conn;
    $user1_id = (int)$user1_id;
    $user2_id = (int)$user2_id;
    
    $sql = "SELECT m.*, u.username as sender_name 
            FROM messages m 
            JOIN users u ON m.sender_id = u.id 
            WHERE ((sender_id = $user1_id AND receiver_id = $user2_id) 
                   OR (sender_id = $user2_id AND receiver_id = $user1_id))";
    
    if ($shipment_id) {
        $shipment_id = (int)$shipment_id;
        $sql .= " AND (m.shipment_id = $shipment_id OR m.shipment_id IS NULL)";
    }
    
    $sql .= " ORDER BY m.created_at ASC";
    
    $result = $conn->query($sql);
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    return $messages;
}

// Get chat conversations for user
function getUserConversations($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    
    $sql = "SELECT DISTINCT 
                CASE 
                    WHEN m.sender_id = $user_id THEN m.receiver_id 
                    ELSE m.sender_id 
                END as other_user_id,
                u.username as other_user_name,
                u.role as other_user_role,
                MAX(m.created_at) as last_message_time
            FROM messages m
            JOIN users u ON 
                CASE 
                    WHEN m.sender_id = $user_id THEN m.receiver_id 
                    ELSE m.sender_id 
                END = u.id
            WHERE m.sender_id = $user_id OR m.receiver_id = $user_id
            GROUP BY other_user_id, other_user_name, other_user_role
            ORDER BY last_message_time DESC";
    
    $result = $conn->query($sql);
    $conversations = [];
    while ($row = $result->fetch_assoc()) {
        $conversations[] = $row;
    }
    return $conversations;
}

// Send message
function sendMessage($sender_id, $receiver_id, $content, $shipment_id = null) {
    global $conn;
    
    $sender_id = (int)$sender_id;
    $receiver_id = (int)$receiver_id;
    $content = $conn->real_escape_string(htmlspecialchars($content));
    $shipment_id = $shipment_id ? (int)$shipment_id : 'NULL';
    
    $sql = "INSERT INTO messages (sender_id, receiver_id, shipment_id, content) 
            VALUES ($sender_id, $receiver_id, $shipment_id, '$content')";
    
    return $conn->query($sql);
}

// Create shipment
function createShipment($customer_id, $tracking_number, $origin, $destination) {
    global $conn;
    
    $customer_id = (int)$customer_id;
    $tracking_number = $conn->real_escape_string($tracking_number);
    $origin = $conn->real_escape_string($origin);
    $destination = $conn->real_escape_string($destination);
    
    $sql = "INSERT INTO shipments (customer_id, tracking_number, origin, destination) 
            VALUES ($customer_id, '$tracking_number', '$origin', '$destination')";
    
    return $conn->query($sql);
}

// Update shipment status
function updateShipmentStatus($shipment_id, $status) {
    global $conn;
    
    $shipment_id = (int)$shipment_id;
    $status = $conn->real_escape_string($status);
    
    $sql = "UPDATE shipments SET status = '$status' WHERE id = $shipment_id";
    return $conn->query($sql);
}

// Create user
function createUser($username, $email, $password, $role) {
    global $conn;
    
    $username = $conn->real_escape_string($username);
    $email = $conn->real_escape_string($email);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = $conn->real_escape_string($role);
    
    $sql = "INSERT INTO users (username, email, password, role) 
            VALUES ('$username', '$email', '$hashed_password', '$role')";
    
    return $conn->query($sql);
}

// Escape HTML output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>