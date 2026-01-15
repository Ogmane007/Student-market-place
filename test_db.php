<?php
// Include your database connection file
include 'db.php';

// Simple query to test connection
$sql = "SHOW TABLES";
$result = $conn->query($sql);

if ($result) {
    echo "<h2>✅ Database connection successful!</h2>";
    echo "<p>Tables in student_marketplace:</p><ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<h2>❌ Connection failed or query error.</h2>";
    echo $conn->error;
}
?>
