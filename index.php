<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    redirectBasedOnRole();
}

if (isset($_GET['logout'])) {
    logout();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if (login($email, $password)) {
        redirectBasedOnRole();
    } else {
        $error = "Invalid email or password";
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="form-container">
    <h2 class="form-title">Logistics Chat System</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <div class="form-actions">
            <button type="submit" name="login" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>