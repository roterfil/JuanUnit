<?php
include '../includes/session_check_admin.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $unit_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM units WHERE id = ?");
    $stmt->bind_param("i", $unit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $unit = $result->fetch_assoc();
        echo json_encode(['success' => true, 'unit' => $unit]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unit not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No unit ID provided']);
}
?>