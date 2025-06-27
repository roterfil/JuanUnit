<?php
function create_notification($conn, $user_id, $user_type, $message, $link = '#') {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, user_type, message, link) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $user_type, $message, $link);
    $stmt->execute();
    $stmt->close();
}

function notify_all_admins($conn, $message, $link) {
    $admin_query = "SELECT id FROM admins";
    $admin_result = $conn->query($admin_query);
    while ($admin = $admin_result->fetch_assoc()) {
        create_notification($conn, $admin['id'], 'admin', $message, $link);
    }
}

function notify_all_tenants($conn, $message, $link) {
    // This function will notify ALL active tenants (those assigned to a unit)
    $tenant_query = "SELECT id FROM tenants WHERE unit_id IS NOT NULL";
    $tenant_result = $conn->query($tenant_query);
    while ($tenant = $tenant_result->fetch_assoc()) {
        create_notification($conn, $tenant['id'], 'tenant', $message, $link);
    }
}
?>