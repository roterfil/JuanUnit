<?php
$stored_hash = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm';
$password = 'admin123';

if (password_verify($password, $stored_hash)) {
    echo "✅ Password verification SUCCESSFUL!";
} else {
    echo "❌ Password verification FAILED!";
}

echo "<br><br>";
echo "Testing with current database...";

// Connect to database
$conn = new mysqli('localhost', 'root', 'lukarine', 'juanunit_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$username = 'admin';
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $admin = $result->fetch_assoc();
    echo "<br>Database hash: " . $admin['password'];
    
    if (password_verify('admin123', $admin['password'])) {
        echo "<br>✅ Database password verification SUCCESSFUL!";
    } else {
        echo "<br>❌ Database password verification FAILED!";
    }
} else {
    echo "<br>❌ Admin user not found in database!";
}
?>