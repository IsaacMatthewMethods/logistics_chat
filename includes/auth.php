<?php
require_once 'config.php';

// Login function
function login($email, $password) {
    global $conn;
    
    $email = $conn->real_escape_string($email);
    $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Update last login
            $conn->query("UPDATE users SET last_login = NOW() WHERE id = {$user['id']}");
            
            return true;
        }
    }
    return false;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}

// Redirect based on role
function redirectBasedOnRole() {
    if (isLoggedIn()) {
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: " . BASE_URL . "admin.php");
                break;
            case 'logistics':
                header("Location: " . BASE_URL . "logistics.php");
                break;
            case 'customer':
                header("Location: " . BASE_URL . "customer.php");
                break;
        }
        exit();
    }
}

// Logout function
function logout() {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "index.php");
    exit();
}
?>