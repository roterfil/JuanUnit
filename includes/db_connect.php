<?php
// === START: NEW & FINAL - ROBUST BASE_URL DEFINITION ===
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// This reliably finds the path from the web root to the 'includes' folder
$script_path = str_replace('\\', '/', dirname(__FILE__));
$document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$project_path_part = str_replace($document_root, '', $script_path);

// Go up one level from 'includes' to get the project's base path
$base_path = dirname($project_path_part);

// If the project is in the root, the path might be just '\' or '/', handle that.
if ($base_path == '/' || $base_path == '\\') {
    $base_path = '';
}

// Define the final, correct BASE_URL
define('BASE_URL', $protocol . $host . $base_path . '/');
// === END: BASE_URL Definition ===


// === START: Database Connection Logic ===
$host = 'localhost';
$username = 'root';
$password = 'lukarine';
$database = 'juanunit_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
// === END: Database Connection Logic ===
?>