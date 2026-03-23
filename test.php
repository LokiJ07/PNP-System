<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config/db_connect.php';

echo "<h2>Simple Add User Test</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_badge = "TEST-" . time();
    $test_password = "password123";
    $hashed = password_hash($test_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (badge_number, rank, first_name, last_name, email, password, role) VALUES (?, 'PO1', 'Test', 'User', ?, ?, 'user')");
    $email = "test" . time() . "@test.com";
    $stmt->bind_param("sss", $test_badge, $email, $hashed);
    
    if ($stmt->execute()) {
        echo "✅ Test user added! Badge: $test_badge, Email: $email, Password: $test_password<br>";
    } else {
        echo "❌ Error: " . $stmt->error . "<br>";
    }
    $stmt->close();
}
?>
<form method="POST">
    <button type="submit">Add Test User</button>
</form>