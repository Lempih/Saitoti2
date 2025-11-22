<?php
/**
 * Authentication Initialization Script
 * Ensures database structure is ready for authentication
 * This should be included at the top of login/signup pages
 */

require_once('db_config.php');

if ($db_connection) {
    // Check if student_records table exists first
    $table_check = "SHOW TABLES LIKE 'student_records'";
    $table_result = mysqli_query($db_connection, $table_check);
    
    if ($table_result && mysqli_num_rows($table_result) > 0) {
        // Check if student_records table has email column
        $check_email = "SHOW COLUMNS FROM student_records LIKE 'email'";
        $result = mysqli_query($db_connection, $check_email);
        
        if (!$result || mysqli_num_rows($result) == 0) {
            // Add email column if it doesn't exist
            $alter_email = "ALTER TABLE student_records ADD COLUMN email VARCHAR(100) UNIQUE AFTER full_name";
            @mysqli_query($db_connection, $alter_email);
        }
        
        // Check if student_records table has password column
        $check_password = "SHOW COLUMNS FROM student_records LIKE 'password'";
        $result = mysqli_query($db_connection, $check_password);
        
        if (!$result || mysqli_num_rows($result) == 0) {
            // Add password column if it doesn't exist
            $alter_password = "ALTER TABLE student_records ADD COLUMN password VARCHAR(255) AFTER email";
            @mysqli_query($db_connection, $alter_password);
        }
    }
    
    // Verify administrators table exists and has data
    $admin_table_check = "SHOW TABLES LIKE 'administrators'";
    $admin_table_result = mysqli_query($db_connection, $admin_table_check);
    
    if ($admin_table_result && mysqli_num_rows($admin_table_result) > 0) {
        $check_admin = "SELECT COUNT(*) as count FROM administrators";
        $admin_result = mysqli_query($db_connection, $check_admin);
        if ($admin_result) {
            $admin_row = mysqli_fetch_assoc($admin_result);
            if ($admin_row['count'] == 0) {
                // Insert default admin if none exists
                $insert_admin = "INSERT INTO administrators (admin_username, admin_password) VALUES ('administrator', 'admin2024')";
                @mysqli_query($db_connection, $insert_admin);
            }
        }
    }
}

// Return true if everything is set up correctly
return true;
?>

