<?php
// === START: THE DEFINITIVE, HARDCODED BASE_URL ===
// This is the simplest and most reliable method for your local setup.
// It will never fail.
define('BASE_URL', 'http://localhost/juanunit/');
define('SITE_URL', 'https://juanunit.roterfil.com');
define('DB_HOST', 'localhost');
define('DB_NAME', 'juanunit_db');
define('DB_USER', 'root');
define('DB_PASS', 'GwAF16pf:Cf5');
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