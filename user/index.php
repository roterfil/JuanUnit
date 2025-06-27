<?php
include '../includes/session_check_user.php';
require_once '../includes/db_connect.php';

// Fetch tenant information with unit details
$tenant_query = "SELECT t.*, u.unit_number, u.description, u.monthly_rent, u.status 
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

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Unit Information -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem; color: #333;">
                    <i class="fas fa-home"></i> My Unit Details
                </h3>
                
                <?php if ($tenant_info['unit_number']): ?>
                    <div style="display: grid; gap: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8f9ff; border-radius: 10px;">
                            <span style="font-weight: 600; color: #333;">Unit Number:</span>
                            <span style="color: #667eea; font-weight: 600; font-size: 1.2rem;">
                                <?php echo htmlspecialchars($tenant_info['unit_number']); ?>
                            </span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8f9ff; border-radius: 10px;">
                            <span style="font-weight: 600; color: #333;">Monthly Rent:</span>
                            <span style="color: #11998e; font-weight: 600; font-size: 1.2rem;">
                                ₱<?php echo number_format($tenant_info['monthly_rent'], 2); ?>
                            </span>
                        </div>
                        
                        <div style="padding: 1rem; background: #f8f9ff; border-radius: 10px;">
                            <span style="font-weight: 600; color: #333; display: block; margin-bottom: 0.5rem;">Description:</span>
                            <p style="color: #666; margin: 0; line-height: 1.6;">
                                <?php echo htmlspecialchars($tenant_info['description']); ?>
                            </p>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8f9ff; border-radius: 10px;">
                            <span style="font-weight: 600; color: #333;">Status:</span>
                            <span class="badge badge-<?php echo $tenant_info['status'] == 'Occupied' ? 'primary' : 'warning'; ?>">
                                <?php echo htmlspecialchars($tenant_info['status']); ?>
                            </span>
                        </div>

                        <?php if ($next_payment): ?>
                            <div style="padding: 1rem; background: <?php echo $next_payment['due_date'] < date('Y-m-d') ? '#ffebee' : '#e8f5e8'; ?>; border-radius: 10px; border-left: 4px solid <?php echo $next_payment['due_date'] < date('Y-m-d') ? '#ff6b6b' : '#11998e'; ?>;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 600; color: #333;">Next Payment Due:</span>
                                    <div style="text-align: right;">
                                        <div style="font-weight: 600; color: <?php echo $next_payment['due_date'] < date('Y-m-d') ? '#c62828' : '#2e7d32'; ?>;">
                                            ₱<?php echo number_format($next_payment['amount'], 2); ?>
                                        </div>
                                        <div style="font-size: 14px; color: #666;">
                                            <?php 
                                            $due_date = date('M j, Y', strtotime($next_payment['due_date']));
                                            if ($next_payment['due_date'] < date('Y-m-d')) {
                                                echo $due_date . ' (Overdue)';
                                            } else {
                                                echo $due_date;
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                        <a href="payments.php" class="btn btn-primary">
                            <i class="fas fa-credit-card"></i> View Payments
                        </a>
                        <a href="maintenance.php" class="btn btn-secondary">
                            <i class="fas fa-tools"></i> Report Issue
                        </a>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; background: #fff5f5; border-radius: 10px; border: 2px dashed #ffcdd2;">
                        <i class="fas fa-home" style="font-size: 48px; color: #ddd; margin-bottom: 1rem;"></i>
                        <h4 style="color: #666; margin-bottom: 1rem;">No Unit Assigned</h4>
                        <p style="color: #999;">You haven't been assigned to a unit yet. Please contact the administration for unit assignment.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Latest Announcements -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem; color: #333;">
                    <i class="fas fa-bullhorn"></i> Latest Announcements
                </h3>
                
                <?php if ($announcements_result->num_rows > 0): ?>
                    <div style="display: grid; gap: 1rem;">
                        <?php while ($announcement = $announcements_result->fetch_assoc()): ?>
                            <div style="padding: 1rem; border-left: 4px solid #667eea; background: #f8f9ff; border-radius: 0 10px 10px 0;">
                                <h4 style="color: #333; font-size: 16px; margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($announcement['title']); ?>
                                </h4>
                                <p style="color: #666; font-size: 14px; margin-bottom: 0.5rem; line-height: 1.5;">
                                    <?php 
                                    $content = htmlspecialchars($announcement['content']);
                                    echo strlen($content) > 100 ? substr($content, 0, 100) . '...' : $content;
                                    ?>
                                </p>
                                <div style="color: #999; font-size: 12px;">
                                    <?php echo date('M j, Y', strtotime($announcement['created_at'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-bullhorn" style="font-size: 32px; color: #ddd; margin-bottom: 1rem;"></i>
                        <p style="color: #999; margin: 0;">No announcements yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>