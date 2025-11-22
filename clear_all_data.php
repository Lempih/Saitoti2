<?php
/**
 * Clear All Data Script
 * Removes all student records and exam results (keeps courses and admin)
 */

session_start();
require_once('db_config.php');
require_once('auth_check.php');

// Only allow admins to clear data
if (!isset($_SESSION['logged_in_user'])) {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: login.php");
    exit();
}

$success = false;
$errors = [];

if (isset($_POST['confirm_clear']) && $_POST['confirm_clear'] === 'DELETE_ALL_DATA') {
    if (!$db_connection) {
        $errors[] = "Database connection failed.";
    } else {
        // Start transaction
        mysqli_begin_transaction($db_connection);
        
        try {
            // Delete all exam results
            $delete_results = "DELETE FROM exam_results";
            if (mysqli_query($db_connection, $delete_results)) {
                $results_deleted = mysqli_affected_rows($db_connection);
            } else {
                throw new Exception("Error deleting results: " . mysqli_error($db_connection));
            }
            
            // Delete all student records
            $delete_students = "DELETE FROM student_records";
            if (mysqli_query($db_connection, $delete_students)) {
                $students_deleted = mysqli_affected_rows($db_connection);
            } else {
                throw new Exception("Error deleting students: " . mysqli_error($db_connection));
            }
            
            // Commit transaction
            mysqli_commit($db_connection);
            $success = true;
            $_SESSION['success'] = "All data cleared successfully! " . $results_deleted . " results and " . $students_deleted . " students deleted.";
            header("Location: dashboard.php");
            exit();
        } catch (Exception $e) {
            mysqli_rollback($db_connection);
            $errors[] = $e->getMessage();
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
    <title>Clear All Data - Academic Results System</title>
    <style>
        .warning-box {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 700px;
            margin: 100px auto;
            box-shadow: 0 20px 60px rgba(231, 76, 60, 0.4);
            text-align: center;
        }
        .warning-box h2 {
            margin-bottom: 20px;
            font-size: 2rem;
        }
        .warning-box p {
            margin: 15px 0;
            line-height: 1.8;
        }
        .warning-icon {
            font-size: 5rem;
            margin-bottom: 20px;
        }
        input[type="text"] {
            width: 100%;
            padding: 15px;
            margin: 20px 0;
            border: 2px solid #fff;
            border-radius: 10px;
            font-size: 1rem;
            text-align: center;
        }
        .confirm-btn {
            background: white;
            color: #e74c3c;
            padding: 15px 40px;
            border: none;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .confirm-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(255, 255, 255, 0.3);
        }
        .cancel-link {
            display: block;
            color: white;
            text-decoration: none;
            margin-top: 20px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        .cancel-link:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <?php include('includes/admin_nav.php'); ?>
    
    <div class="main">
        <div class="warning-box">
            <div class="warning-icon">
                <i class="fa fa-exclamation-triangle"></i>
            </div>
            <h2>Clear All Data</h2>
            <p><strong>WARNING:</strong> This action will permanently delete:</p>
            <ul style="text-align: left; display: inline-block; margin: 20px 0;">
                <li>All student records</li>
                <li>All exam results</li>
            </ul>
            <p><strong>This action cannot be undone!</strong></p>
            <p>Courses and administrator accounts will be preserved.</p>
            
            <?php if (!empty($errors)): ?>
                <div style="background: rgba(255, 255, 255, 0.2); padding: 15px; border-radius: 10px; margin: 20px 0;">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" onsubmit="return confirm('Are you absolutely sure? This will delete ALL students and results!');">
                <p>Type <strong>DELETE_ALL_DATA</strong> to confirm:</p>
                <input type="text" name="confirm_clear" placeholder="Type DELETE_ALL_DATA" required autocomplete="off">
                <button type="submit" class="confirm-btn">
                    <i class="fa fa-trash"></i> Clear All Data
                </button>
            </form>
            
            <a href="dashboard.php" class="cancel-link">
                <i class="fa fa-arrow-left"></i> Cancel - Go Back to Dashboard
            </a>
        </div>
    </div>
    
    <script src="./js/toast.js"></script>
</body>
</html>

