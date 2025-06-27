<?php
session_start();
require_once('db_connect.php');

if (isset($_SESSION['admin_id']) || isset($_SESSION['tenant_id'])) {
    $is_admin = isset($_SESSION['admin_id']);
    $user_id = $is_admin ? $_SESSION['admin_id'] : $_SESSION['tenant_id'];
    $user_type = $is_admin ? 'admin' : 'tenant';

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND user_type = ? AND is_read = 0");
    $stmt->bind_param("is", $user_id, $user_type);
    $stmt->execute();
    $stmt->close();
}

$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../login.php';
header("Location: " . $redirect_url);
exit();
?>