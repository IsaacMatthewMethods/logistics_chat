php
<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'header.php';

if (isset($_SESSION['user_id'])) {
    if($_SESSION['role'] === 'admin'){
        header('Location: admin.php');
        exit();
    } elseif($_SESSION['role'] === 'logistics'){
        header('Location: logistics.php');
        exit();
    } else {
        header('Location: customer.php');
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if($_SESSION['role'] === 'admin'){
                header('Location: admin.php');
                exit();
            }elseif($_SESSION['role'] === 'logistics'){
                header('Location: logistics.php');
                exit();
            }else{
                header('Location: customer.php');
                exit();
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<div class="main-content">
    <div class="form-container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo e($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'footer.php';
?>
