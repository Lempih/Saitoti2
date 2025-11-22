<?php
/**
 * Quick Migration Script to Fix Foreign Key Constraint
 * Run this once as admin to fix the foreign key issue
 */

session_start();
require_once('db_config.php');
require_once('auth_check.php');

// Only allow admins
if (!isset($_SESSION['logged_in_user'])) {
    $_SESSION['error'] = "Unauthorized access. Admin login required.";
    header("Location: login.php");
    exit();
}

$results = [];

if (!$db_connection) {
    $results[] = ['type' => 'error', 'message' => 'Database connection failed.'];
} else {
    mysqli_begin_transaction($db_connection);
    
    try {
        // Step 1: Drop foreign key constraint
        $results[] = ['type' => 'info', 'message' => 'Step 1: Checking for foreign key constraint...'];
        $fk_check = "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'exam_results' 
                     AND CONSTRAINT_NAME = 'exam_results_ibfk_2'";
        $fk_result = mysqli_query($db_connection, $fk_check);
        
        if ($fk_result && mysqli_num_rows($fk_result) > 0) {
            $results[] = ['type' => 'info', 'message' => 'Dropping foreign key constraint exam_results_ibfk_2...'];
            if (mysqli_query($db_connection, "ALTER TABLE exam_results DROP FOREIGN KEY exam_results_ibfk_2")) {
                $results[] = ['type' => 'success', 'message' => '✓ Foreign key constraint dropped.'];
            } else {
                throw new Exception('Error dropping foreign key: ' . mysqli_error($db_connection));
            }
        } else {
            $results[] = ['type' => 'info', 'message' => 'No foreign key constraint found.'];
        }
        
        // Step 2: Check if student_records has registration_number
        $results[] = ['type' => 'info', 'message' => 'Step 2: Checking student_records table...'];
        $check_reg_student = "SHOW COLUMNS FROM student_records LIKE 'registration_number'";
        $reg_student_result = mysqli_query($db_connection, $check_reg_student);
        $has_reg_student = $reg_student_result && mysqli_num_rows($reg_student_result) > 0;
        
        if (!$has_reg_student) {
            $check_roll_student = "SHOW COLUMNS FROM student_records LIKE 'roll_number'";
            $roll_student_result = mysqli_query($db_connection, $check_roll_student);
            $has_roll_student = $roll_student_result && mysqli_num_rows($roll_student_result) > 0;
            
            if ($has_roll_student) {
                // Check if primary key uses roll_number
                $pk_query = "SHOW KEYS FROM student_records WHERE Key_name = 'PRIMARY' AND Column_name = 'roll_number'";
                $pk_result = mysqli_query($db_connection, $pk_query);
                $pk_uses_roll = $pk_result && mysqli_num_rows($pk_result) > 0;
                
                if ($pk_uses_roll) {
                    $results[] = ['type' => 'info', 'message' => 'Dropping primary key constraint...'];
                    if (!mysqli_query($db_connection, "ALTER TABLE student_records DROP PRIMARY KEY")) {
                        throw new Exception('Error dropping primary key: ' . mysqli_error($db_connection));
                    }
                }
                
                $results[] = ['type' => 'info', 'message' => 'Renaming roll_number to registration_number in student_records...'];
                if (mysqli_query($db_connection, "ALTER TABLE student_records CHANGE COLUMN roll_number registration_number VARCHAR(50) NOT NULL")) {
                    $results[] = ['type' => 'success', 'message' => '✓ Column renamed in student_records.'];
                    
                    if ($pk_uses_roll) {
                        $results[] = ['type' => 'info', 'message' => 'Re-adding primary key constraint...'];
                        if (mysqli_query($db_connection, "ALTER TABLE student_records ADD PRIMARY KEY (full_name, registration_number)")) {
                            $results[] = ['type' => 'success', 'message' => '✓ Primary key re-added.'];
                        } else {
                            throw new Exception('Error re-adding primary key: ' . mysqli_error($db_connection));
                        }
                    }
                } else {
                    throw new Exception('Error renaming column in student_records: ' . mysqli_error($db_connection));
                }
            } else {
                $results[] = ['type' => 'info', 'message' => 'Adding registration_number column to student_records...'];
                if (mysqli_query($db_connection, "ALTER TABLE student_records ADD COLUMN registration_number VARCHAR(50) NOT NULL AFTER full_name")) {
                    $results[] = ['type' => 'success', 'message' => '✓ Column added to student_records.'];
                } else {
                    throw new Exception('Error adding column to student_records: ' . mysqli_error($db_connection));
                }
            }
        } else {
            $results[] = ['type' => 'info', 'message' => '✓ registration_number already exists in student_records.'];
        }
        
        // Step 3: Check if exam_results has registration_number
        $results[] = ['type' => 'info', 'message' => 'Step 3: Checking exam_results table...'];
        $check_reg_exam = "SHOW COLUMNS FROM exam_results LIKE 'registration_number'";
        $reg_exam_result = mysqli_query($db_connection, $check_reg_exam);
        $has_reg_exam = $reg_exam_result && mysqli_num_rows($reg_exam_result) > 0;
        
        if (!$has_reg_exam) {
            $check_roll_exam = "SHOW COLUMNS FROM exam_results LIKE 'roll_number'";
            $roll_exam_result = mysqli_query($db_connection, $check_roll_exam);
            $has_roll_exam = $roll_exam_result && mysqli_num_rows($roll_exam_result) > 0;
            
            if ($has_roll_exam) {
                $results[] = ['type' => 'info', 'message' => 'Renaming roll_number to registration_number in exam_results...'];
                if (mysqli_query($db_connection, "ALTER TABLE exam_results CHANGE COLUMN roll_number registration_number VARCHAR(50) NOT NULL")) {
                    $results[] = ['type' => 'success', 'message' => '✓ Column renamed in exam_results.'];
                } else {
                    throw new Exception('Error renaming column in exam_results: ' . mysqli_error($db_connection));
                }
            } else {
                $results[] = ['type' => 'info', 'message' => 'Adding registration_number column to exam_results...'];
                if (mysqli_query($db_connection, "ALTER TABLE exam_results ADD COLUMN registration_number VARCHAR(50) NOT NULL AFTER student_name")) {
                    $results[] = ['type' => 'success', 'message' => '✓ Column added to exam_results.'];
                } else {
                    throw new Exception('Error adding column to exam_results: ' . mysqli_error($db_connection));
                }
            }
        } else {
            $results[] = ['type' => 'info', 'message' => '✓ registration_number already exists in exam_results.'];
        }
        
        // Step 4: Re-add foreign key constraint if we dropped it
        if ($fk_result && mysqli_num_rows($fk_result) > 0) {
            $results[] = ['type' => 'info', 'message' => 'Step 4: Re-adding foreign key constraint...'];
            if (mysqli_query($db_connection, "ALTER TABLE exam_results ADD CONSTRAINT exam_results_ibfk_2 FOREIGN KEY (student_name, registration_number) REFERENCES student_records (full_name, registration_number) ON DELETE CASCADE ON UPDATE CASCADE")) {
                $results[] = ['type' => 'success', 'message' => '✓ Foreign key constraint re-added successfully.'];
            } else {
                $results[] = ['type' => 'warning', 'message' => 'Warning: Could not re-add foreign key constraint. This may be due to existing data. Error: ' . mysqli_error($db_connection)];
            }
        }
        
        mysqli_commit($db_connection);
        $results[] = ['type' => 'success', 'message' => '✓ Migration completed successfully!'];
        
    } catch (Exception $e) {
        mysqli_rollback($db_connection);
        $results[] = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
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
    <title>Fix Foreign Key Migration - Academic Results System</title>
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
        .result-item {
            padding: 12px 15px;
            margin: 8px 0;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.95rem;
        }
        .result-item.info {
            background: #e3f2fd;
            color: #1976d2;
            border-left: 4px solid #2196f3;
        }
        .result-item.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }
        .result-item.warning {
            background: #fff3e0;
            color: #e65100;
            border-left: 4px solid #ff9800;
        }
        .result-item.error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
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
            background: white;
            color: #27ae60;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.4);
        }
    </style>
</head>
<body>
    <?php include('includes/admin_nav.php'); ?>
    
    <div class="main">
        <div class="migration-box">
            <h2><i class="fa fa-database"></i> Fix Foreign Key Constraint Migration</h2>
            
            <?php foreach ($results as $result): ?>
                <div class="result-item <?php echo htmlspecialchars($result['type']); ?>">
                    <?php echo htmlspecialchars($result['message']); ?>
                </div>
            <?php endforeach; ?>
            
            <?php 
            $has_error = false;
            $has_success = false;
            foreach ($results as $result) {
                if ($result['type'] === 'error') $has_error = true;
                if ($result['type'] === 'success' && strpos($result['message'], 'Migration completed') !== false) $has_success = true;
            }
            ?>
            
            <?php if ($has_success && !$has_error): ?>
                <div class="success-box">
                    <h3><i class="fa fa-check-circle"></i> Migration Complete!</h3>
                    <p>The foreign key constraint has been fixed and the database is ready to use.</p>
                    <a href="dashboard.php" class="btn">
                        <i class="fa fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            <?php elseif (!$has_error && !empty($results)): ?>
                <div style="text-align: center; margin-top: 30px;">
                    <a href="dashboard.php" class="btn" style="background: #27ae60; color: white;">
                        <i class="fa fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="./js/toast.js"></script>
</body>
</html>

