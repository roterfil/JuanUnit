<?php
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: " . $password . "<br>";
echo "Generated hash: " . $hash . "<br><br>";

// Test the generated hash
if (password_verify($password, $hash)) {
    echo "✅ Hash verification SUCCESSFUL!<br>";
    echo "Use this SQL command:<br>";
    echo "<code>UPDATE admins SET password = '" . $hash . "' WHERE username = 'admin';</code>";
} else {
    echo "❌ Hash verification FAILED!";
}
?>