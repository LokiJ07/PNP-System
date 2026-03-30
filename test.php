<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

require_once './config/db_connect.php';

if ($conn) {
    echo "<p style='color:green'>✓ Database connected successfully!</p>";
    
    // Test patrol_activities table
    $result = $conn->query("SELECT COUNT(*) as count FROM patrol_activities WHERE status = 'approved'");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>✓ Patrol activities: " . $row['count'] . " approved records</p>";
    } else {
        echo "<p style='color:red'>✗ Error with patrol_activities: " . $conn->error . "</p>";
    }
    
    // Test checkpoint_activities table
    $result = $conn->query("SELECT COUNT(*) as count FROM checkpoint_activities WHERE status = 'approved'");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>✓ Checkpoint activities: " . $row['count'] . " approved records</p>";
    } else {
        echo "<p style='color:red'>✗ Error with checkpoint_activities: " . $conn->error . "</p>";
    }
    
    // Test oplan_activities table
    $result = $conn->query("SELECT COUNT(*) as count FROM oplan_activities WHERE status = 'approved'");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>✓ Oplan activities: " . $row['count'] . " approved records</p>";
    } else {
        echo "<p style='color:red'>✗ Error with oplan_activities: " . $conn->error . "</p>";
    }
    
} else {
    echo "<p style='color:red'>✗ Database connection failed!</p>";
}
?>