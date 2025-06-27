<?php
// --- START OF DEBUG CODE ---
// These lines will force PHP to show any hidden errors.
error_reporting(E_ALL);
ini_set('display_errors', 1);
// --- END OF DEBUG CODE ---

// Ensure this script returns JSON
header('Content-Type: application/json');

// --- START: Manual Session Check for AJAX ---
session_start();
if (!isset($_SESSION['admin_id'])) {
    // If session is not valid, send a JSON error response and stop.
    echo json_encode(['success' => false, 'message' => 'Authentication required.', 'reason' => 'auth']);
    exit();
}
// --- END: Manual Session Check for AJAX ---

require_once '../includes/db_connect.php';

// Initialize the response array
$response = ['success' => false, 'tenants' => [], 'message' => 'An unknown error occurred.'];

if (isset($_GET['unit_id'])) {
    $unit_id = intval($_GET['unit_id']);

    if ($unit_id > 0) {
        // Prepare a statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, full_name, email FROM tenants WHERE unit_id = ? ORDER BY full_name");
        
        if ($stmt) {
            $stmt->bind_param("i", $unit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $tenants = [];
            while ($row = $result->fetch_assoc()) {
                $tenants[] = $row;
            }
            
            $response['success'] = true;
            $response['tenants'] = $tenants;
            $response['message'] = 'Tenants fetched successfully.';
            
            $stmt->close();
        } else {
            $response['message'] = 'Failed to prepare the database query: ' . $conn->error;
        }
    } else {
        $response['message'] = 'Invalid Unit ID provided.';
    }
} else {
    $response['message'] = 'No Unit ID provided.';
}

$conn->close();

// Always output a valid JSON response
echo json_encode($response);
?>