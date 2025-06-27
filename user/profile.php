<?php
include '../includes/session_check_user.php';
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'update_info') {
            $full_name = htmlspecialchars($_POST['full_name']);
            $email = htmlspecialchars($_POST['email']);
            $phone_number = htmlspecialchars($_POST['phone_number']);
            
            if (empty($full_name) || empty($email)) {
                $error_message = "Full name and email are required!";
            } else {
                // Check if email is already taken by another user
                $check_email_stmt = $conn->prepare("SELECT id FROM tenants WHERE email = ? AND id != ?");
                $check_email_stmt->bind_param("si", $email, $_SESSION['tenant_id']);
                $check_email_stmt->execute();
                $email_result = $check_email_stmt->get_result();
                
                if ($email_result->num_rows > 0) {
                    $error_message = "Email address is already taken by another user!";
                } else {
                    $stmt = $conn->prepare("UPDATE tenants SET full_name = ?, email = ?, phone_number = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $full_name, $email, $phone_number, $_SESSION['tenant_id']);
                    
                    if ($stmt->execute()) {
                        $_SESSION['tenant_name'] = $full_name; // Update session
                        $success_message = "Profile updated successfully!";
                    } else {
                        $error_message = "Failed to update profile!";
                    }
                }
            }
        } elseif ($_POST['action'] == 'change_password') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $error_message = "All password fields are required!";
            } elseif ($new_password !== $confirm_password) {
                $error_message = "New passwords do not match!";
            } elseif (strlen($new_password) < 6) {
                $error_message = "New password must be at least 6 characters long!";
            } else {
                // Verify current password
                $stmt = $conn->prepare("SELECT password FROM tenants WHERE id = ?");
                $stmt->bind_param("i", $_SESSION['tenant_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if (password_verify($current_password, $user['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE tenants SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $hashed_password, $_SESSION['tenant_id']);
                    
                    if ($update_stmt->execute()) {
                        $success_message = "Password changed successfully!";
                    } else {
                        $error_message = "Failed to change password!";
                    }
                } else {
                    $error_message = "Current password is incorrect!";
                }
            }
        }
    }
}

// Fetch current user information
$user_query = "SELECT t.*, u.unit_number, u.monthly_rent 
               FROM tenants t 
               LEFT JOIN units u ON t.unit_id = u.id 
               WHERE t.id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();

$page_title = "My Profile";
$css_path = "../css/style.css";
$js_path = "../js/script.js";
include '../includes/header.php';
?>

<div class="dashboard">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Tenant Portal</h3>
            <p>Welcome, <?php echo htmlspecialchars($user_info['full_name']); ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> My Payments</a></li>
            <li><a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a></li>
            <li><a href="profile.php" class="active"><i class="fas fa-user"></i> My Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>My Profile</h1>
            <p>Manage your personal information and account settings</p>
        </div>

        <?php if ($success_message): ?>
            <div style="background: #e8f5e8; color: #2e7d32; padding: 1rem; border-radius: 10px; margin-bottom: 2rem;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 10px; margin-bottom: 2rem;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Personal Information -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem; color: #333;">
                    <i class="fas fa-user-edit"></i> Personal Information
                </h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="update_info">
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($user_info['full_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user_info['email']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="tel" id="phone_number" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user_info['phone_number'] ?: ''); ?>" placeholder="Enter your phone number">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-save"></i> Update Information
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem; color: #333;">
                    <i class="fas fa-lock"></i> Change Password
                </h3>
                
                <form method="POST" id="passwordForm">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div style="position: relative;">
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                            <i class="fas fa-eye" onclick="togglePassword('current_password', 'toggle-current')" id="toggle-current" style="position: absolute; right: 15px; top: 15px; cursor: pointer; color: #999;"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div style="position: relative;">
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                            <i class="fas fa-eye" onclick="togglePassword('new_password', 'toggle-new')" id="toggle-new" style="position: absolute; right: 15px; top: 15px; cursor: pointer; color: #999;"></i>
                        </div>
                        <small style="color: #666; font-size: 12px;">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div style="position: relative;">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            <i class="fas fa-eye" onclick="togglePassword('confirm_password', 'toggle-confirm')" id="toggle-confirm" style="position: absolute; right: 15px; top: 15px; cursor: pointer; color: #999;"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-danger" style="width: 100%;">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Account Summary -->
        <div class="card" style="margin-top: 2rem;">
            <h3 style="margin-bottom: 1.5rem; color: #333;">
                <i class="fas fa-info-circle"></i> Account Summary
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div style="padding: 1.5rem; background: #f8f9ff; border-radius: 10px; border-left: 4px solid #667eea;">
                    <h4 style="color: #333; margin-bottom: 0.5rem;">Registration Date</h4>
                    <p style="color: #666; margin: 0;">
                        <?php echo date('F j, Y', strtotime($user_info['registration_date'])); ?>
                    </p>
                </div>
                
                <div style="padding: 1.5rem; background: #f8f9ff; border-radius: 10px; border-left: 4px solid #667eea;">
                    <h4 style="color: #333; margin-bottom: 0.5rem;">Current Unit</h4>
                    <p style="color: #666; margin: 0;">
                        <?php if ($user_info['unit_number']): ?>
                            Unit <?php echo htmlspecialchars($user_info['unit_number']); ?>
                        <?php else: ?>
                            Not assigned
                        <?php endif; ?>
                    </p>
                </div>
                
                <?php if ($user_info['monthly_rent']): ?>
                <div style="padding: 1.5rem; background: #f8f9ff; border-radius: 10px; border-left: 4px solid #667eea;">
                    <h4 style="color: #333; margin-bottom: 0.5rem;">Monthly Rent</h4>
                    <p style="color: #666; margin: 0;">
                        ₱<?php echo number_format($user_info['monthly_rent'], 2); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <div style="padding: 1.5rem; background: #f8f9ff; border-radius: 10px; border-left: 4px solid #667eea;">
                    <h4 style="color: #333; margin-bottom: 0.5rem;">Account Status</h4>
                    <p style="color: #666; margin: 0;">
                        <span class="badge badge-success">Active</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Account Actions -->
        <div class="card" style="margin-top: 2rem;">
            <h3 style="margin-bottom: 1.5rem; color: #333;">
                <i class="fas fa-cogs"></i> Account Actions
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="payments.php" class="btn btn-primary" style="text-decoration: none;">
                    <i class="fas fa-credit-card"></i> View Payment History
                </a>
                <a href="maintenance.php" class="btn btn-secondary" style="text-decoration: none;">
                    <i class="fas fa-tools"></i> Submit Maintenance Request
                </a>
                <a href="index.php" class="btn btn-success" style="text-decoration: none;">
                    <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                </a>
                <a href="../logout.php" class="btn btn-danger" style="text-decoration: none;" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Privacy Notice -->
        <div style="margin-top: 2rem; padding: 1rem; background: #e3f2fd; border-radius: 10px; border-left: 4px solid #2196f3;">
            <h4 style="color: #1976d2; margin-bottom: 0.5rem; font-size: 16px;">
                <i class="fas fa-shield-alt"></i> Privacy & Security
            </h4>
            <p style="color: #666; font-size: 14px; margin: 0;">
                Your personal information is protected and will only be used for dormitory management purposes. 
                We do not share your data with third parties. If you have any concerns about your privacy, 
                please contact the administration.
            </p>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword) {
        this.style.borderColor = '#ff6b6b';
    } else {
        this.style.borderColor = '#11998e';
    }
});

// Form validation before submit
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New passwords do not match!');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        alert('New password must be at least 6 characters long!');
        return false;
    }
});
</script>

<?php include '../includes/footer.php'; ?>