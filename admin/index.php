<?php
include '../includes/session_check_admin.php';
require_once '../includes/db_connect.php';

// Fetch dashboard statistics
$total_tenants_query = "SELECT COUNT(*) as count FROM tenants";
$total_tenants = $conn->query($total_tenants_query)->fetch_assoc()['count'];

$occupied_units_query = "SELECT COUNT(*) as count FROM units WHERE status = 'Occupied'";
$occupied_units = $conn->query($occupied_units_query)->fetch_assoc()['count'];

$pending_payments_query = "SELECT COUNT(*) as count FROM payments WHERE status = 'Unpaid'";
$pending_payments = $conn->query($pending_payments_query)->fetch_assoc()['count'];

$maintenance_requests_query = "SELECT COUNT(*) as count FROM maintenance_requests WHERE status = 'Pending'";
$maintenance_requests = $conn->query($maintenance_requests_query)->fetch_assoc()['count'];

// Recent announcements
$recent_announcements_query = "SELECT title, created_at FROM announcements ORDER BY created_at DESC LIMIT 5";
$recent_announcements = $conn->query($recent_announcements_query);

// Recent maintenance requests
$recent_maintenance_query = "SELECT mr.subject, t.full_name, mr.submitted_at, mr.status 
                            FROM maintenance_requests mr 
                            JOIN tenants t ON mr.tenant_id = t.id 
                            ORDER BY mr.submitted_at DESC LIMIT 5";
$recent_maintenance = $conn->query($recent_maintenance_query);

$page_title = "Admin Dashboard";
$css_path = "../css/style.css";
$js_path = "../js/script.js";
include '../includes/header.php';
?>

<div class="dashboard">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Admin Panel</h3>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="units.php"><i class="fas fa-building"></i> Units</a></li>
            <li><a href="tenants.php"><i class="fas fa-users"></i> Tenants</a></li>
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Dashboard Overview</h1>
            <p>Monitor your dormitory operations at a glance</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_tenants; ?></h3>
                <p>Total Tenants</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $occupied_units; ?></h3>
                <p>Occupied Units</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $pending_payments; ?></h3>
                <p>Pending Payments</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $maintenance_requests; ?></h3>
                <p>Open Maintenance</p>
            </div>
        </div>

        <!-- Recent Activities -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
            <!-- Recent Announcements -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3>Recent Announcements</h3>
                    <a href="announcements.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <?php if ($recent_announcements->num_rows > 0): ?>
                    <?php while ($announcement = $recent_announcements->fetch_assoc()): ?>
                        <div style="padding: 1rem; border-left: 4px solid #667eea; margin-bottom: 1rem; background: #f8f9ff;">
                            <h4 style="margin-bottom: 0.5rem; color: #333;"><?php echo htmlspecialchars($announcement['title']); ?></h4>
                            <p style="color: #666; font-size: 14px; margin: 0;">
                                <?php echo date('M j, Y g:i A', strtotime($announcement['created_at'])); ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #666; text-align: center; padding: 2rem;">No announcements yet.</p>
                <?php endif; ?>
            </div>

            <!-- Recent Maintenance Requests -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3>Recent Maintenance</h3>
                    <a href="maintenance.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <?php if ($recent_maintenance->num_rows > 0): ?>
                    <?php while ($request = $recent_maintenance->fetch_assoc()): ?>
                        <div style="padding: 1rem; border-left: 4px solid #ff6b6b; margin-bottom: 1rem; background: #fff5f5;">
                            <h4 style="margin-bottom: 0.5rem; color: #333;"><?php echo htmlspecialchars($request['subject']); ?></h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 0.5rem;">
                                By: <?php echo htmlspecialchars($request['full_name']); ?>
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="badge badge-<?php echo $request['status'] == 'Pending' ? 'warning' : ($request['status'] == 'In Progress' ? 'primary' : 'success'); ?>">
                                    <?php echo $request['status']; ?>
                                </span>
                                <span style="color: #999; font-size: 12px;">
                                    <?php echo date('M j, Y', strtotime($request['submitted_at'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #666; text-align: center; padding: 2rem;">No maintenance requests yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="margin-top: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="units.php" class="btn btn-primary" style="text-decoration: none;">
                    <i class="fas fa-plus"></i> Add New Unit
                </a>
                <a href="tenants.php" class="btn btn-secondary" style="text-decoration: none;">
                    <i class="fas fa-user-plus"></i> Manage Tenants
                </a>
                <a href="announcements.php" class="btn btn-success" style="text-decoration: none;">
                    <i class="fas fa-bullhorn"></i> Post Announcement
                </a>
                <a href="payments.php" class="btn btn-primary" style="text-decoration: none;">
                    <i class="fas fa-money-check"></i> View Payments
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>