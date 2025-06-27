<?php
include '../includes/session_check_admin.php';
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'assign_unit') {
            $tenant_id = intval($_POST['tenant_id']);
            $unit_id = intval($_POST['unit_id']);
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Update tenant's unit assignment
                $stmt1 = $conn->prepare("UPDATE tenants SET unit_id = ? WHERE id = ?");
                $stmt1->bind_param("ii", $unit_id, $tenant_id);
                $stmt1->execute();
                
                // Update unit status to occupied
                $stmt2 = $conn->prepare("UPDATE units SET status = 'Occupied' WHERE id = ?");
                $stmt2->bind_param("i", $unit_id);
                $stmt2->execute();
                
                // Create initial payment record for this month
                $current_month = date('Y-m-01');
                $due_date = date('Y-m-05'); // Due on 5th of each month
                
                // Get unit rent
                $rent_stmt = $conn->prepare("SELECT monthly_rent FROM units WHERE id = ?");
                $rent_stmt->bind_param("i", $unit_id);
                $rent_stmt->execute();
                $rent_result = $rent_stmt->get_result();
                $rent = $rent_result->fetch_assoc()['monthly_rent'];
                
                $payment_stmt = $conn->prepare("INSERT INTO payments (tenant_id, amount, due_date) VALUES (?, ?, ?)");
                $payment_stmt->bind_param("ids", $tenant_id, $rent, $due_date);
                $payment_stmt->execute();
                
                $conn->commit();
                $success_message = "Tenant assigned to unit successfully!";
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Failed to assign tenant to unit!";
            }
        } elseif ($_POST['action'] == 'unassign_unit') {
            $tenant_id = intval($_POST['tenant_id']);
            
            // Get current unit ID before unassigning
            $unit_stmt = $conn->prepare("SELECT unit_id FROM tenants WHERE id = ?");
            $unit_stmt->bind_param("i", $tenant_id);
            $unit_stmt->execute();
            $unit_result = $unit_stmt->get_result();
            $current_unit_id = $unit_result->fetch_assoc()['unit_id'];
            
            if ($current_unit_id) {
                $conn->begin_transaction();
                
                try {
                    // Unassign tenant from unit
                    $stmt1 = $conn->prepare("UPDATE tenants SET unit_id = NULL WHERE id = ?");
                    $stmt1->bind_param("i", $tenant_id);
                    $stmt1->execute();
                    
                    // Update unit status to available
                    $stmt2 = $conn->prepare("UPDATE units SET status = 'Available' WHERE id = ?");
                    $stmt2->bind_param("i", $current_unit_id);
                    $stmt2->execute();
                    
                    $conn->commit();
                    $success_message = "Tenant unassigned from unit successfully!";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error_message = "Failed to unassign tenant from unit!";
                }
            }
        }
    }
}

// Fetch all tenants with their unit information
$tenants_query = "SELECT t.*, u.unit_number, u.monthly_rent 
                  FROM tenants t 
                  LEFT JOIN units u ON t.unit_id = u.id 
                  ORDER BY t.full_name";
$tenants_result = $conn->query($tenants_query);

// Fetch available units for assignment
$available_units_query = "SELECT id, unit_number, monthly_rent FROM units WHERE status = 'Available' ORDER BY unit_number";
$available_units_result = $conn->query($available_units_query);

$page_title = "Tenants Management";
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
            <li><a href="tenants.php" class="active"><i class="fas fa-users"></i> Tenants</a></li>
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Tenants Management</h1>
            <p>Manage tenant assignments and unit allocations</p>
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

        <!-- Search Bar -->
        <div style="margin-bottom: 2rem;">
            <input type="text" id="searchInput" placeholder="Search tenants..." class="form-control" style="max-width: 400px;" onkeyup="searchTable('searchInput', 'tenantsTable')">
        </div>

        <!-- Tenants Table -->
        <div class="table-container">
            <table class="table" id="tenantsTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Unit</th>
                        <th>Monthly Rent</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($tenant = $tenants_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tenant['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($tenant['email']); ?></td>
                            <td><?php echo htmlspecialchars($tenant['phone_number'] ?: 'N/A'); ?></td>
                            <td>
                                <?php if ($tenant['unit_number']): ?>
                                    <span class="badge badge-primary">Unit <?php echo htmlspecialchars($tenant['unit_number']); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($tenant['monthly_rent']): ?>
                                    ₱<?php echo number_format($tenant['monthly_rent'], 2); ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($tenant['registration_date'])); ?></td>
                            <td>
                                <?php if ($tenant['unit_id']): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to unassign this tenant?')">
                                        <input type="hidden" name="action" value="unassign_unit">
                                        <input type="hidden" name="tenant_id" value="<?php echo $tenant['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i> Unassign
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button onclick="openAssignModal(<?php echo $tenant['id']; ?>, '<?php echo htmlspecialchars($tenant['full_name']); ?>')" class="btn btn-sm btn-success">
                                        <i class="fas fa-home"></i> Assign Unit
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Assign Unit Modal -->
<div id="assignUnitModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Assign Unit to <span id="tenantName"></span></h2>
            <span class="close" onclick="closeModal('assignUnitModal')">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="assign_unit">
            <input type="hidden" name="tenant_id" id="assignTenantId">
            
            <div class="form-group">
                <label for="unit_id">Select Available Unit</label>
                <select id="unit_id" name="unit_id" class="form-control" required>
                    <option value="">Choose a unit...</option>
                    <?php 
                    $available_units_result->data_seek(0); // Reset result pointer
                    while ($unit = $available_units_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $unit['id']; ?>">
                            Unit <?php echo htmlspecialchars($unit['unit_number']); ?> - ₱<?php echo number_format($unit['monthly_rent'], 2); ?>/month
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" onclick="closeModal('assignUnitModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign Unit</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignModal(tenantId, tenantName) {
    document.getElementById('assignTenantId').value = tenantId;
    document.getElementById('tenantName').textContent = tenantName;
    openModal('assignUnitModal');
}
</script>

<?php include '../includes/footer.php'; ?>