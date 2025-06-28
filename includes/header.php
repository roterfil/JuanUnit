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
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$is_dashboard_page = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/user/') !== false);

if ((isset($_SESSION['admin_id']) || isset($_SESSION['tenant_id'])) && $is_dashboard_page) {
    
    require_once(__DIR__ . '/db_connect.php');
    
    $is_admin = isset($_SESSION['admin_id']);
    $user_id = $is_admin ? $_SESSION['admin_id'] : $_SESSION['tenant_id'];
    $user_type = $is_admin ? 'admin' : 'tenant';

    $count_stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND user_type = ? AND is_read = 0");
    $count_stmt->bind_param("is", $user_id, $user_type);
    $count_stmt->execute();
    $unread_count = $count_stmt->get_result()->fetch_assoc()['unread_count'];

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
                    <a href="<?php echo BASE_URL . 'includes/mark_notifications_read.php'; ?>" class="mark-as-read" id="mark-all-read-link">Mark all as read</a>
                <?php endif; ?>
            </div>
            <div class="notification-body">
                <?php if (isset($notifications) && $notifications->num_rows > 0): ?>
                    <?php while ($notif = $notifications->fetch_assoc()): ?>
                        <div class="notification-item <?php echo $notif['is_read'] ? 'read' : 'unread'; ?>">
                            <p><?php echo htmlspecialchars($notif['message']); ?></p>
                            <small><?php echo date('M j, Y g:i A', strtotime($notif['created_at'])); ?></small>
                        </div>
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

    // Close dropdown if clicked outside
    window.addEventListener('click', function(e) {
        var notifArea = document.querySelector('.notification-area');
        if (notifArea && !notifArea.contains(e.target)) {
            var dropdown = document.getElementById('notificationDropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
        }
    });

    // AJAX call for "Mark all as read"
    document.addEventListener('DOMContentLoaded', function() {
        const markAllReadLink = document.getElementById('mark-all-read-link');
        
        if (markAllReadLink) {
            markAllReadLink.addEventListener('click', function(event) {
                event.preventDefault(); // Stop the link from navigating

                const url = this.getAttribute('href');

                // This section checks if the server is returning valid JSON.
                // It helps catch errors if the PHP script fails.
                fetch(url, { 
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Standard header for AJAX requests
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json(); // Attempt to parse the response as JSON
                })
                .then(data => {
                    if (data.success) {
                        // Update the UI instantly without a page reload
                        const badge = document.querySelector('.notification-badge');
                        if (badge) {
                            badge.style.display = 'none';
                        }

                        document.querySelectorAll('.notification-item.unread').forEach(item => {
                            item.classList.remove('unread');
                        });

                        this.style.display = 'none'; // Hide the "Mark all as read" link
                    } else {
                        // Handle cases where the script runs but returns success: false
                        alert('Could not mark notifications as read. Please try again.');
                    }
                })
                .catch(error => {
                    // This block will catch PHP errors or network failures.
                    console.error('Fetch Error:', error);
                    alert('An error occurred. Please check the browser console for details.');
                });
            });
        }
    });
    </script>
<?php } ?>