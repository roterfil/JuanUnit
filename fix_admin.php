<?php
require_once 'includes/db_connect.php';

echo "<h2>Admin Login Fix Tool</h2>";

// Generate a fresh hash for admin123
$password = 'admin123';
$new_hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h3>Step 1: Generated New Hash</h3>";
echo "Password: <strong>$password</strong><br>";
echo "New Hash: <code>$new_hash</code><br>";

// Test the hash
if (password_verify($password, $new_hash)) {
    echo "<span style='color: green;'>✅ Hash verification: SUCCESS</span><br><br>";
} else {
    echo "<span style='color: red;'>❌ Hash verification: FAILED</span><br><br>";
    exit("Something is wrong with password_hash function!");
}

// Update the database
$stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $new_hash);

if ($stmt->execute()) {
    echo "<h3>Step 2: Database Update</h3>";
    echo "<span style='color: green;'>✅ Database updated successfully!</span><br><br>";
} else {
    echo "<span style='color: red;'>❌ Database update failed: " . $stmt->error . "</span><br><br>";
    exit("Database update failed!");
}

// Verify the database contains the new hash
$check_stmt = $conn->prepare("SELECT password FROM admins WHERE username = 'admin'");
$check_stmt->execute();
$result = $check_stmt->get_result();
$admin = $result->fetch_assoc();

echo "<h3>Step 3: Database Verification</h3>";
echo "Hash in database: <code>" . $admin['password'] . "</code><br>";

if ($admin['password'] === $new_hash) {
    echo "<span style='color: green;'>✅ Database hash matches generated hash</span><br>";
} else {
    echo "<span style='color: red;'>❌ Database hash does NOT match generated hash</span><br>";
}

// Final verification test
if (password_verify('admin123', $admin['password'])) {
    echo "<span style='color: green;'>✅ Final verification: Password 'admin123' works with database hash</span><br><br>";
    
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 10px; border-left: 5px solid #4caf50;'>";
    echo "<h3>🎉 SUCCESS! Admin login should now work!</h3>";
    echo "<strong>Login Credentials:</strong><br>";
    echo "Username: <code>admin</code><br>";
    echo "Password: <code>admin123</code><br><br>";
    echo "<a href='login.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Login Now</a>";
    echo "</div>";
} else {
    echo "<span style='color: red;'>❌ Final verification: FAILED</span><br>";
    echo "There might be an issue with your PHP password functions.";
}

// Clean up
$stmt->close();
$check_stmt->close();
$conn->close();
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}

h2, h3 {
    color: #333;
}

code {
    background: #f0f0f0;
    padding: 2px 5px;
    border-radius: 3px;
    font-family: monospace;
    word-break: break-all;
}
</style>