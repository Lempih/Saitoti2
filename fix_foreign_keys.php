<?php
/**
 * Fix Foreign Key Constraints for Registration Number Migration
 * This script fixes foreign key constraints when migrating from roll_number to registration_number
 */

session_start();
require_once('db_config.php');
require_once('auth_check.php');

// Only allow admins to run this script
if (!isset($_SESSION['logged_in_user'])) {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: login.php");
    exit();
}

$messages = [];
$errors = [];

if (!$db_connection) {
    $errors[] = "Database connection failed.";
} else {
    // Check current state
    $check_exam_results = "SHOW COLUMNS FROM exam_results LIKE 'registration_number'";
    $exam_results_reg = mysqli_query($db_connection, $check_exam_results);
    $has_exam_reg = $exam_results_reg && mysqli_num_rows($exam_results_reg) > 0;
    
    $check_student_records = "SHOW COLUMNS FROM student_records LIKE 'registration_number'";
    $student_records_reg = mysqli_query($db_connection, $check_student_records);
    $has_student_reg = $student_records_reg && mysqli_num_rows($student_records_reg) > 0;
    
    // Check foreign key constraints
    $fk_query = "SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                 FROM information_schema.KEY_COLUMN_USAGE 
                 WHERE TABLE_SCHEMA = DATABASE() 
                 AND TABLE_NAME = 'exam_results' 
                 AND CONSTRAINT_NAME = 'exam_results_ibfk_2'";
    $fk_result = mysqli_query($db_connection, $fk_query);
    $has_fk = $fk_result && mysqli_num_rows($fk_result) > 0;
    
    if ($has_fk) {
        // Drop the foreign key constraint first
        $messages[] = "Dropping foreign key constraint exam_results_ibfk_2...";
        if (mysqli_query($db_connection, "ALTER TABLE exam_results DROP FOREIGN KEY exam_results_ibfk_2")) {
            $messages[] = "✓ Foreign key constraint dropped successfully.";
        } else {
            $errors[] = "Error dropping foreign key: " . mysqli_error($db_connection);
        }
    }
    
    // Migrate student_records table
    if (!$has_student_reg) {
        $messages[] = "Migrating student_records table...";
        $check_roll = "SHOW COLUMNS FROM student_records LIKE 'roll_number'";
        $roll_exists = mysqli_query($db_connection, $check_roll);
        
        if ($roll_exists && mysqli_num_rows($roll_exists) > 0) {
            // Drop primary key if it uses roll_number
            $pk_query = "SHOW KEYS FROM student_records WHERE Key_name = 'PRIMARY'";
            $pk_result = mysqli_query($db_connection, $pk_query);
            $pk_uses_roll = false;
            if ($pk_result) {
                while ($pk_row = mysqli_fetch_assoc($pk_result)) {
                    if ($pk_row['Column_name'] === 'roll_number') {
                        $pk_uses_roll = true;
                        break;
                    }
                }
            }
            
            if ($pk_uses_roll) {
                $messages[] = "Dropping primary key constraint...";
                mysqli_query($db_connection, "ALTER TABLE student_records DROP PRIMARY KEY");
            }
            
            // Change roll_number to registration_number
            $messages[] = "Changing roll_number to registration_number in student_records...";
            if (mysqli_query($db_connection, "ALTER TABLE student_records CHANGE COLUMN roll_number registration_number VARCHAR(50) NOT NULL")) {
                $messages[] = "✓ Column renamed to registration_number in student_records.";
                
                // Re-add primary key with new column name
                if ($pk_uses_roll) {
                    $messages[] = "Re-adding primary key constraint...";
                    if (mysqli_query($db_connection, "ALTER TABLE student_records ADD PRIMARY KEY (full_name, registration_number)")) {
                        $messages[] = "✓ Primary key constraint re-added.";
                    } else {
                        $errors[] = "Error re-adding primary key: " . mysqli_error($db_connection);
                    }
                }
            } else {
                $errors[] = "Error renaming column in student_records: " . mysqli_error($db_connection);
            }
        } else {
            $messages[] = "Adding registration_number column to student_records...";
            if (mysqli_query($db_connection, "ALTER TABLE student_records ADD COLUMN registration_number VARCHAR(50) NOT NULL AFTER full_name")) {
                $messages[] = "✓ Column added to student_records.";
            } else {
                $errors[] = "Error adding column to student_records: " . mysqli_error($db_connection);
            }
        }
    } else {
        $messages[] = "→ registration_number already exists in student_records.";
    }
    
    // Migrate exam_results table
    if (!$has_exam_reg) {
        $messages[] = "Migrating exam_results table...";
        $check_roll_results = "SHOW COLUMNS FROM exam_results LIKE 'roll_number'";
        $roll_results_exists = mysqli_query($db_connection, $check_roll_results);
        
        if ($roll_results_exists && mysqli_num_rows($roll_results_exists) > 0) {
            // Change roll_number to registration_number
            $messages[] = "Changing roll_number to registration_number in exam_results...";
            if (mysqli_query($db_connection, "ALTER TABLE exam_results CHANGE COLUMN roll_number registration_number VARCHAR(50) NOT NULL")) {
                $messages[] = "✓ Column renamed to registration_number in exam_results.";
            } else {
                $errors[] = "Error renaming column in exam_results: " . mysqli_error($db_connection);
            }
        } else {
            $messages[] = "Adding registration_number column to exam_results...";
            if (mysqli_query($db_connection, "ALTER TABLE exam_results ADD COLUMN registration_number VARCHAR(50) NOT NULL AFTER student_name")) {
                $messages[] = "✓ Column added to exam_results.";
            } else {
                $errors[] = "Error adding column to exam_results: " . mysqli_error($db_connection);
            }
        }
    } else {
        $messages[] = "→ registration_number already exists in exam_results.";
    }
    
    // Re-add foreign key constraint if we dropped it
    if ($has_fk && $has_student_reg && $has_exam_reg) {
        $messages[] = "Re-adding foreign key constraint...";
        if (mysqli_query($db_connection, "ALTER TABLE exam_results ADD CONSTRAINT exam_results_ibfk_2 FOREIGN KEY (student_name, registration_number) REFERENCES student_records (full_name, registration_number) ON DELETE CASCADE ON UPDATE CASCADE")) {
            $messages[] = "✓ Foreign key constraint re-added successfully.";
        } else {
            $errors[] = "Error re-adding foreign key: " . mysqli_error($db_connection);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <title>Fix Foreign Keys - Academic Results System</title>
    <style>
        .migration-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 900px;
            margin: 50px auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        .migration-box h2 {
            color: #27ae60;
            margin-bottom: 30px;
            font-size: 2rem;
            text-align: center;
        }
        .message {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #27ae60;
        }
        .error {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #e74c3c;
        }
        .success-box {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-top: 30px;
        }
        .success-box h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
        }
    </style>
</head>
<body>
    <?php include('includes/admin_nav.php'); ?>
    
    <div class="main">
        <div class="migration-box">
            <h2><i class="fa fa-database"></i> Fix Foreign Key Constraints</h2>
            
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message"><?php echo htmlspecialchars($msg); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (empty($errors) && !empty($messages)): ?>
                <div class="success-box">
                    <h3><i class="fa fa-check-circle"></i> Migration Completed!</h3>
                    <p>Foreign key constraints have been fixed and the database has been migrated to use registration_number.</p>
                    <a href="dashboard.php" class="btn">
                        <i class="fa fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (empty($messages) && empty($errors)): ?>
                <div class="message">
                    <p>No migration needed. The database appears to be already migrated.</p>
                </div>
                <div style="text-align: center; margin-top: 30px;">
                    <a href="dashboard.php" class="btn">
                        <i class="fa fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="./js/toast.js"></script>
</body>
</html>

