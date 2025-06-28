<?php
session_start();
require_once('db_connect.php');

$response = ['success' => false];

if (isset($_SESSION['admin_id']) || isset($_SESSION['tenant_id'])) {
    $is_admin = isset($_SESSION['admin_id']);
    $user_id = $is_admin ? $_SESSION['admin_id'] : $_SESSION['tenant_id'];
    $user_type = $is_admin ? 'admin' : 'tenant';

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND user_type = ? AND is_read = 0");
    $stmt->bind_param("is", $user_id, $user_type);
    
    if ($stmt->execute()) {
        $response['success'] = true;
    }
    $stmt->close();
}

// Set the header to indicate a JSON response and output the result
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>