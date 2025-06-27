<?php
// --- START OF DEBUG CODE ---
// These lines will force PHP to show any hidden errors.
error_reporting(E_ALL);
ini_set('display_errors', 1);
// --- END OF DEBUG CODE ---

// Ensure this script returns JSON
header('Content-Type: application/json');

// Session and Database connection
include '../includes/session_check_admin.php';
require_once '../includes/db_connect.php';

// Initialize the response array
$response = ['success' => false, 'unit' => null, 'message' => 'An unknown error occurred.'];

if (isset($_GET['id'])) {
    $unit_id = intval($_GET['id']);

    if ($unit_id > 0) {
        $stmt = $conn->prepare("SELECT * FROM units WHERE id = ?");
        
        if ($stmt) {
            $stmt->bind_param("i", $unit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $unit = $result->fetch_assoc();
                $response['success'] = true;
                $response['unit'] = $unit;
                $response['message'] = 'Unit data fetched successfully.';
            } else {
                $response['message'] = 'Unit not found with the provided ID.';
            }
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