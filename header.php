php
<?php
require_once 'auth.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics Chat System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <header class="main-header">
        <div class="header-content">
            <div class="logo">
                <h1>Logistics<span>Chat</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="/admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="/admin.php?page=users"><i class="fas fa-users"></i> Users</a></li>
                        <li><a href="/admin.php?page=shipments"><i class="fas fa-truck"></i> Shipments</a></li>
                    <?php elseif ($_SESSION['role'] === 'logistics'): ?>
                        <li><a href="/logistics.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="/chat.php"><i class="fas fa-comments"></i> Chats</a></li>
                        <li><a href="/logistics.php?page=shipments"><i class="fas fa-truck"></i> Shipments</a></li>
                    <?php else: ?>
                        <li><a href="/customer.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="/chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                        <li><a href="/customer.php?page=shipments"><i class="fas fa-truck"></i> My Shipments</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="user-actions">
                <div class="user-dropdown">
                    <button class="user-btn">
                        <i class="fas fa-user-circle"></i> <?php echo e($_SESSION['username']); ?>
                        <i class="fas fa-caret-down"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="#"><i class="fas fa-user"></i> Profile</a>
                        <a href="/?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <?php endif; ?>
    <main class="main-content">