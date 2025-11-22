<?php
/**
 * Create Course Units Table
 * Run this script once to create the course_units table for managing course-specific subjects/units
 */

session_start();
require_once('db_config.php');
require_once('auth_check.php');

$messages = [];
$errors = [];

if (!$db_connection) {
    $errors[] = "Database connection failed.";
} else {
    // Check if course_units table exists
    $check_table = "SHOW TABLES LIKE 'course_units'";
    $table_exists = mysqli_query($db_connection, $check_table);
    
    if ($table_exists && mysqli_num_rows($table_exists) > 0) {
        $messages[] = "→ course_units table already exists.";
    } else {
        // Create course_units table
        $create_table = "CREATE TABLE IF NOT EXISTS `course_units` (
            `unit_id` INT(11) NOT NULL AUTO_INCREMENT,
            `course_name` VARCHAR(50) NOT NULL,
            `unit_name` VARCHAR(100) NOT NULL,
            `unit_code` VARCHAR(50) NULL,
            `display_order` INT(3) DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`unit_id`),
            KEY `course_name` (`course_name`),
            CONSTRAINT `course_units_ibfk_1` FOREIGN KEY (`course_name`) REFERENCES `courses` (`course_name`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (mysqli_query($db_connection, $create_table)) {
            $messages[] = "✓ course_units table created successfully!";
        } else {
            $errors[] = "Error creating course_units table: " . mysqli_error($db_connection);
        }
    }
    
    // Check if exam_results has subject_name columns (for dynamic subjects)
    // For now, we'll keep using subject_1, subject_2, etc. but link them to course_units
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
    <title>Create Course Units Table - Academic Results System</title>
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
            <h2><i class="fa fa-database"></i> Create Course Units Table</h2>
            
            <?php foreach ($messages as $msg): ?>
                <div class="message"><?php echo htmlspecialchars($msg); ?></div>
            <?php endforeach; ?>
            
            <?php foreach ($errors as $error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
            
            <?php if (empty($errors)): ?>
                <div class="success-box">
                    <h3><i class="fa fa-check-circle"></i> Setup Complete!</h3>
                    <p>The course units table has been created. You can now manage course units.</p>
                    <a href="manage_course_units.php" class="btn">
                        <i class="fa fa-arrow-right"></i> Manage Course Units
                    </a>
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

