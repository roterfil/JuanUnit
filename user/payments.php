<?php
include '../includes/session_check_user.php';
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle file upload for proof of payment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'upload_proof') {
    $payment_id = intval($_POST['payment_id']);
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['proof_file']['tmp_name'];
        $file_name = $_FILES['proof_file']['name'];
        $file_size = $_FILES['proof_file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file_ext, $allowed_extensions)) {
            $error_message = "Only JPG, JPEG, PNG, and PDF files are allowed!";
        } elseif ($file_size > $max_file_size) {
            $error_message = "File size must be less than 5MB!";
        } else {
            // Generate unique filename
            $new_filename = 'proof_' . $payment_id . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Update payment record with proof filename
                $stmt = $conn->prepare("UPDATE payments SET proof_of_payment = ? WHERE id = ? AND tenant_id = ?");
                $stmt->bind_param("sii", $new_filename, $payment_id, $_SESSION['tenant_id']);
                
                if ($stmt->execute()) {
                    $success_message = "Proof of payment uploaded successfully!";
                    // *** NEW: Notify all admins ***
                    require_once('../includes/notifications.php');
                    $notification_message = "Tenant " . $_SESSION['tenant_name'] . " uploaded a payment proof.";
                    notify_all_admins($conn, $notification_message, 'admin/payments.php#payment-' . $payment_id);                } else {
                    $error_message = "Failed to update payment record!";
                    unlink($upload_path); // Delete uploaded file on database error
                }
            } else {
                $error_message = "Failed to upload file!";
            }
        }
    } else {
        $error_message = "Please select a file to upload!";
    }
}

// Fetch tenant's payment history
$payments_query = "SELECT p.*, u.unit_number 
                   FROM payments p 
                   LEFT JOIN tenants t ON p.tenant_id = t.id 
                   LEFT JOIN units u ON t.unit_id = u.id 
                   WHERE p.tenant_id = ? 
                   ORDER BY p.due_date DESC";
$stmt = $conn->prepare($payments_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$payments_result = $stmt->get_result();

// Calculate payment statistics
$total_paid_query = "SELECT SUM(amount) as total FROM payments WHERE tenant_id = ? AND status = 'Paid'";
$stmt = $conn->prepare($total_paid_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$total_paid = $stmt->get_result()->fetch_assoc()['total'] ?: 0;

$total_pending_query = "SELECT SUM(amount) as total FROM payments WHERE tenant_id = ? AND status = 'Unpaid'";
$stmt = $conn->prepare($total_pending_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$total_pending = $stmt->get_result()->fetch_assoc()['total'] ?: 0;

$overdue_count_query = "SELECT COUNT(*) as count FROM payments WHERE tenant_id = ? AND status = 'Unpaid' AND due_date < CURDATE()";
$stmt = $conn->prepare($overdue_count_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$overdue_count = $stmt->get_result()->fetch_assoc()['count'];

$page_title = "My Payments";
$css_path = "../css/style.css";
$js_path = "../js/script.js";
include '../includes/header.php';
?>

<div class="dashboard">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Tenant Portal</h3>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['tenant_name']); ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="payments.php" class="active"><i class="fas fa-credit-card"></i> My Payments</a></li>
            <li><a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>My Payments</h1>
            <p>View your payment history and upload payment proofs</p>
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
                <h3 style="color: #11998e;">₱<?php echo number_format($total_paid, 2); ?></h3>
                <p>Total Paid</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #ff6b6b;">₱<?php echo number_format($total_pending, 2); ?></h3>
                <p>Amount Pending</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #ffc107;"><?php echo $overdue_count; ?></h3>
                <p>Overdue Payments</p>
            </div>
        </div>

        <!-- Payment History -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem; color: #333;">
                <i class="fas fa-history"></i> Payment History
            </h3>

            <?php if ($payments_result->num_rows > 0): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
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
                                <tr  id="payment-<?php echo $payment['id']; ?>" style="<?php echo ($payment['status'] == 'Unpaid' && $payment['due_date'] < date('Y-m-d')) ? 'background-color: #fff5f5;' : ''; ?>">
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
                                            <!-- *** CHANGE HERE: Converted link to button with onclick event *** -->
                                            <button onclick="openProofModal('../uploads/<?php echo htmlspecialchars($payment['proof_of_payment']); ?>')" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        <?php else: ?>
                                            <span style="color: #999;">No proof</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($payment['status'] == 'Unpaid'): ?>
                                            <button onclick="openUploadModal(<?php echo $payment['id']; ?>, <?php echo $payment['amount']; ?>, '<?php echo date('M j, Y', strtotime($payment['due_date'])); ?>')" class="btn btn-sm btn-success">
                                                <i class="fas fa-upload"></i> Upload Proof
                                            </button>
                                        <?php else: ?>
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Paid</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem;">
                    <i class="fas fa-credit-card" style="font-size: 64px; color: #ddd; margin-bottom: 1rem;"></i>
                    <h3 style="color: #999; margin-bottom: 1rem;">No Payment Records</h3>
                    <p style="color: #666;">Your payment history will appear here once you're assigned to a unit.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upload Proof Modal -->
<div id="uploadProofModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Upload Proof of Payment</h2>
            <span class="close" onclick="closeModal('uploadProofModal')">×</span>
        </div>
        
        <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f8f9ff; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="font-weight: 600;">Amount:</span>
                <span id="modalAmount" style="color: #667eea; font-weight: 600;"></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="font-weight: 600;">Due Date:</span>
                <span id="modalDueDate" style="color: #666;"></span>
            </div>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload_proof">
            <input type="hidden" name="payment_id" id="uploadPaymentId">
            
            <div class="form-group">
                <label for="proof_file">Select Proof of Payment</label>
                <input type="file" id="proof_file" name="proof_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                <small style="color: #666; font-size: 12px;">
                    Accepted formats: JPG, JPEG, PNG, PDF (Max size: 5MB)
                </small>
            </div>
            
            <div id="filePreview" style="margin-top: 1rem; display: none;">
                <img id="imagePreview" style="max-width: 100%; max-height: 200px; border-radius: 10px; border: 2px solid #e1e5e9;">
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" onclick="closeModal('uploadProofModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload Proof</button>
            </div>
        </form>
    </div>
</div>

<!-- *** NEW: Proof Viewer Modal *** -->
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

<script>
function openUploadModal(paymentId, amount, dueDate) {
    document.getElementById('uploadPaymentId').value = paymentId;
    document.getElementById('modalAmount').textContent = '₱' + amount.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalDueDate').textContent = dueDate;
    openModal('uploadProofModal');
}

// File preview functionality
document.getElementById('proof_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('filePreview');
    const imagePreview = document.getElementById('imagePreview');
    
    if (file) {
        const fileType = file.type;
        if (fileType.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    } else {
        preview.style.display = 'none';
    }
});
</script>

<?php include '../includes/footer.php'; ?>