<?php
include '../includes/session_check_admin.php';
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle image uploads
function handle_upload($file) {
    $upload_dir = '../uploads/units/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if (isset($file) && $file['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $file['tmp_name'];
        $file_name = basename($file['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_extensions)) {
            return ['error' => 'Only JPG, JPEG, PNG, and GIF files are allowed.'];
        }
        
        $new_filename = 'unit_' . time() . '_' . uniqid() . '.' . $file_ext;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file_tmp, $upload_path)) {
            return ['success' => true, 'path' => $new_filename];
        } else {
            return ['error' => 'Failed to upload file.'];
        }
    }
    return ['success' => false]; // No file uploaded or error
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action == 'add_unit' || $action == 'edit_unit') {
            $unit_number = htmlspecialchars($_POST['unit_number']);
            $description = htmlspecialchars($_POST['description']);
            $monthly_rent = floatval($_POST['monthly_rent']);
            $status = htmlspecialchars($_POST['status']);
            $image_path = $_POST['existing_image'] ?? null;

            if (isset($_FILES['unit_image']) && $_FILES['unit_image']['error'] == UPLOAD_ERR_OK) {
                $upload_result = handle_upload($_FILES['unit_image']);
                if (isset($upload_result['error'])) {
                    $error_message = $upload_result['error'];
                } else {
                    $image_path = $upload_result['path'];
                }
            }

            if (!$error_message) {
                if ($action == 'add_unit') {
                    $stmt = $conn->prepare("INSERT INTO units (unit_number, description, monthly_rent, status, image_path) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssdss", $unit_number, $description, $monthly_rent, $status, $image_path);
                    if ($stmt->execute()) {
                        $success_message = "Unit added successfully!";
                    } else {
                        $error_message = "Failed to add unit: " . $conn->error;
                    }
                } elseif ($action == 'edit_unit') {
                    $unit_id = intval($_POST['unit_id']);
                    $stmt = $conn->prepare("UPDATE units SET unit_number = ?, description = ?, monthly_rent = ?, status = ?, image_path = ? WHERE id = ?");
                    $stmt->bind_param("ssdssi", $unit_number, $description, $monthly_rent, $status, $image_path, $unit_id);
                    if ($stmt->execute()) {
                        $success_message = "Unit updated successfully!";
                    } else {
                        $error_message = "Failed to update unit: " . $conn->error;
                    }
                }
            }
        } elseif ($action == 'delete_unit') {
            $unit_id = intval($_POST['unit_id']);
            
            // Optional: Also delete image file from server
            $stmt = $conn->prepare("SELECT image_path FROM units WHERE id = ?");
            $stmt->bind_param("i", $unit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if($row = $result->fetch_assoc()){
                if($row['image_path'] && file_exists('../uploads/units/' . $row['image_path'])){
                    unlink('../uploads/units/' . $row['image_path']);
                }
            }

            $delete_stmt = $conn->prepare("DELETE FROM units WHERE id = ?");
            $delete_stmt->bind_param("i", $unit_id);
            if ($delete_stmt->execute()) {
                $success_message = "Unit deleted successfully!";
            } else {
                $error_message = "Failed to delete unit. It might be assigned to tenants.";
            }
        }
    }
}

// Fetch all units
$units_query = "SELECT u.*, COUNT(t.id) as tenant_count
                FROM units u 
                LEFT JOIN tenants t ON u.id = t.unit_id 
                GROUP BY u.id
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
            <div style="background: #e8f5e8; color: #2e7d32; padding: 1rem; border-radius: 10px; margin-bottom: 2rem;"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 10px; margin-bottom: 2rem;"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div style="margin-bottom: 2rem;">
            <button onclick="openModal('addUnitModal')" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Unit</button>
        </div>

        <div class="unit-grid">
            <?php while ($unit = $units_result->fetch_assoc()): ?>
                <div class="unit-card">
                    <div class="unit-card-image">
                        <img src="<?php echo $unit['image_path'] ? '../uploads/units/' . htmlspecialchars($unit['image_path']) : 'https://via.placeholder.com/400x250/dee2e6/6c757d.png?text=No+Image'; ?>" alt="Unit <?php echo htmlspecialchars($unit['unit_number']); ?>">
                        <div class="unit-card-status badge badge-<?php echo $unit['status'] == 'Available' ? 'success' : ($unit['status'] == 'Occupied' ? 'primary' : 'warning'); ?>">
                            <?php echo htmlspecialchars($unit['status']); ?>
                        </div>
                    </div>
                    <div class="unit-card-content">
                        <h3 class="unit-card-title">Unit <?php echo htmlspecialchars($unit['unit_number']); ?></h3>
                        <p class="unit-card-description"><?php echo htmlspecialchars($unit['description']); ?></p>
                        <div class="unit-card-details">
                            <span><i class="fas fa-money-bill-wave"></i> ₱<?php echo number_format($unit['monthly_rent'], 2); ?>/mo</span>
                            <span><i class="fas fa-users"></i> <?php echo $unit['tenant_count']; ?> Tenant(s)</span>
                        </div>
                    </div>
                    <div class="unit-card-actions">
                        <button onclick="openEditUnitModal(<?php echo $unit['id']; ?>)" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i> Edit</button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this unit? This cannot be undone.')">
                            <input type="hidden" name="action" value="delete_unit">
                            <input type="hidden" name="unit_id" value="<?php echo $unit['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Unit Modals -->
<!-- Add Unit Modal -->
<div id="addUnitModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Unit</h2>
            <span class="close" onclick="closeModal('addUnitModal')">×</span>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_unit">
            <div class="form-group">
                <label>Unit Number</label><input type="text" name="unit_number" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label><textarea name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label>Monthly Rent (₱)</label><input type="number" name="monthly_rent" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="Available">Available</option>
                    <option value="Occupied">Occupied</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                </select>
            </div>
            <div class="form-group">
                <label>Unit Image</label><input type="file" name="unit_image" class="form-control" accept="image/*">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeModal('addUnitModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Unit</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Unit Modal -->
<div id="editUnitModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Unit</h2>
            <span class="close" onclick="closeModal('editUnitModal')">×</span>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_unit">
            <input type="hidden" name="unit_id" id="edit_unit_id">
            <input type="hidden" name="existing_image" id="edit_existing_image">
            <div class="form-group">
                <label>Unit Number</label><input type="text" id="edit_unit_number" name="unit_number" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label><textarea id="edit_description" name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label>Monthly Rent (₱)</label><input type="number" id="edit_monthly_rent" name="monthly_rent" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="edit_status" name="status" class="form-control" required>
                    <option value="Available">Available</option>
                    <option value="Occupied">Occupied</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                </select>
            </div>
            <div class="form-group">
                <label>Unit Image</label>
                <img id="edit_image_preview" src="" alt="Current Image" style="max-width: 100px; max-height: 100px; display: block; margin-bottom: 10px;">
                <input type="file" name="unit_image" class="form-control" accept="image/*">
                <small>Leave blank to keep the current image.</small>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeModal('editUnitModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Unit</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>