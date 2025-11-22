<?php
/**
 * Database Migration Script
 * Migrates from roll_number to registration_number system
 * Makes registration_number act as password for student login
 */

require_once('db_config.php');

if (!$db_connection) {
    die("Database connection failed!");
}

echo "<h2>Migrating to Registration Number System...</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
    .success { color: #27ae60; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
    .error { color: #e74c3c; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
    .info { color: #3498db; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 10px 0; }
</style>";

$errors = [];
$successes = [];

// Step 1: Check if registration_number column exists
$check_registration = "SHOW COLUMNS FROM student_records LIKE 'registration_number'";
$result = mysqli_query($db_connection, $check_registration);

if (mysqli_num_rows($result) == 0) {
    // Rename roll_number to registration_number
    $rename_query = "ALTER TABLE student_records CHANGE COLUMN roll_number registration_number VARCHAR(50) NOT NULL";
    if (mysqli_query($db_connection, $rename_query)) {
        $successes[] = "Renamed roll_number to registration_number in student_records table";
    } else {
        $errors[] = "Error renaming roll_number: " . mysqli_error($db_connection);
    }
} else {
    $successes[] = "registration_number column already exists";
}

// Step 2: Update exam_results table
$check_results_reg = "SHOW COLUMNS FROM exam_results LIKE 'registration_number'";
$result_results = mysqli_query($db_connection, $check_results_reg);

if (mysqli_num_rows($result_results) == 0) {
    // Check if roll_number exists in exam_results
    $check_roll = "SHOW COLUMNS FROM exam_results LIKE 'roll_number'";
    $roll_exists = mysqli_query($db_connection, $check_roll);
    
    if (mysqli_num_rows($roll_exists) > 0) {
        // Rename roll_number to registration_number in exam_results
        $rename_results = "ALTER TABLE exam_results CHANGE COLUMN roll_number registration_number VARCHAR(50) NOT NULL";
        if (mysqli_query($db_connection, $rename_results)) {
            $successes[] = "Renamed roll_number to registration_number in exam_results table";
        } else {
            $errors[] = "Error renaming in exam_results: " . mysqli_error($db_connection);
        }
    }
} else {
    $successes[] = "registration_number column already exists in exam_results";
}

// Step 3: Update foreign key constraints if needed
// Check current constraints
$constraint_check = "SELECT CONSTRAINT_NAME, TABLE_NAME 
                     FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND COLUMN_NAME = 'registration_number' 
                     AND REFERENCED_TABLE_NAME IS NOT NULL";

$constraints = mysqli_query($db_connection, $constraint_check);
if (!$constraints) {
    // Try to update foreign key constraint name
    $update_fk = "ALTER TABLE exam_results 
                  DROP FOREIGN KEY IF EXISTS exam_results_ibfk_2,
                  ADD CONSTRAINT exam_results_reg_fk 
                  FOREIGN KEY (student_name, registration_number) 
                  REFERENCES student_records (full_name, registration_number) 
                  ON DELETE CASCADE ON UPDATE CASCADE";
    
    @mysqli_query($db_connection, $update_fk);
}

// Step 4: Update primary key in student_records if needed
$check_pk = "SHOW COLUMNS FROM student_records";
$columns = mysqli_query($db_connection, $check_pk);
$has_registration_in_pk = false;

while ($col = mysqli_fetch_assoc($columns)) {
    if ($col['Field'] == 'registration_number' && $col['Key'] == 'PRI') {
        $has_registration_in_pk = true;
        break;
    }
}

// Step 5: Store registration_number as password (for backward compatibility, keep password column but populate it)
$update_password_query = "UPDATE student_records 
                          SET password = registration_number 
                          WHERE password IS NULL OR password = ''";
mysqli_query($db_connection, $update_password_query);
$successes[] = "Updated empty passwords to use registration_number";

// Display results
echo "<div class='info'><strong>Migration Summary:</strong></div>";

if (!empty($successes)) {
    foreach ($successes as $msg) {
        echo "<div class='success'>✓ $msg</div>";
    }
}

if (!empty($errors)) {
    foreach ($errors as $msg) {
        echo "<div class='error'>✗ $msg</div>";
    }
}

echo "<h3 style='color: #27ae60; margin-top: 30px;'>Migration Complete!</h3>";
echo "<p><a href='student_login.php' style='padding: 10px 20px; background: #27ae60; color: white; text-decoration: none; border-radius: 5px;'>Go to Student Login</a></p>";
echo "<p><a href='index.html' style='padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Go to Homepage</a></p>";

?>

