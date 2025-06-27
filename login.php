<?php
session_start();
require_once 'includes/db_connect.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_username = trim(htmlspecialchars($_POST['email_username']));
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    if ($user_type == 'admin') {
        // Admin login
        $stmt = $conn->prepare("SELECT id, password, full_name FROM admins WHERE username = ?");
        $stmt->bind_param("s", $email_username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                header("Location: admin/index.php");
                exit();
            } else {
                $error_message = "Invalid password!";
            }
        } else {
            $error_message = "Admin username not found!";
        }
    } else {
        // Tenant login
        $stmt = $conn->prepare("SELECT id, password, full_name FROM tenants WHERE email = ?");
        $stmt->bind_param("s", $email_username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $tenant = $result->fetch_assoc();
            if (password_verify($password, $tenant['password'])) {
                $_SESSION['tenant_id'] = $tenant['id'];
                $_SESSION['tenant_name'] = $tenant['full_name'];
                header("Location: user/index.php");
                exit();
            } else {
                $error_message = "Invalid password!";
            }
        } else {
            $error_message = "Email address not found!";
        }
    }
}

$page_title = "Login";
$css_path = "css/style.css";
$js_path = "js/script.js";
include 'includes/header.php';
?>

<div class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h2>Welcome Back</h2>
            <p>Sign in to your JuanUnit account</p>
        </div>

        <?php if ($error_message): ?>
            <div style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; text-align: center;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <!-- User Type Toggle -->
            <div class="user-type-toggle">
                <input type="radio" id="tenant" name="user_type" value="tenant" checked>
                <label for="tenant">Tenant</label>
                
                <input type="radio" id="admin" name="user_type" value="admin">
                <label for="admin">Admin</label>
            </div>

            <div class="form-group">
                <label for="email_username" id="login-label">Email Address</label>
                <input type="text" id="email_username" name="email_username" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control" required>
                    <i class="fas fa-eye" onclick="togglePassword('password', 'password-toggle')" id="password-toggle" style="position: absolute; right: 15px; top: 15px; cursor: pointer; color: #999;"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                Sign In
            </button>
        </form>

        <div class="text-center">
            <p>Don't have an account? <a href="register.php" style="color: #667eea; text-decoration: none; font-weight: 600;">Register here</a></p>
            <p><a href="index.php" style="color: #999; text-decoration: none;">← Back to Home</a></p>
        </div>
    </div>
</div>

<script>
// Update label based on user type selection
document.addEventListener('DOMContentLoaded', function() {
    const tenantRadio = document.getElementById('tenant');
    const adminRadio = document.getElementById('admin');
    const label = document.getElementById('login-label');
    
    tenantRadio.addEventListener('change', function() {
        if (this.checked) {
            label.textContent = 'Email Address';
        }
    });
    
    adminRadio.addEventListener('change', function() {
        if (this.checked) {
            label.textContent = 'Username';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>