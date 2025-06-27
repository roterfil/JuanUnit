<?php
include '../includes/session_check_admin.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['unit_id'])) {
    $unit_id = intval($_GET['unit_id']);
    
    $stmt = $conn->prepare("SELECT id, full_name, email FROM tenants WHERE unit_id = ? ORDER BY full_name");
    $stmt->bind_param("i", $unit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tenants = [];
    while ($row = $result->fetch_assoc()) {
        $tenants[] = $row;
    }
    
    echo json_encode(['success' => true, 'tenants' => $tenants]);
} else {
    echo json_encode(['success' => false, 'message' => 'No unit ID provided']);
}
?>