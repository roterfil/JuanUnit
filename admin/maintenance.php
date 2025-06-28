<?php
include '../includes/session_check_admin.php';
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle form submissions for status, archive, and unarchive
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? null;
    $request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;

    if ($action == 'update_status' && $request_id > 0) {
        $status = htmlspecialchars($_POST['status']);
        $stmt = $conn->prepare("UPDATE maintenance_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $request_id);
        
        if ($stmt->execute()) {
            $success_message = "Status updated successfully!";
            // Notify tenant
            $req_info_query = $conn->prepare("SELECT tenant_id, subject FROM maintenance_requests WHERE id = ?");
            $req_info_query->bind_param("i", $request_id);
            $req_info_query->execute();
            $request_info = $req_info_query->get_result()->fetch_assoc();
            require_once('../includes/notifications.php');
            $notification_message = "Your request '" . substr($request_info['subject'], 0, 20) . "...' status is now: " . $status;
            create_notification($conn, $request_info['tenant_id'], 'tenant', $notification_message, 'user/maintenance.php#request-' . $request_id);
        } else {
            $error_message = "Failed to update status!";
        }
    } elseif ($action == 'archive_request' && $request_id > 0) {
        $stmt = $conn->prepare("UPDATE maintenance_requests SET is_archived = 1 WHERE id = ? AND status = 'Completed'");
        $stmt->bind_param("i", $request_id);
        if ($stmt->execute()) {
            $success_message = "Request archived successfully!";
        } else {
            $error_message = "Failed to archive request.";
        }
    } elseif ($action == 'unarchive_request' && $request_id > 0) {
        $stmt = $conn->prepare("UPDATE maintenance_requests SET is_archived = 0 WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        if ($stmt->execute()) {
            $success_message = "Request unarchived successfully!";
        } else {
            $error_message = "Failed to unarchive request.";
        }
    }
}

// Fetch maintenance statistics (only for non-archived items)
$pending_count = $conn->query("SELECT COUNT(*) as count FROM maintenance_requests WHERE status = 'Pending' AND is_archived = 0")->fetch_assoc()['count'];
$in_progress_count = $conn->query("SELECT COUNT(*) as count FROM maintenance_requests WHERE status = 'In Progress' AND is_archived = 0")->fetch_assoc()['count'];
$completed_count = $conn->query("SELECT COUNT(*) as count FROM maintenance_requests WHERE status = 'Completed' AND is_archived = 0")->fetch_assoc()['count'];

// --- Get filter and search parameters ---
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// --- Build the dynamic query ---
$base_query = "SELECT mr.*, t.full_name, u.unit_number 
               FROM maintenance_requests mr 
               JOIN tenants t ON mr.tenant_id = t.id 
               LEFT JOIN units u ON t.unit_id = u.id";
$where_clauses = [];
$params = [];
$param_types = '';

if ($filter == 'Archived') {
    $where_clauses[] = "mr.is_archived = 1";
} else {
    $where_clauses[] = "mr.is_archived = 0";
    if (in_array($filter, ['Pending', 'In Progress', 'Completed'])) {
        $where_clauses[] = "mr.status = ?";
        $params[] = $filter;
        $param_types .= 's';
    }
}

if (!empty($search)) {
    $where_clauses[] = "(mr.subject LIKE ? OR mr.description LIKE ? OR t.full_name LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
}

$query = $base_query;
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}
$query .= " ORDER BY mr.submitted_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$maintenance_result = $stmt->get_result();


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
            <div style="background: #e8f5e8; color: #2e7d32; padding: 1rem; border-radius: 10px; margin-bottom: 2rem;"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 10px; margin-bottom: 2rem;"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Maintenance Statistics -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <h3 style="color: #ffc107;"><?php echo $pending_count; ?></h3><p>Pending Requests</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #667eea;"><?php echo $in_progress_count; ?></h3><p>In Progress</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #11998e;"><?php echo $completed_count; ?></h3><p>Completed</p>
            </div>
        </div>

        <!-- Filters and Search Bar -->
        <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <!-- Filter Buttons -->
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <?php
                    $filters = ['all' => 'All Requests', 'Pending' => 'Pending', 'In Progress' => 'In Progress', 'Completed' => 'Completed', 'Archived' => 'Archived'];
                    foreach ($filters as $key => $value):
                        $is_active = ($filter == $key);
                        $btn_class = $is_active ? 'btn-primary' : 'btn-secondary';
                    ?>
                        <a href="?filter=<?php echo $key; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-sm <?php echo $btn_class; ?>"><?php echo $value; ?></a>
                    <?php endforeach; ?>
                </div>
                <!-- Search Bar -->
                <div style="flex-grow: 1; max-width: 300px;">
                    <form method="GET" style="display: flex; gap: 0.5rem;">
                        <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                        <input type="text" name="search" placeholder="Search requests..." class="form-control" value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                        <?php if(!empty($search)): ?>
                            <a href="?filter=<?php echo htmlspecialchars($filter); ?>" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Maintenance Requests Grid -->
        <div class="unit-grid">
            <?php if ($maintenance_result->num_rows > 0): ?>
                <?php while ($request = $maintenance_result->fetch_assoc()): ?>
                    <div id="request-<?php echo $request['id']; ?>" class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <h3 style="color: #333; margin:0; font-size: 1.2rem;"><?php echo htmlspecialchars($request['subject']); ?></h3>
                                <span class="badge badge-<?php echo $request['status'] == 'Pending' ? 'warning' : ($request['status'] == 'In Progress' ? 'primary' : 'success'); ?>">
                                    <?php echo $request['status']; ?>
                                </span>
                            </div>
                            <div style="color: #666; font-size: 13px; margin-bottom: 1rem; display: flex; flex-wrap: wrap; gap: 1rem;">
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($request['full_name']); ?></span>
                                <?php if ($request['unit_number']): ?>
                                    <span><i class="fas fa-home"></i> Unit <?php echo htmlspecialchars($request['unit_number']); ?></span>
                                <?php endif; ?>
                                <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($request['submitted_at'])); ?></span>
                            </div>
                            <p style="color: #666; line-height: 1.6; margin-bottom: 1.5rem; padding: 1rem; background: #f8f9ff; border-radius: 10px; min-height: 60px;">
                                <?php echo nl2br(htmlspecialchars($request['description'])); ?>
                            </p>
                        </div>
                        
                        <div style="border-top: 1px solid #e1e5e9; padding-top: 1rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                            <!-- Left side actions -->
                            <div>
                                <?php if ($request['image_path']): ?>
                                    <button class="btn btn-sm btn-secondary" onclick="openProofModal('../uploads/maintenance/<?php echo htmlspecialchars($request['image_path']); ?>')">
                                        <i class="fas fa-image"></i> View Image
                                    </button>
                                <?php endif; ?>
                            </div>

                            <!-- Right side actions -->
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <?php if ($filter == 'Archived'): ?>
                                    <form method="POST"><input type="hidden" name="action" value="unarchive_request"><input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-box-open"></i> Unarchive</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="hidden" name="action" value="update_status"><input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="form-control" style="width: auto; font-size: 14px;">
                                            <option value="Pending" <?php echo $request['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="In Progress" <?php echo $request['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="Completed" <?php echo $request['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                    </form>
                                    <?php if ($request['status'] == 'Completed'): ?>
                                        <form method="POST" onsubmit="return confirm('Archive this request? It will be hidden from the main view.')"><input type="hidden" name="action" value="archive_request"><input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-archive"></i> Archive</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 3rem; grid-column: 1 / -1;">
                    <i class="fas fa-search" style="font-size: 64px; color: #ddd; margin-bottom: 1rem;"></i>
                    <h3 style="color: #999; margin-bottom: 1rem;">No Maintenance Requests Found</h3>
                    <p style="color: #666;">No requests match your current filters. Try adjusting your search or filter settings.</p>
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
        <div id="proofContent" style="padding: 1rem 0; text-align: center;"></div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>