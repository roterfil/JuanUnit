<?php
include '../includes/session_check_admin.php';
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'mark_paid') {
            $payment_id = intval($_POST['payment_id']);
            $payment_date = date('Y-m-d');
            
            $stmt = $conn->prepare("UPDATE payments SET status = 'Paid', payment_date = ? WHERE id = ?");
            $stmt->bind_param("si", $payment_date, $payment_id);
            
            if ($stmt->execute()) {
                $success_message = "Payment marked as paid successfully!";
                // *** NEW: Notify the tenant ***
                $tenant_id_query = $conn->prepare("SELECT tenant_id, amount FROM payments WHERE id = ?");
                $tenant_id_query->bind_param("i", $payment_id);
                $tenant_id_query->execute();
                $payment_info = $tenant_id_query->get_result()->fetch_assoc();
                $tenant_id = $payment_info['tenant_id'];
                $amount = $payment_info['amount'];
                require_once('../includes/notifications.php');
                $notification_message = "Your payment of ₱" . number_format($amount, 2) . " has been confirmed.";
                create_notification($conn, $tenant_id, 'tenant', $notification_message, 'user/payments.php#payment-' . $payment_id);
            } else {
                $error_message = "Failed to update payment status!";
            }
        } else        if ($_POST['action'] == 'add_payment') {
            $unit_id = intval($_POST['unit_id']);
            $tenant_ids = $_POST['tenant_ids']; // Array of tenant IDs
            $amount = floatval($_POST['amount']);
            $due_date = $_POST['due_date'];
            
            $success_count = 0;
            $total_tenants = count($tenant_ids);
            
            foreach ($tenant_ids as $tenant_id) {
                $tenant_id = intval($tenant_id);
                $stmt = $conn->prepare("INSERT INTO payments (tenant_id, amount, due_date) VALUES (?, ?, ?)");
                $stmt->bind_param("ids", $tenant_id, $amount, $due_date);
                
            if ($stmt->execute()) {
                $new_payment_id = $conn->insert_id; // Get the new payment ID
                $success_count++;

                // Notify the tenant about the new bill with a specific link
                require_once('../includes/notifications.php');
                $formatted_due_date = date('M j, Y', strtotime($due_date));
                $notification_message = "A new payment of ₱" . number_format($amount, 2) . " is due on " . $formatted_due_date . ".";
                create_notification($conn, $tenant_id, 'tenant', $notification_message, 'user/payments.php#payment-' . $new_payment_id);
            }
            
            if ($success_count == $total_tenants) {
                $success_message = "Payment records added successfully for all selected tenants!";
            } elseif ($success_count > 0) {
                $success_message = "Payment records added for $success_count out of $total_tenants tenants.";
            } else {
                $error_message = "Failed to add payment records!";
            }
        }
    }
}
}

// Fetch payment statistics
$total_payments_query = "SELECT SUM(amount) as total FROM payments WHERE status = 'Paid'";
$total_payments = $conn->query($total_payments_query)->fetch_assoc()['total'] ?: 0;

$pending_amount_query = "SELECT SUM(amount) as total FROM payments WHERE status = 'Unpaid'";
$pending_amount = $conn->query($pending_amount_query)->fetch_assoc()['total'] ?: 0;

$overdue_count_query = "SELECT COUNT(*) as count FROM payments WHERE status = 'Unpaid' AND due_date < CURDATE()";
$overdue_count = $conn->query($overdue_count_query)->fetch_assoc()['count'];

// Fetch all payments with tenant information
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$search_condition = '';
$search_param = '';

if ($search) {
    $search_condition = "WHERE (t.full_name LIKE ? OR u.unit_number LIKE ? OR p.amount LIKE ?)";
    $search_param = "%$search%";
}

$payments_query = "SELECT p.*, t.full_name, u.unit_number 
                   FROM payments p 
                   JOIN tenants t ON p.tenant_id = t.id 
                   LEFT JOIN units u ON t.unit_id = u.id 
                   $search_condition
                   ORDER BY p.due_date DESC, p.status";

if ($search) {
    $stmt = $conn->prepare($payments_query);
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $payments_result = $stmt->get_result();
} else {
    $payments_result = $conn->query($payments_query);
}

// Fetch units for adding new payment records
$units_query = "SELECT id, unit_number, monthly_rent FROM units ORDER BY unit_number";
$units_result = $conn->query($units_query);

$page_title = "Payments Management";
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
            <li><a href="payments.php" class="active"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Payments Management</h1>
            <p>Track and manage all tenant payments</p>
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

        <!-- Payment Statistics -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <h3 style="color: #11998e;">₱<?php echo number_format($total_payments, 2); ?></h3>
                <p>Total Collected</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #ff6b6b;">₱<?php echo number_format($pending_amount, 2); ?></h3>
                <p>Pending Amount</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #ffc107;"><?php echo $overdue_count; ?></h3>
                <p>Overdue Payments</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="margin-bottom: 2rem; display: flex; gap: 1rem; align-items: center;">
            <button onclick="openModal('addPaymentModal')" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Payment Record
            </button>
            <div style="flex: 1; max-width: 400px;">
                <form method="GET" style="display: flex; gap: 0.5rem;">
                    <input type="text" name="search" placeholder="Search payments..." 
                           class="form-control" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if (isset($_GET['search']) && $_GET['search']): ?>
                        <a href="payments.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Unit</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th>Proof</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($payment = $payments_result->fetch_assoc()): ?>
                        <tr id="payment-<?php echo $payment['id']; ?>" style="<?php echo ($payment['status'] == 'Unpaid' && $payment['due_date'] < date('Y-m-d')) ? 'background-color: #fff5f5;' : ''; ?>">
                            <td><?php echo htmlspecialchars($payment['full_name']); ?></td>
                            <td>
                                <?php if ($payment['unit_number']): ?>
                                    Unit <?php echo htmlspecialchars($payment['unit_number']); ?>
                                <?php else: ?>
                                    <span style="color: #999;">No Unit</span>
                                <?php endif; ?>
                            </td>
                            <td><strong>₱<?php echo number_format($payment['amount'], 2); ?></strong></td>
                            <td>
                                <?php 
                                $due_date = date('M j, Y', strtotime($payment['due_date']));
                                if ($payment['status'] == 'Unpaid' && $payment['due_date'] < date('Y-m-d')) {
                                    echo '<span style="color: #ff6b6b; font-weight: 600;">' . $due_date . ' (Overdue)</span>';
                                } else {
                                    echo $due_date;
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($payment['payment_date']): ?>
                                    <?php echo date('M j, Y', strtotime($payment['payment_date'])); ?>
                                <?php else: ?>
                                    <span style="color: #999;">Not paid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $payment['status'] == 'Paid' ? 'success' : 'danger'; ?>">
                                    <?php echo $payment['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($payment['proof_of_payment']): ?>
                                    <!-- *** CHANGE #1: Converted link to button for consistency *** -->
                                    <button onclick="openProofModal('../uploads/<?php echo htmlspecialchars($payment['proof_of_payment']); ?>')" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                <?php else: ?>
                                    <span style="color: #999;">No proof</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($payment['status'] == 'Unpaid'): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Mark this payment as paid?')">
                                        <input type="hidden" name="action" value="mark_paid">
                                        <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Mark Paid
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Paid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div id="addPaymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add Payment Record</h2>
            <span class="close" onclick="closeModal('addPaymentModal')">×</span>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add_payment">
            
            <div class="form-group">
                <label for="unit_id">Select Unit</label>
                <select id="unit_id" name="unit_id" class="form-control" required onchange="loadUnitDetails()">
                    <option value="">Choose a unit...</option>
                    <?php while ($unit = $units_result->fetch_assoc()): ?>
                        <option value="<?php echo $unit['id']; ?>" data-rent="<?php echo $unit['monthly_rent']; ?>">
                            Unit <?php echo htmlspecialchars($unit['unit_number']); ?> - ₱<?php echo number_format($unit['monthly_rent'], 2); ?>/month
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div id="unit-details" style="display: none; padding: 1rem; background: #f8f9ff; border-radius: 10px; margin-bottom: 1rem;">
                <h4 style="margin-bottom: 1rem; color: #333;">Unit Information</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <strong>Monthly Rent:</strong>
                        <div id="unit-rent" style="color: #11998e; font-size: 1.2rem; font-weight: 600;"></div>
                    </div>
                    <div>
                        <strong>Current Tenants:</strong>
                        <div id="tenant-count" style="color: #667eea; font-weight: 600;"></div>
                    </div>
                </div>
                <div>
                    <strong>Select Tenants:</strong>
                    <div id="tenants-list" style="max-height: 150px; overflow-y: auto; border: 1px solid #e1e5e9; border-radius: 5px; padding: 0.5rem; margin-top: 0.5rem;">
                        <!-- Tenants will be loaded here -->
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="amount">Amount (₱)</label>
                <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" required>
                <button type="button" onclick="useUnitRent()" id="useUnitRentBtn" class="btn btn-sm btn-secondary" style="margin-top: 0.5rem; display: none;">
                    Use Unit Rent Amount
                </button>
            </div>
            
            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" class="form-control" required>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" onclick="closeModal('addPaymentModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- *** CHANGE #2: Added the Proof Viewer Modal HTML *** -->
<div id="proofModal" class="modal modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h2>View Proof of Payment</h2>
            <span class="close" onclick="closeModal('proofModal')">×</span>
        </div>
        <div id="proofContent" style="padding: 1rem 0;">
            <!-- Proof will be loaded here by JavaScript -->
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>