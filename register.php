<?php
require_once 'includes/db_connect.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = htmlspecialchars($_POST['full_name']);
    $email = htmlspecialchars($_POST['email']);
    $phone_number = htmlspecialchars($_POST['phone_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error_message = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM tenants WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Email already registered!";
        } else {
            // Hash password and insert new tenant
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO tenants (full_name, email, phone_number, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $full_name, $email, $phone_number, $hashed_password);
            
            if ($stmt->execute()) {
                $success_message = "Registration successful! You can now login.";
            } else {
                $error_message = "Registration failed! Please try again.";
            }
        }
    }
}

$page_title = "Register";
$css_path = "css/style.css";
$js_path = "js/script.js";
include 'includes/header.php';
?>

<div class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h2>Join JuanUnit</h2>
            <p>Create your tenant account</p>
        </div>

        <?php if ($success_message): ?>
            <div style="background: #e8f5e8; color: #2e7d32; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; text-align: center;">
                <?php echo $success_message; ?>
                <p style="margin-top: 0.5rem;"><a href="login.php" style="color: #2e7d32; font-weight: 600;">Go to Login</a></p>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; text-align: center;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="tel" id="phone_number" name="phone_number" class="form-control" value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control" required>
                    <i class="fas fa-eye" onclick="togglePassword('password', 'password-toggle')" id="password-toggle" style="position: absolute; right: 15px; top: 15px; cursor: pointer; color: #999;"></i>
                </div>
                <small style="color: #666; font-size: 12px;">Minimum 6 characters</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div style="position: relative;">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    <i class="fas fa-eye" onclick="togglePassword('confirm_password', 'confirm-password-toggle')" id="confirm-password-toggle" style="position: absolute; right: 15px; top: 15px; cursor: pointer; color: #999;"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                Create Account
            </button>
        </form>

        <div class="text-center">
            <p>Already have an account? <a href="login.php" style="color: #667eea; text-decoration: none; font-weight: 600;">Login here</a></p>
            <p><a href="index.php" style="color: #999; text-decoration: none;">← Back to Home</a></p>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.style.borderColor = '#ff6b6b';
    } else {
        this.style.borderColor = '#11998e';
    }
});

// Form validation before submit
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long!');
        return false;
    }
});
</script>

<?php include 'includes/footer.php'; ?>