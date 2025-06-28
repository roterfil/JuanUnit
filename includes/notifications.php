<?php
// This file should NOT start a session or exit. It's a library of functions.
// It assumes db_connect.php and session data are already available from the script that includes it.

/**
 * Creates a notification for a single user.
 *
 * @param mysqli $conn The database connection object.
 * @param int $user_id The ID of the user (admin or tenant).
 * @param string $user_type 'admin' or 'tenant'.
 * @param string $message The notification message.
 * @param string|null $link The relative link for the notification.
 * @return bool True on success, false on failure.
 */
function create_notification($conn, $user_id, $user_type, $message, $link = null) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, user_type, message, link) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $user_type, $message, $link);
        return $stmt->execute();
    }
    return false;
}

/**
 * Creates a notification for all tenants.
 *
 * @param mysqli $conn The database connection object.
 * @param string $message The notification message.
 * @param string|null $link The relative link for the notification.
 */
function notify_all_tenants($conn, $message, $link = null) {
    $tenants_query = "SELECT id FROM tenants";
    $tenants_result = $conn->query($tenants_query);
    
    while ($tenant = $tenants_result->fetch_assoc()) {
        create_notification($conn, $tenant['id'], 'tenant', $message, $link);
    }
}

/**
 * Creates a notification for all administrators.
 *
 * @param mysqli $conn The database connection object.
 * @param string $message The notification message.
 * @param string|null $link The relative link for the notification.
 */
function notify_all_admins($conn, $message, $link = null) {
    $admins_query = "SELECT id FROM admins";
    $admins_result = $conn->query($admins_query);
    
    while ($admin = $admins_result->fetch_assoc()) {
        create_notification($conn, $admin['id'], 'admin', $message, $link);
    }
}
?>