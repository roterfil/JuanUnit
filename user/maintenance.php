<?php
include '../includes/session_check_user.php';
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle form submission for new maintenance request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'submit_request') {
    $subject = htmlspecialchars($_POST['subject']);
    $description = htmlspecialchars($_POST['description']);
    
    if (empty($subject) || empty($description)) {
        $error_message = "Subject and description are required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO maintenance_requests (tenant_id, subject, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $_SESSION['tenant_id'], $subject, $description);
        
        if ($stmt->execute()) {
            $success_message = "Maintenance request submitted successfully!";
        } else {
            $error_message = "Failed to submit maintenance request!";
        }
    }
}

// Fetch tenant's maintenance requests
$maintenance_query = "SELECT mr.*, u.unit_number 
                      FROM maintenance_requests mr 
                      LEFT JOIN tenants t ON mr.tenant_id = t.id 
                      LEFT JOIN units u ON t.unit_id = u.id 
                      WHERE mr.tenant_id = ? 
                      ORDER BY mr.submitted_at DESC";
$stmt = $conn->prepare($maintenance_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$maintenance_result = $stmt->get_result();

// Calculate maintenance statistics
$pending_count_query = "SELECT COUNT(*) as count FROM maintenance_requests WHERE tenant_id = ? AND status = 'Pending'";
$stmt = $conn->prepare($pending_count_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$pending_count = $stmt->get_result()->fetch_assoc()['count'];

$in_progress_count_query = "SELECT COUNT(*) as count FROM maintenance_requests WHERE tenant_id = ? AND status = 'In Progress'";
$stmt = $conn->prepare($in_progress_count_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$in_progress_count = $stmt->get_result()->fetch_assoc()['count'];

$completed_count_query = "SELECT COUNT(*) as count FROM maintenance_requests WHERE tenant_id = ? AND status = 'Completed'";
$stmt = $conn->prepare($completed_count_query);
$stmt->bind_param("i", $_SESSION['tenant_id']);
$stmt->execute();
$completed_count = $stmt->get_result()->fetch_assoc()['count'];

$page_title = "Maintenance Requests";
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
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> My Payments</a></li>
            <li><a href="maintenance.php" class="active"><i class="fas fa-tools"></i> Maintenance</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Maintenance Requests</h1>
            <p>Submit and track your maintenance requests</p>
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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Submit New Request -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem; color: #333;">
                    <i class="fas fa-plus-circle"></i> Submit New Request
                </h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="submit_request">
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" required maxlength="200" placeholder="Brief description of the issue">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Detailed Description</label>
                        <textarea id="description" name="description" class="form-control" rows="5" required placeholder="Please provide detailed information about the maintenance issue, including location and any relevant details that would help our maintenance team."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </form>
                
                <div style="margin-top: 1.5rem; padding: 1rem; background: #e3f2fd; border-radius: 10px; border-left: 4px solid #2196f3;">
                    <h4 style="color: #1976d2; margin-bottom: 0.5rem; font-size: 16px;">
                        <i class="fas fa-info-circle"></i> Tips for Better Service
                    </h4>
                    <ul style="color: #666; font-size: 14px; margin: 0; padding-left: 1.2rem;">
                        <li>Be specific about the location of the issue</li>
                        <li>Include any error messages if applicable</li>
                        <li>Mention if the issue affects safety or security</li>
                        <li>Provide contact hours if immediate access is needed</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem; color: #333;">
                    <i class="fas fa-bolt"></i> Common Issues
                </h3>
                
                <div style="display: grid; gap: 1rem;">
                    <button onclick="fillQuickRequest('Plumbing Issue', 'Water leak or drainage problem in the unit')" class="btn btn-secondary" style="text-align: left; padding: 1rem;">
                        <i class="fas fa-wrench" style="margin-right: 0.5rem;"></i>
                        <div>
                            <strong>Plumbing Issue</strong>
                            <br><small style="opacity: 0.8;">Water leak, clogged drain, etc.</small>
                        </div>
                    </button>
                    
                    <button onclick="fillQuickRequest('Electrical Problem', 'Electrical issue in the unit (lights, outlets, etc.)')" class="btn btn-secondary" style="text-align: left; padding: 1rem;">
                        <i class="fas fa-bolt" style="margin-right: 0.5rem;"></i>
                        <div>
                            <strong>Electrical Problem</strong>
                            <br><small style="opacity: 0.8;">Lights, outlets, switches</small>
                        </div>
                    </button>
                    
                    <button onclick="fillQuickRequest('Air Conditioning', 'AC unit not working properly or needs maintenance')" class="btn btn-secondary" style="text-align: left; padding: 1rem;">
                        <i class="fas fa-snowflake" style="margin-right: 0.5rem;"></i>
                        <div>
                            <strong>Air Conditioning</strong>
                            <br><small style="opacity: 0.8;">AC repair or maintenance</small>
                        </div>
                    </button>
                    
                    <button onclick="fillQuickRequest('Door/Window Issue', 'Problem with door lock, window, or related hardware')" class="btn btn-secondary" style="text-align: left; padding: 1rem;">
                        <i class="fas fa-door-open" style="margin-right: 0.5rem;"></i>
                        <div>
                            <strong>Door/Window Issue</strong>
                            <br><small style="opacity: 0.8;">Locks, handles, frames</small>
                        </div>
                    </button>
                    
                    <button onclick="fillQuickRequest('Internet/WiFi Issue', 'Internet connectivity or WiFi problems in the unit')" class="btn btn-secondary" style="text-align: left; padding: 1rem;">
                        <i class="fas fa-wifi" style="margin-right: 0.5rem;"></i>
                        <div>
                            <strong>Internet/WiFi Issue</strong>
                            <br><small style="opacity: 0.8;">Connectivity problems</small>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Request History -->
        <div class="card" style="margin-top: 2rem;">
            <h3 style="margin-bottom: 1.5rem; color: #333;">
                <i class="fas fa-history"></i> My Request History
            </h3>

            <?php if ($maintenance_result->num_rows > 0): ?>
                <div style="display: grid; gap: 1.5rem;">
                    <?php while ($request = $maintenance_result->fetch_assoc()): ?>
                        <div style="border: 1px solid #e1e5e9; border-radius: 10px; padding: 1.5rem; background: white;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <h4 style="color: #333; margin: 0;"><?php echo htmlspecialchars($request['subject']); ?></h4>
                                <span class="badge badge-<?php echo $request['status'] == 'Pending' ? 'warning' : ($request['status'] == 'In Progress' ? 'primary' : 'success'); ?>">
                                    <?php echo $request['status']; ?>
                                </span>
                            </div>
                            
                            <p style="color: #666; line-height: 1.6; margin-bottom: 1rem;">
                                <?php echo nl2br(htmlspecialchars($request['description'])); ?>
                            </p>
                            
                            <div style="display: flex; justify-content: between; align-items: center; color: #999; font-size: 14px;">
                                <span><i class="fas fa-calendar"></i> Submitted: <?php echo date('M j, Y g:i A', strtotime($request['submitted_at'])); ?></span>
                                <?php if ($request['unit_number']): ?>
                                    <span style="margin-left: 2rem;"><i class="fas fa-home"></i> Unit <?php echo htmlspecialchars($request['unit_number']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($request['status'] == 'Completed'): ?>
                                <div style="margin-top: 1rem; padding: 0.8rem; background: #e8f5e8; border-radius: 8px; border-left: 4px solid #11998e;">
                                    <span style="color: #2e7d32; font-weight: 600; font-size: 14px;">
                                        <i class="fas fa-check-circle"></i> Request Completed
                                    </span>
                                </div>
                            <?php elseif ($request['status'] == 'In Progress'): ?>
                                <div style="margin-top: 1rem; padding: 0.8rem; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #667eea;">
                                    <span style="color: #1976d2; font-weight: 600; font-size: 14px;">
                                        <i class="fas fa-cog fa-spin"></i> Work in Progress
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem;">
                    <i class="fas fa-tools" style="font-size: 64px; color: #ddd; margin-bottom: 1rem;"></i>
                    <h3 style="color: #999; margin-bottom: 1rem;">No Maintenance Requests</h3>
                    <p style="color: #666;">You haven't submitted any maintenance requests yet. Use the form above to report any issues.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function fillQuickRequest(subject, description) {
    document.getElementById('subject').value = subject;
    document.getElementById('description').value = description;
    document.getElementById('subject').scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php include '../includes/footer.php'; ?>