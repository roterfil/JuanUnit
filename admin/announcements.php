<?php
include '../includes/session_check_admin.php';
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_announcement') {
            $title = htmlspecialchars($_POST['title']);
            $content = htmlspecialchars($_POST['content']);
            
            if (empty($title) || empty($content)) {
                $error_message = "Title and content are required!";
            } else {
                $stmt = $conn->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
                $stmt->bind_param("ss", $title, $content);
                
                if ($stmt->execute()) {
                    $success_message = "Announcement posted successfully!";
                    require_once('../includes/notifications.php');
                    $notification_message = "New announcement: '" . substr($title, 0, 30) . "...'";
                    notify_all_tenants($conn, $notification_message, 'user/index.php'); // Corrected link
                } else {
                    $error_message = "Failed to post announcement!";
                }
            }
        } elseif ($_POST['action'] == 'delete_announcement') {
            $announcement_id = intval($_POST['announcement_id']);
            
            $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
            $stmt->bind_param("i", $announcement_id);
            
            if ($stmt->execute()) {
                $success_message = "Announcement deleted successfully!";
            } else {
                $error_message = "Failed to delete announcement!";
            }
        } elseif ($_POST['action'] == 'edit_announcement') {
            $announcement_id = intval($_POST['announcement_id']);
            $title = htmlspecialchars($_POST['title']);
            $content = htmlspecialchars($_POST['content']);
            
            if (empty($title) || empty($content)) {
                $error_message = "Title and content are required!";
            } else {
                $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ? WHERE id = ?");
                $stmt->bind_param("ssi", $title, $content, $announcement_id);
                
                if ($stmt->execute()) {
                    $success_message = "Announcement updated successfully!";
                } else {
                    $error_message = "Failed to update announcement!";
                }
            }
        }
    }
}

// Fetch all announcements
$announcements_query = "SELECT * FROM announcements ORDER BY created_at DESC";
$announcements_result = $conn->query($announcements_query);

$page_title = "Announcements Management";
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
            <li><a href="announcements.php" class="active"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Announcements Management</h1>
            <p>Create and manage announcements for all tenants</p>
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

        <!-- Add Announcement Button -->
        <div style="margin-bottom: 2rem;">
            <button onclick="openModal('addAnnouncementModal')" class="btn btn-primary">
                <i class="fas fa-plus"></i> Post New Announcement
            </button>
        </div>

        <!-- Announcements List -->
        <div style="display: grid; gap: 2rem;">
            <?php if ($announcements_result->num_rows > 0): ?>
                <?php while ($announcement = $announcements_result->fetch_assoc()): ?>
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <h3 style="color: #333; margin: 0; flex: 1;"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                            <div style="display: flex; gap: 0.5rem;">
                                <button onclick="openEditModal(<?php echo $announcement['id']; ?>, '<?php echo htmlspecialchars(addslashes($announcement['title'])); ?>', '<?php echo htmlspecialchars(addslashes(str_replace(["\r", "\n"], "\\n", $announcement['content']))); ?>')" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this announcement?')">
                                    <input type="hidden" name="action" value="delete_announcement">
                                    <input type="hidden" name="announcement_id" value="<?php echo $announcement['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <p style="color: #666; line-height: 1.6; margin-bottom: 1rem;">
                            <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                        </p>
                        
                        <div style="border-top: 1px solid #e1e5e9; padding-top: 1rem; color: #999; font-size: 14px;">
                            <i class="fas fa-calendar"></i> Posted on <?php echo date('M j, Y g:i A', strtotime($announcement['created_at'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-bullhorn" style="font-size: 64px; color: #ddd; margin-bottom: 1rem;"></i>
                    <h3 style="color: #999; margin-bottom: 1rem;">No Announcements Yet</h3>
                    <p style="color: #666;">Start by posting your first announcement to keep tenants informed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Announcement Modal -->
<div id="addAnnouncementModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Post New Announcement</h2>
            <span class="close" onclick="closeModal('addAnnouncementModal')">×</span>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add_announcement">
            
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control" required maxlength="200">
            </div>
            
            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" class="form-control" rows="6" required placeholder="Enter your announcement content here..."></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" onclick="closeModal('addAnnouncementModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Post Announcement</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div id="editAnnouncementModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Announcement</h2>
            <span class="close" onclick="closeModal('editAnnouncementModal')">×</span>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit_announcement">
            <input type="hidden" name="announcement_id" id="edit_announcement_id">
            
            <div class="form-group">
                <label for="edit_title">Title</label>
                <input type="text" id="edit_title" name="title" class="form-control" required maxlength="200">
            </div>
            
            <div class="form-group">
                <label for="edit_content">Content</label>
                <textarea id="edit_content" name="content" class="form-control" rows="6" required></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" onclick="closeModal('editAnnouncementModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Announcement</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, title, content) {
    document.getElementById('edit_announcement_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_content').value = content.replace(/\\n/g, '\n');
    openModal('editAnnouncementModal');
}
</script>

<?php include '../includes/footer.php'; ?>