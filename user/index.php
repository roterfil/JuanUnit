<?php
include '../includes/session_check_user.php';
require_once '../includes/db_connect.php';

// Fetch tenant information with unit details, including image_path
$tenant_query = "SELECT t.*, u.unit_number, u.description, u.monthly_rent, u.status, u.image_path 
                 FROM tenants t 
                 LEFT JOIN units u ON t.unit_id = u.id 
                 WHERE t.id = ?";
$stmt = $conn->prepare($tenant_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$tenant_info = $stmt->get_result()->fetch_assoc();

// Fetch recent announcements (last 3)
$announcements_query = "SELECT title, content, created_at FROM announcements ORDER BY created_at DESC LIMIT 3";
$announcements_result = $conn->query($announcements_query);

// Fetch pending payments
$pending_payments_query = "SELECT COUNT(*) as count FROM payments WHERE tenant_id = ? AND status = 'Unpaid'";
$stmt = $conn->prepare($pending_payments_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$pending_payments = $stmt->get_result()->fetch_assoc()['count'];

// Fetch open maintenance requests
$open_maintenance_query = "SELECT COUNT(*) as count FROM maintenance_requests WHERE tenant_id = ? AND status != 'Completed'";
$stmt = $conn->prepare($open_maintenance_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$open_maintenance = $stmt->get_result()->fetch_assoc()['count'];

// Fetch next payment due
$next_payment_query = "SELECT amount, due_date FROM payments WHERE tenant_id = ? AND status = 'Unpaid' ORDER BY due_date ASC LIMIT 1";
$stmt = $conn->prepare($next_payment_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$next_payment_result = $stmt->get_result();
$next_payment = $next_payment_result->num_rows > 0 ? $next_payment_result->fetch_assoc() : null;

$page_title = "My Dashboard";
$css_path = "../css/style.css";
$js_path = "../js/script.js";
include '../includes/header.php';
?>

<div class="dashboard">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Tenant Portal</h3>
            <p>Welcome, <?php echo htmlspecialchars($tenant_info['full_name']); ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> My Payments</a></li>
            <li><a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>My Dashboard</h1>
            <p>Overview of your dormitory stay and account status</p>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <h3 style="color: <?php echo $pending_payments > 0 ? '#ff6b6b' : '#11998e'; ?>;"><?php echo $pending_payments; ?></h3>
                <p>Pending Payments</p>
            </div>
            <div class="stat-card">
                <h3 style="color: <?php echo $open_maintenance > 0 ? '#ffc107' : '#11998e'; ?>;"><?php echo $open_maintenance; ?></h3>
                <p>Open Maintenance</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #667eea;">
                    <?php echo $tenant_info['unit_number'] ? 'Unit ' . htmlspecialchars($tenant_info['unit_number']) : 'No Unit'; ?>
                </h3>
                <p>Current Unit</p>
            </div>
        </div>

        <!-- === AESTHETIC REVAMP - GRID ADJUSTMENT === -->
        <div style="display: grid; grid-template-columns: 1fr 1.3fr; gap: 2rem; align-items: flex-start;">

            <!-- Left Column: Revamped Unit Card -->
            <div style="grid-column: 1; grid-row: 1;">
                <h3 style="margin-bottom: 1.5rem; font-size: 1.5rem;"><i class="fas fa-home" style="color: #667eea; margin-right: 0.5rem;"></i> My Unit</h3>
                
                <?php if ($tenant_info['unit_number']): ?>
                    <div class="unit-card">
                        <div class="unit-card-image">
                            <img src="<?php echo $tenant_info['image_path'] ? '../uploads/units/' . htmlspecialchars($tenant_info['image_path']) : 'https://via.placeholder.com/400x250/dee2e6/6c757d.png?text=My+Unit'; ?>" alt="Unit <?php echo htmlspecialchars($tenant_info['unit_number']); ?>">
                            <div class="unit-card-status badge badge-<?php echo $tenant_info['status'] == 'Occupied' ? 'primary' : 'warning'; ?>">
                                <?php echo htmlspecialchars($tenant_info['status']); ?>
                            </div>
                        </div>
                        <div class="unit-card-content">
                            <h3 class="unit-card-title">Unit <?php echo htmlspecialchars($tenant_info['unit_number']); ?></h3>
                            <p class="unit-card-description"><?php echo htmlspecialchars($tenant_info['description']); ?></p>
                            <div class="unit-card-details">
                                <span><i class="fas fa-money-bill-wave"></i> ₱<?php echo number_format($tenant_info['monthly_rent'], 2); ?> / month</span>
                            </div>
                        </div>
                        <div class="unit-card-actions" style="display: block; padding: 1.5rem;">
                            <!-- Next Payment Due Banner (Integrated) -->
                            <?php if ($next_payment): ?>
                                <div style="padding: 1rem; margin-bottom: 1rem; background: <?php echo $next_payment['due_date'] < date('Y-m-d') ? '#fff5f5' : '#f0fff4'; ?>; border-radius: 10px; border-left: 4px solid <?php echo $next_payment['due_date'] < date('Y-m-d') ? '#ff6b6b' : '#2e7d32'; ?>;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-weight: 600; color: #333;">Next Payment:</span>
                                        <div style="text-align: right;">
                                            <div style="font-weight: 700; font-size: 1.1rem; color: <?php echo $next_payment['due_date'] < date('Y-m-d') ? '#c62828' : '#2e7d32'; ?>;">
                                                ₱<?php echo number_format($next_payment['amount'], 2); ?>
                                            </div>
                                            <div style="font-size: 14px; color: #666;">
                                                Due: <?php echo date('M j, Y', strtotime($next_payment['due_date'])) . ($next_payment['due_date'] < date('Y-m-d') ? ' (Overdue)' : ''); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <!-- Action Buttons -->
                            <div style="display: flex; gap: 1rem;">
                                <a href="payments.php" class="btn btn-primary" style="flex: 1;"><i class="fas fa-credit-card"></i> My Payments</a>
                                <a href="maintenance.php" class="btn btn-secondary" style="flex: 1;"><i class="fas fa-tools"></i> Report Issue</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card" style="text-align: center; padding: 3rem; background: #fff5f5; border: 2px dashed #ffcdd2;">
                        <i class="fas fa-home" style="font-size: 48px; color: #e0e0e0; margin-bottom: 1rem;"></i>
                        <h4 style="color: #666; margin-bottom: 1rem;">No Unit Assigned</h4>
                        <p style="color: #999;">Please contact the administration to be assigned to a unit.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Revamped Announcements -->
            <div style="grid-column: 2; grid-row: 1;">
                <h3 style="margin-bottom: 1.5rem; font-size: 1.5rem;"><i class="fas fa-bullhorn" style="color: #667eea; margin-right: 0.5rem;"></i> Latest Announcements</h3>
                
                <?php if ($announcements_result->num_rows > 0): ?>
                    <div style="display: grid; gap: 1.5rem;">
                        <?php while ($announcement = $announcements_result->fetch_assoc()): ?>
                            <div class="card" style="padding: 1.5rem; background: #f8f9ff; border-left: 4px solid #667eea;">
                                <h4 style="font-size: 1.1rem; color: #333; margin-bottom: 0.75rem;">
                                    <?php echo htmlspecialchars($announcement['title']); ?>
                                </h4>
                                <p style="font-size: 0.95rem; color: #555; line-height: 1.7; margin-bottom: 1rem;">
                                    <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                                </p>
                                <div style="font-size: 0.8rem; color: #999; text-align: right; border-top: 1px solid #e1e5e9; padding-top: 0.75rem;">
                                    <i class="fas fa-calendar-alt"></i> Posted on <?php echo date('M j, Y', strtotime($announcement['created_at'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="card" style="text-align: center; padding: 3rem;">
                        <i class="fas fa-info-circle" style="font-size: 48px; color: #e0e0e0; margin-bottom: 1rem;"></i>
                        <h4 style="color: #999;">No New Announcements</h4>
                        <p style="color: #aaa;">It's all quiet for now!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- === AESTHETIC REVAMP ENDS HERE === -->
    </div>
</div>

<?php include '../includes/footer.php'; ?>