<?php
/**
 * Database Update Script
 * Adds email and password fields to student_records table
 */

require_once('db_config.php');

if (!$db_connection) {
    die("Database connection failed!");
}

echo "<h2>Updating Database Schema...</h2>";

// Check if email column exists
$check_email = "SHOW COLUMNS FROM student_records LIKE 'email'";
$result = mysqli_query($db_connection, $check_email);

if (mysqli_num_rows($result) == 0) {
    // Add email column
    $alter_email = "ALTER TABLE student_records ADD COLUMN email VARCHAR(100) UNIQUE AFTER full_name";
    if (mysqli_query($db_connection, $alter_email)) {
        echo "<p style='color: green;'>✓ Added email column to student_records</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding email column: " . mysqli_error($db_connection) . "</p>";
    }
} else {
    echo "<p>→ Email column already exists</p>";
}

// Check if password column exists
$check_password = "SHOW COLUMNS FROM student_records LIKE 'password'";
$result = mysqli_query($db_connection, $check_password);

if (mysqli_num_rows($result) == 0) {
    // Add password column
    $alter_password = "ALTER TABLE student_records ADD COLUMN password VARCHAR(255) AFTER email";
    if (mysqli_query($db_connection, $alter_password)) {
        echo "<p style='color: green;'>✓ Added password column to student_records</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding password column: " . mysqli_error($db_connection) . "</p>";
    }
} else {
    echo "<p>→ Password column already exists</p>";
}

// Check if student_id column exists (for better primary key)
$check_id = "SHOW COLUMNS FROM student_records LIKE 'student_id'";
$result = mysqli_query($db_connection, $check_id);

if (mysqli_num_rows($result) == 0) {
    // Try to add student_id as auto-increment primary key
    // First, we need to drop the existing primary key
    try {
        $drop_pk = "ALTER TABLE student_records DROP PRIMARY KEY";
        mysqli_query($db_connection, $drop_pk);
        
        $add_id = "ALTER TABLE student_records ADD COLUMN student_id INT AUTO_INCREMENT PRIMARY KEY FIRST";
        if (mysqli_query($db_connection, $add_id)) {
            echo "<p style='color: green;'>✓ Added student_id column</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>→ Could not add student_id (may already have composite key): " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>→ student_id column already exists</p>";
}

echo "<h3 style='color: green; margin-top: 30px;'>✓ Database update completed!</h3>";
echo "<p><a href='seed_dummy_data.php' style='padding: 10px 20px; background: #27ae60; color: white; text-decoration: none; border-radius: 5px;'>Run Dummy Data Seeder</a></p>";
echo "<p><a href='index.html' style='padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Go to Homepage</a></p>";
?>

