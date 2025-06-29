<?php
/**
 * ===================================================================
 *  ENVIRONMENT & DATABASE CONFIGURATION
 * ===================================================================
 *
 * This file sets up the configuration for your application based
 * on the server environment (live vs. local).
 *
 */

// Define the hostname of your live server
$live_server_host = 'juanunit.roterfil.com';

// Check if the current server host matches the live server host
$is_live_environment = (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == $live_server_host);

if ($is_live_environment) {
    /************************
     * PRODUCTION SETTINGS  *
     ************************/
    
    // Set the base URL for the live site
    define('BASE_URL', 'https://juanunit.roterfil.com/');

    // --- IMPORTANT: Update these with your LIVE database credentials from your hosting provider ---
    $db_host = 'localhost';         // This is often 'localhost', but check with your host
    $db_user = 'root';       // Your live database username
    $db_pass = 'GwAF16pf:Cf5';   // Your live database password
    $db_name = 'juanunit_db';       // Your live database name
    
    // Optional: Hide errors on a live server for security
    error_reporting(0);
    ini_set('display_errors', 0);

} else {
    /************************
     * LOCALHOST SETTINGS   *
     ************************/
    
    // Set the base URL for your local development server
    define('BASE_URL', 'http://localhost/juanunit/');

    // Local database credentials
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = 'lukarine'; // <-- IMPORTANT: Change this to YOUR local XAMPP/WAMP MySQL password
    $db_name = 'juanunit_db';
    
    // Show all errors on local server for easy debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}


/**
 * ===================================================================
 *  DATABASE CONNECTION
 * ===================================================================
 *
 * This section uses the settings defined above to connect to the
 * database. There is no need to edit below this line.
 *
 */

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection for errors
if ($conn->connect_error) {
    die("Database Connection Error: Could not connect to the database. Please check your configuration.");
}

// Set character set to UTF-8 for international character support
$conn->set_charset("utf8");

?>