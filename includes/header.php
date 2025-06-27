<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - JuanUnit' : 'JuanUnit - Unit Management System'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo (isset($css_path) ? $css_path : '../css/style.css') . '?v=' . time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php
// Start the session at the very beginning of the header
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Only attempt to show notifications if a user is logged in
if (isset($_SESSION['admin_id']) || isset($_SESSION['tenant_id'])) {
    
    // We are on a dashboard page, so we need the database connection and notifications
    require_once(__DIR__ . '/db_connect.php');
    
    $is_admin = isset($_SESSION['admin_id']);
    $user_id = $is_admin ? $_SESSION['admin_id'] : $_SESSION['tenant_id'];
    $user_type = $is_admin ? 'admin' : 'tenant';

    // Fetch unread notification count
    $count_stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND user_type = ? AND is_read = 0");
    $count_stmt->bind_param("is", $user_id, $user_type);
    $count_stmt->execute();
    $unread_count = $count_stmt->get_result()->fetch_assoc()['unread_count'];

    // Fetch recent notifications
    $notif_stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND user_type = ? ORDER BY created_at DESC LIMIT 5");
    $notif_stmt->bind_param("is", $user_id, $user_type);
    $notif_stmt->execute();
    $notifications = $notif_stmt->get_result();
    ?>
    
    <div class="notification-area">
        <div class="notification-bell" onclick="toggleNotificationDropdown()">
            <i class="fas fa-bell"></i>
            <?php if (isset($unread_count) && $unread_count > 0): ?>
                <span class="notification-badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </div>
        <div class="notification-dropdown" id="notificationDropdown">
            <div class="notification-header">
                <span>Notifications</span>
                <?php if (isset($unread_count) && $unread_count > 0): ?>
                    <a href="<?php echo BASE_URL . 'includes/mark_notifications_read.php'; ?>" class="mark-as-read">Mark all as read</a>
                <?php endif; ?>
            </div>
            <div class="notification-body">
                <?php if (isset($notifications) && $notifications->num_rows > 0): ?>
                    <?php while ($notif = $notifications->fetch_assoc()): ?>
                        <a href="<?php echo BASE_URL . $notif['link']; ?>" class="notification-item <?php echo $notif['is_read'] ? 'read' : 'unread'; ?>">
                            <p><?php echo htmlspecialchars($notif['message']); ?></p>
                            <small><?php echo date('M j, Y g:i A', strtotime($notif['created_at'])); ?></small>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="notification-item-empty">
                        <p>No new notifications</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    function toggleNotificationDropdown() {
        var dropdown = document.getElementById('notificationDropdown');
        if (dropdown) {
            dropdown.classList.toggle('show');
        }
    }
    window.addEventListener('click', function(e) {
        var notifArea = document.querySelector('.notification-area');
        if (notifArea && !notifArea.contains(e.target)) {
            var dropdown = document.getElementById('notificationDropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
        }
    });
    </script>
<?php } ?>