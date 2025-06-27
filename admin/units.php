<?php
include '../includes/session_check_admin.php';
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_unit') {
            $unit_number = htmlspecialchars($_POST['unit_number']);
            $description = htmlspecialchars($_POST['description']);
            $monthly_rent = floatval($_POST['monthly_rent']);
            
            // Check if unit number already exists
            $check_stmt = $conn->prepare("SELECT id FROM units WHERE unit_number = ?");
            $check_stmt->bind_param("s", $unit_number);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error_message = "Unit number already exists!";
            } else {
                $stmt = $conn->prepare("INSERT INTO units (unit_number, description, monthly_rent) VALUES (?, ?, ?)");
                $stmt->bind_param("ssd", $unit_number, $description, $monthly_rent);
                
                if ($stmt->execute()) {
                    $success_message = "Unit added successfully!";
                } else {
                    $error_message = "Failed to add unit!";
                }
            }
        } elseif ($_POST['action'] == 'update_status') {
            $unit_id = intval($_POST['unit_id']);
            $status = htmlspecialchars($_POST['status']);
            
            $stmt = $conn->prepare("UPDATE units SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $unit_id);
            
            if ($stmt->execute()) {
                $success_message = "Unit status updated successfully!";
            } else {
                $error_message = "Failed to update unit status!";
            }
        }
    }
}

// Fetch all units
$units_query = "SELECT u.*, t.full_name as tenant_name 
                FROM units u 
                LEFT JOIN tenants t ON u.id = t.unit_id 
                ORDER BY u.unit_number";
$units_result = $conn->query($units_query);

$page_title = "Units Management";
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
            <li><a href="units.php" class="active"><i class="fas fa-building"></i> Units</a></li>
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
            <h1>Units Management</h1>
            <p>Manage all dormitory units and their status</p>
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

        <!-- Add Unit Button -->
        <div style="margin-bottom: 2rem;">
            <button onclick="openModal('addUnitModal')" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Unit
            </button>
        </div>

        <!-- Units Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem;">
            <?php while ($unit = $units_result->fetch_assoc()): ?>
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <h3 style="color: #333;">Unit <?php echo htmlspecialchars($unit['unit_number']); ?></h3>
                        <span class="badge badge-<?php echo $unit['status'] == 'Available' ? 'success' : ($unit['status'] == 'Occupied' ? 'primary' : 'warning'); ?>">
                            <?php echo $unit['status']; ?>
                        </span>
                    </div>
                    
                    <p style="color: #666; margin-bottom: 1rem;"><?php echo htmlspecialchars($unit['description']); ?></p>
                    
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #667eea;">₱<?php echo number_format($unit['monthly_rent'], 2); ?></strong>
                        <span style="color: #666;"> / month</span>
                    </div>
                    
                    <?php if ($unit['tenant_name']): ?>
                        <p style="color: #333; margin-bottom: 1rem;">
                            <i class="fas fa-user"></i> <strong>Tenant:</strong> <?php echo htmlspecialchars($unit['tenant_name']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <form method="POST" style="flex: 1;">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="unit_id" value="<?php echo $unit['id']; ?>">
                            <select name="status" onchange="this.form.submit()" class="form-control" style="font-size: 14px;">
                                <option value="Available" <?php echo $unit['status'] == 'Available' ? 'selected' : ''; ?>>Available</option>
                                <option value="Occupied" <?php echo $unit['status'] == 'Occupied' ? 'selected' : ''; ?>>Occupied</option>
                                <option value="Under Maintenance" <?php echo $unit['status'] == 'Under Maintenance' ? 'selected' : ''; ?>>Under Maintenance</option>
                            </select>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Add Unit Modal -->
<div id="addUnitModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Unit</h2>
            <span class="close" onclick="closeModal('addUnitModal')">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add_unit">
            
            <div class="form-group">
                <label for="unit_number">Unit Number</label>
                <input type="text" id="unit_number" name="unit_number" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="monthly_rent">Monthly Rent (₱)</label>
                <input type="number" id="monthly_rent" name="monthly_rent" class="form-control" step="0.01" min="0" required>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeModal('addUnitModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Unit</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>