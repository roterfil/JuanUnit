<?php
include '../includes/session_check_admin.php';
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'update_status') {
        $request_id = intval($_POST['request_id']);
        $status = htmlspecialchars($_POST['status']);
        
        $stmt = $conn->prepare("UPDATE maintenance_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $request_id);
        
        if ($stmt->execute()) {
            $success_message = "Maintenance request status updated successfully!";
            
            // Notify the tenant
            $req_info_query = $conn->prepare("SELECT tenant_id, subject FROM maintenance_requests WHERE id = ?");
            $req_info_query->bind_param("i", $request_id);
            $req_info_query->execute();
            $request_info = $req_info_query->get_result()->fetch_assoc();
            $tenant_id = $request_info['tenant_id'];
            $subject = $request_info['subject'];

            require_once('../includes/notifications.php');
            $notification_message = "Your request '" . substr($subject, 0, 20) . "...' status is now: " . $status;
            create_notification($conn, $tenant_id, 'tenant', $notification_message, 'user/maintenance.php#request-' . $request_id);

        } else {
            $error_message = "Failed to update maintenance request status!";
        }
    }
}

// Fetch maintenance statistics
$pending_count_query = "SELECT COUNT(*) as count FROM maintenance_requests WHERE status = 'Pending'";
$pending_count = $conn->query($pending_count_query)->fetch_assoc()['count'];

$in_progress_count_query = "SELECT COUNT(*) as count FROM maintenance_requests WHERE status = 'In Progress'";
$in_progress_count = $conn->query($in_progress_count_query)->fetch_assoc()['count'];

$completed_count_query = "SELECT COUNT(*) as count FROM maintenance_requests WHERE status = 'Completed'";
$completed_count = $conn->query($completed_count_query)->fetch_assoc()['count'];

// Fetch all maintenance requests with tenant information
$maintenance_query = "SELECT mr.*, t.full_name, u.unit_number 
                      FROM maintenance_requests mr 
                      JOIN tenants t ON mr.tenant_id = t.id 
                      LEFT JOIN units u ON t.unit_id = u.id 
                      ORDER BY 
                        CASE mr.status 
                          WHEN 'Pending' THEN 1 
                          WHEN 'In Progress' THEN 2 
                          WHEN 'Completed' THEN 3 
                        END, 
                        mr.submitted_at DESC";
$maintenance_result = $conn->query($maintenance_query);

$page_title = "Maintenance Management";
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
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="units.php"><i class="fas fa-building"></i> Units</a></li>
            <li><a href="tenants.php"><i class="fas fa-users"></i> Tenants</a></li>
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="maintenance.php" class="active"><i class="fas fa-tools"></i> Maintenance</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Maintenance Management</h1>
            <p>Track and manage all maintenance requests</p>
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

        <!-- Maintenance Statistics -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <h3 style="color: #ffc107;"><?php echo $pending_count; ?></h3>
                <p>Pending Requests</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #667eea;"><?php echo $in_progress_count; ?></h3>
                <p>In Progress</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #11998e;"><?php echo $completed_count; ?></h3>
                <p>Completed</p>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div style="margin-bottom: 2rem; display: flex; gap: 1rem;">
            <button onclick="filterRequests('all')" class="btn btn-primary filter-btn active" data-filter="all">
                All Requests
            </button>
            <button onclick="filterRequests('Pending')" class="btn btn-secondary filter-btn" data-filter="Pending">
                Pending
            </button>
            <button onclick="filterRequests('In Progress')" class="btn btn-secondary filter-btn" data-filter="In Progress">
                In Progress
            </button>
            <button onclick="filterRequests('Completed')" class="btn btn-secondary filter-btn" data-filter="Completed">
                Completed
            </button>
        </div>

        <!-- Maintenance Requests -->
        <div style="display: grid; gap: 1.5rem;">
            <?php if ($maintenance_result->num_rows > 0): ?>
                <?php while ($request = $maintenance_result->fetch_assoc()): ?>
                    <div id="request-<?php echo $request['id']; ?>" class="card maintenance-request" data-status="<?php echo $request['status']; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <div style="flex: 1;">
                                <h3 style="color: #333; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($request['subject']); ?></h3>
                                <div style="display: flex; gap: 2rem; margin-bottom: 1rem; color: #666; font-size: 14px;">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($request['full_name']); ?></span>
                                    <?php if ($request['unit_number']): ?>
                                        <span><i class="fas fa-home"></i> Unit <?php echo htmlspecialchars($request['unit_number']); ?></span>
                                    <?php endif; ?>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y g:i A', strtotime($request['submitted_at'])); ?></span>
                                </div>
                            </div>
                            <span class="badge badge-<?php echo $request['status'] == 'Pending' ? 'warning' : ($request['status'] == 'In Progress' ? 'primary' : 'success'); ?>">
                                <?php echo $request['status']; ?>
                            </span>
                        </div>
                        
                        <p style="color: #666; line-height: 1.6; margin-bottom: 1.5rem; padding: 1rem; background: #f8f9ff; border-radius: 10px;">
                            <?php echo nl2br(htmlspecialchars($request['description'])); ?>
                        </p>
                        
                        <!-- *** REVISED and CORRECTED bottom row container *** -->
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            
                            <!-- Left side: View Image Button -->
                            <div>
                                <?php if ($request['image_path']): ?>
                                    <button class="btn btn-sm btn-secondary" onclick="openProofModal('../uploads/maintenance/<?php echo htmlspecialchars($request['image_path']); ?>')">
                                        <i class="fas fa-image"></i> View Attached Image
                                    </button>
                                <?php endif; ?>
                            </div>

                            <!-- Right side: Status Update Form -->
                            <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <label style="font-size: 14px; color: #666;">Status:</label>
                                <select name="status" onchange="this.form.submit()" class="form-control" style="width: auto; font-size: 14px;">
                                    <option value="Pending" <?php echo $request['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="In Progress" <?php echo $request['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Completed" <?php echo $request['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-tools" style="font-size: 64px; color: #ddd; margin-bottom: 1rem;"></i>
                    <h3 style="color: #999; margin-bottom: 1rem;">No Maintenance Requests</h3>
                    <p style="color: #666;">All caught up! No maintenance requests at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Reusing the proof modal for viewing maintenance images -->
<div id="proofModal" class="modal modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h2>View Attachment</h2>
            <span class="close" onclick="closeModal('proofModal')">×</span>
        </div>
        <div id="proofContent" style="padding: 1rem 0; text-align: center;">
            <!-- Content loaded by JavaScript -->
        </div>
    </div>
</div>

<script>
function filterRequests(status) {
    const requests = document.querySelectorAll('.maintenance-request');
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    // Update active button
    filterBtns.forEach(btn => {
        btn.classList.remove('active', 'btn-primary');
        btn.classList.add('btn-secondary');
    });
    
    document.querySelector(`[data-filter="${status}"]`).classList.remove('btn-secondary');
    document.querySelector(`[data-filter="${status}"]`).classList.add('btn-primary', 'active');
    
    // Show/hide requests
    requests.forEach(request => {
        if (status === 'all' || request.dataset.status === status) {
            request.style.display = 'block';
        } else {
            request.style.display = 'none';
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>