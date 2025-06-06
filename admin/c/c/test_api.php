<?php
// Test the manage_kitchen_actions API
echo "<h3>Testing Kitchen Actions API</h3>";

// Test database connection
require_once 'config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "✅ Database connection successful<br>";
}

// Check if columns exist
$result = $conn->query("DESCRIBE cloud_kitchen_owner");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

echo "<h4>Cloud Kitchen Owner Table Columns:</h4>";
echo "<ul>";
foreach ($columns as $column) {
    echo "<li>$column</li>";
}
echo "</ul>";

// Check suspension columns
$suspensionColumns = ['suspension_reason', 'suspended_by', 'suspension_date'];
$missingColumns = array_diff($suspensionColumns, $columns);

if (empty($missingColumns)) {
    echo "✅ All suspension columns exist<br>";
} else {
    echo "❌ Missing columns: " . implode(', ', $missingColumns) . "<br>";
    echo "<p>Run this SQL to add missing columns:</p>";
    echo "<pre>";
    echo "ALTER TABLE cloud_kitchen_owner \n";
    echo "ADD COLUMN suspension_reason TEXT NULL DEFAULT NULL,\n";
    echo "ADD COLUMN suspended_by INT(11) NULL DEFAULT NULL,\n";
    echo "ADD COLUMN suspension_date TIMESTAMP NULL DEFAULT NULL;";
    echo "</pre>";
}

// Test admin_actions table
$result = $conn->query("SHOW TABLES LIKE 'admin_actions'");
if ($result->num_rows > 0) {
    echo "✅ admin_actions table exists<br>";
} else {
    echo "❌ admin_actions table missing<br>";
    echo "<p>Create admin_actions table with:</p>";
    echo "<pre>";
    echo "CREATE TABLE admin_actions (\n";
    echo "  action_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,\n";
    echo "  admin_id int(11) DEFAULT NULL,\n";
    echo "  action_type text DEFAULT NULL,\n";
    echo "  action_target text DEFAULT NULL,\n";
    echo "  created_at timestamp NOT NULL DEFAULT current_timestamp(),\n";
    echo "  PRIMARY KEY (action_id)\n";
    echo ");";
    echo "</pre>";
}

echo "<h4>Active Kitchens for Testing:</h4>";
$result = $conn->query("SELECT user_id, business_name, status FROM cloud_kitchen_owner WHERE status = 'active' LIMIT 5");
if ($result->num_rows > 0) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>ID: {$row['user_id']} - {$row['business_name']} - Status: {$row['status']}</li>";
    }
    echo "</ul>";
} else {
    echo "No active kitchens found<br>";
}

$conn->close();
?>

<h4>Test API Endpoints:</h4>
<form action="manage_kitchen_actions.php" method="POST" target="_blank">
    <input type="hidden" name="action" value="test">
    <input type="hidden" name="kitchen_id" value="3">
    <button type="submit">Test API Connection</button>
</form> 